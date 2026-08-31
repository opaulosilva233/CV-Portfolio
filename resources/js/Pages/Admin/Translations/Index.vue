<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { debounce } from '@/Composables/useDebounce';
import axios from 'axios';

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({
            total_items: 0,
            complete_items: 0,
            incomplete_items: 0,
            target_locales: ['en', 'nl'],
            source_locale: 'pt',
        }),
    },
    model_types: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            type: 'all',
            status: 'all',
            search: '',
        }),
    },
});

// Filters state
const search = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || 'all');
const selectedStatus = ref(props.filters.status || 'all');

// Loading states
const isTranslatingAll = ref(false);
const translatingItemUid = ref(null);
const isSavingModal = ref(false);
const translatingSingleField = ref({});

// Modal state
const isModalOpen = ref(false);
const activeItem = ref(null);
const modalActiveLocale = ref('en'); // 'en', 'nl', or 'all'
const selectedFieldKeys = ref([]);
const editableTranslations = ref({});
const editableGalleryTranslations = ref({});

// Watch search & filters to update Inertia page
watch([search, selectedType, selectedStatus], debounce(() => {
    router.get(route('admin.translations.index'), {
        search: search.value,
        type: selectedType.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
}, 300));

// Global batch translation
const handleTranslateAll = (force = false) => {
    const confirmMsg = force 
        ? __('Are you sure you want to force re-translate ALL contents across the application? This will overwrite existing translations.')
        : __('Translate all missing fields automatically via Google Translate?');

    if (!confirm(confirmMsg)) return;

    isTranslatingAll.value = true;
    router.post(route('admin.translations.translate-all'), { force }, {
        onFinish: () => {
            isTranslatingAll.value = false;
        }
    });
};

// Item quick translation
const handleTranslateSingleItem = (item, force = true) => {
    translatingItemUid.value = item.uid;
    router.post(route('admin.translations.translate-item'), {
        model_type: item.model_type,
        model_id: item.model_id,
        force: force,
    }, {
        preserveScroll: true,
        onFinish: () => {
            translatingItemUid.value = null;
            if (isModalOpen.value && activeItem.value?.uid === item.uid) {
                // Refresh modal data from updated item in props
                const updated = props.items.find(i => i.uid === item.uid);
                if (updated) openTranslationModal(updated);
            }
        }
    });
};

// Open Translation Modal
const openTranslationModal = (item) => {
    activeItem.value = item;
    
    // Initialize form values
    const translationsMap = {};
    item.fields.forEach(f => {
        props.summary.target_locales.forEach(loc => {
            const key = `${f.field}_${loc}`;
            translationsMap[key] = f.translations[loc] || '';
        });
    });
    editableTranslations.value = translationsMap;

    // Gallery descriptions if Project
    const galleryMap = {};
    if (item.gallery_items && item.gallery_items.length > 0) {
        props.summary.target_locales.forEach(loc => {
            galleryMap[loc] = {};
            item.gallery_items.forEach(g => {
                galleryMap[loc][g.image_key] = g.translations[loc] || '';
            });
        });
    }
    editableGalleryTranslations.value = galleryMap;

    // Preselect all field keys by default
    selectedFieldKeys.value = item.fields.map(f => f.field);

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    activeItem.value = null;
    translatingSingleField.value = {};
};

// Toggle all fields in modal
const toggleSelectAllFields = (e) => {
    if (e.target.checked && activeItem.value) {
        selectedFieldKeys.value = activeItem.value.fields.map(f => f.field);
    } else {
        selectedFieldKeys.value = [];
    }
};

const isAllFieldsSelected = computed(() => {
    if (!activeItem.value || activeItem.value.fields.length === 0) return false;
    return selectedFieldKeys.value.length === activeItem.value.fields.length;
});

// Translate selected fields inside modal
const translateSelectedFieldsInModal = async () => {
    if (!activeItem.value || selectedFieldKeys.value.length === 0) {
        alert(__('Please select at least one field to translate.'));
        return;
    }

    isSavingModal.value = true;

    try {
        const locales = modalActiveLocale.value === 'all' 
            ? props.summary.target_locales 
            : [modalActiveLocale.value];

        // Translate each selected field on-the-fly
        for (const field of activeItem.value.fields) {
            if (!selectedFieldKeys.value.includes(field.field)) continue;
            if (!field.original_value || field.original_value.trim() === '') continue;

            for (const loc of locales) {
                const response = await axios.post(route('admin.translations.translate-text'), {
                    text: field.original_value,
                    target_locale: loc,
                    source_locale: props.summary.source_locale,
                });

                if (response.data && response.data.translated_text !== undefined) {
                    editableTranslations.value[`${field.field}_${loc}`] = response.data.translated_text;
                }
            }
        }

        // Also translate gallery items if applicable and Project
        if (activeItem.value.gallery_items && activeItem.value.gallery_items.length > 0) {
            for (const g of activeItem.value.gallery_items) {
                if (!g.original_value) continue;
                for (const loc of locales) {
                    const response = await axios.post(route('admin.translations.translate-text'), {
                        text: g.original_value,
                        target_locale: loc,
                        source_locale: props.summary.source_locale,
                    });
                    if (response.data && response.data.translated_text !== undefined) {
                        if (!editableGalleryTranslations.value[loc]) {
                            editableGalleryTranslations.value[loc] = {};
                        }
                        editableGalleryTranslations.value[loc][g.image_key] = response.data.translated_text;
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error translating fields:', error);
        alert(__('Translation request failed. Please check logs.'));
    } finally {
        isSavingModal.value = false;
    }
};

// Translate a single field immediately
const translateSingleFieldOnDemand = async (field, locale) => {
    if (!field.original_value || field.original_value.trim() === '') return;

    const key = `${field.field}_${locale}`;
    translatingSingleField.value[key] = true;

    try {
        const response = await axios.post(route('admin.translations.translate-text'), {
            text: field.original_value,
            target_locale: locale,
            source_locale: props.summary.source_locale,
        });

        if (response.data && response.data.translated_text !== undefined) {
            editableTranslations.value[key] = response.data.translated_text;
        }
    } catch (error) {
        console.error('Error translating single field:', error);
    } finally {
        translatingSingleField.value[key] = false;
    }
};

// Translate a single gallery item description immediately
const translateSingleGalleryOnDemand = async (galleryItem, locale) => {
    if (!galleryItem.original_value) return;

    const key = `gallery_${galleryItem.image_key}_${locale}`;
    translatingSingleField.value[key] = true;

    try {
        const response = await axios.post(route('admin.translations.translate-text'), {
            text: galleryItem.original_value,
            target_locale: locale,
            source_locale: props.summary.source_locale,
        });

        if (response.data && response.data.translated_text !== undefined) {
            if (!editableGalleryTranslations.value[locale]) {
                editableGalleryTranslations.value[locale] = {};
            }
            editableGalleryTranslations.value[locale][galleryItem.image_key] = response.data.translated_text;
        }
    } catch (error) {
        console.error('Error translating gallery description:', error);
    } finally {
        translatingSingleField.value[key] = false;
    }
};

// Save all modal edits
const saveModalTranslations = () => {
    if (!activeItem.value) return;

    isSavingModal.value = true;

    // Convert flat editableTranslations back to array of { field, locale, value }
    const translationsPayload = [];
    activeItem.value.fields.forEach(f => {
        props.summary.target_locales.forEach(loc => {
            const key = `${f.field}_${loc}`;
            translationsPayload.push({
                field: f.field,
                locale: loc,
                value: editableTranslations.value[key] || '',
            });
        });
    });

    router.put(route('admin.translations.update'), {
        model_type: activeItem.value.model_type,
        model_id: activeItem.value.model_id,
        translations: translationsPayload,
        gallery_translations: editableGalleryTranslations.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onFinish: () => {
            isSavingModal.value = false;
        }
    });
};

// Colors mapping for badge
const getBadgeClass = (color) => {
    const map = {
        purple: 'bg-purple-500/10 text-purple-400 border-purple-500/30',
        blue: 'bg-blue-500/10 text-blue-400 border-blue-500/30',
        emerald: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
        cyan: 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
        amber: 'bg-amber-500/10 text-amber-400 border-amber-500/30',
        rose: 'bg-rose-500/10 text-rose-400 border-rose-500/30',
        teal: 'bg-teal-500/10 text-teal-400 border-teal-500/30',
    };
    return map[color] || 'bg-gray-500/10 text-gray-400 border-gray-500/30';
};
</script>

<template>
    <Head :title="__('Translations Management')" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 via-cyan-400 to-blue-400">
                        {{ __('Translations Management') }}
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-400 mt-1">
                        {{ __('Translate and manage multi-language CRUD content (PT ➔ EN, NL)') }}
                    </p>
                </div>

                <!-- Global Batch Translation Action -->
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="handleTranslateAll(false)"
                        :disabled="isTranslatingAll"
                        class="px-4 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-semibold rounded-xl text-xs sm:text-sm uppercase tracking-wider shadow-[0_0_20px_rgba(6,182,212,0.35)] hover:shadow-[0_0_25px_rgba(6,182,212,0.5)] border border-cyan-400/30 transition-all duration-300 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="isTranslatingAll" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        <span>{{ isTranslatingAll ? __('Translating...') : __('Translate All Missing') }}</span>
                    </button>

                    <button
                        type="button"
                        @click="handleTranslateAll(true)"
                        :disabled="isTranslatingAll"
                        title="Force re-translate all records"
                        class="px-3 py-2.5 bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white border border-white/10 rounded-xl text-xs font-semibold tracking-wider transition-all duration-200 disabled:opacity-50"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 space-y-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- 1. Stats Overview Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Items -->
                    <div class="relative overflow-hidden rounded-2xl bg-white/5 border border-white/10 p-5 backdrop-blur-xl group hover:border-cyan-500/40 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ __('Total Content Items') }}</p>
                                <h3 class="text-2xl sm:text-3xl font-bold text-white mt-1">{{ summary.total_items }}</h3>
                            </div>
                            <div class="p-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Complete Items -->
                    <div class="relative overflow-hidden rounded-2xl bg-white/5 border border-white/10 p-5 backdrop-blur-xl group hover:border-emerald-500/40 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ __('Fully Translated (100%)') }}</p>
                                <h3 class="text-2xl sm:text-3xl font-bold text-emerald-400 mt-1">{{ summary.complete_items }}</h3>
                            </div>
                            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Incomplete Items -->
                    <div class="relative overflow-hidden rounded-2xl bg-white/5 border border-white/10 p-5 backdrop-blur-xl group hover:border-amber-500/40 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ __('Pending / Incomplete') }}</p>
                                <h3 class="text-2xl sm:text-3xl font-bold text-amber-400 mt-1">{{ summary.incomplete_items }}</h3>
                            </div>
                            <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Target Locales -->
                    <div class="relative overflow-hidden rounded-2xl bg-white/5 border border-white/10 p-5 backdrop-blur-xl group hover:border-purple-500/40 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ __('Target Languages') }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span v-for="loc in summary.target_locales" :key="loc" class="px-2.5 py-1 text-xs font-bold uppercase rounded-lg bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                        {{ loc }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Search & Filters Bar -->
                <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between bg-white/5 border border-white/10 p-4 rounded-2xl backdrop-blur-xl">
                    <!-- Search input -->
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="__('Search by title, key or content...')"
                            class="w-full pl-10 pr-4 py-2 bg-black/40 border border-white/10 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-cyan-500/50 focus:ring-1 focus:ring-cyan-500/50 transition-all duration-200"
                        />
                    </div>

                    <!-- Type filter dropdown -->
                    <div class="flex flex-wrap items-center gap-3">
                        <select
                            v-model="selectedType"
                            class="px-3.5 py-2 bg-black/40 border border-white/10 rounded-xl text-sm text-gray-200 focus:outline-none focus:border-cyan-500/50"
                        >
                            <option v-for="t in model_types" :key="t.value" :value="t.value">
                                {{ t.label }}
                            </option>
                        </select>

                        <!-- Status filter pills -->
                        <div class="flex items-center bg-black/40 p-1 rounded-xl border border-white/10">
                            <button
                                type="button"
                                @click="selectedStatus = 'all'"
                                :class="[
                                    'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200',
                                    selectedStatus === 'all' 
                                        ? 'bg-purple-600/60 text-white shadow' 
                                        : 'text-gray-400 hover:text-white'
                                ]"
                            >
                                {{ __('All') }}
                            </button>
                            <button
                                type="button"
                                @click="selectedStatus = 'incomplete'"
                                :class="[
                                    'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200',
                                    selectedStatus === 'incomplete' 
                                        ? 'bg-amber-600/60 text-white shadow' 
                                        : 'text-gray-400 hover:text-white'
                                ]"
                            >
                                {{ __('Pending') }}
                            </button>
                            <button
                                type="button"
                                @click="selectedStatus = 'complete'"
                                :class="[
                                    'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200',
                                    selectedStatus === 'complete' 
                                        ? 'bg-emerald-600/60 text-white shadow' 
                                        : 'text-gray-400 hover:text-white'
                                ]"
                            >
                                {{ __('Complete') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 3. Items List Table -->
                <div class="bg-white/5 border border-white/10 rounded-2xl backdrop-blur-xl overflow-hidden shadow-2xl">
                    <div v-if="items.length === 0" class="p-12 text-center text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-base font-medium">{{ __('No content items found matching your filters.') }}</p>
                    </div>

                    <div v-else class="divide-y divide-white/5">
                        <div
                            v-for="item in items"
                            :key="item.uid"
                            class="p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 hover:bg-white/[0.02] transition-colors duration-150"
                        >
                            <!-- Left: Item info & snippets -->
                            <div class="flex items-start gap-3.5 flex-1 min-w-0">
                                <span
                                    :class="[
                                        'px-2.5 py-1 text-xs font-semibold rounded-lg border flex-shrink-0 mt-0.5',
                                        getBadgeClass(item.type_color)
                                    ]"
                                >
                                    {{ item.type_label }}
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm sm:text-base font-semibold text-white truncate">
                                            {{ item.title }}
                                        </h4>
                                        <span v-if="item.is_complete" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            100%
                                        </span>
                                        <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            {{ item.stats.percentage }}%
                                        </span>
                                    </div>

                                    <p v-if="item.subtitle" class="text-xs text-gray-400 truncate mt-0.5">
                                        {{ item.subtitle }}
                                    </p>

                                    <!-- Fields list preview -->
                                    <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                        <span
                                            v-for="f in item.fields"
                                            :key="f.field"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-black/40 text-gray-300 border border-white/5"
                                        >
                                            <span class="font-mono text-cyan-400 mr-1">{{ f.label }}:</span>
                                            <span class="truncate max-w-[180px]">{{ f.original_value || '—' }}</span>
                                        </span>
                                        <span v-if="item.gallery_items && item.gallery_items.length > 0" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-purple-500/10 text-purple-300 border border-purple-500/20">
                                            {{ item.gallery_items.length }} {{ __('Gallery Images') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Languages progress & Actions -->
                            <div class="flex items-center flex-wrap sm:flex-nowrap justify-between lg:justify-end gap-4 flex-shrink-0">
                                <!-- Status pills per locale -->
                                <div class="flex items-center gap-2">
                                    <div
                                        v-for="loc in summary.target_locales"
                                        :key="loc"
                                        :class="[
                                            'px-2.5 py-1 rounded-lg border text-xs font-mono flex items-center gap-1.5',
                                            item.stats.by_locale[loc]?.translated === item.stats.by_locale[loc]?.total
                                                ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400'
                                                : 'bg-amber-500/10 border-amber-500/30 text-amber-400'
                                        ]"
                                    >
                                        <span class="font-bold uppercase">{{ loc }}:</span>
                                        <span>{{ item.stats.by_locale[loc]?.translated }}/{{ item.stats.by_locale[loc]?.total }}</span>
                                        <svg v-if="item.stats.by_locale[loc]?.translated === item.stats.by_locale[loc]?.total" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2">
                                    <!-- Instant Translate button -->
                                    <button
                                        type="button"
                                        @click="handleTranslateSingleItem(item, true)"
                                        :disabled="translatingItemUid === item.uid"
                                        title="Auto-translate all fields with Google Translate"
                                        class="px-3 py-1.5 bg-cyan-600/20 hover:bg-cyan-600/40 text-cyan-300 hover:text-white border border-cyan-500/30 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-1.5 disabled:opacity-50"
                                    >
                                        <svg v-if="translatingItemUid === item.uid" class="animate-spin h-3.5 w-3.5 text-cyan-300" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                        </svg>
                                        <span>{{ translatingItemUid === item.uid ? __('Translating...') : __('Auto-Translate') }}</span>
                                    </button>

                                    <!-- Manage / Edit Translations Modal button -->
                                    <button
                                        type="button"
                                        @click="openTranslationModal(item)"
                                        class="px-3 py-1.5 bg-white/5 hover:bg-white/15 text-gray-200 hover:text-white border border-white/10 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-1.5"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        <span>{{ __('Manage / Edit') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- 4. Detailed Translation Modal -->
        <div
            v-if="isModalOpen && activeItem"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
        >
            <div class="relative w-full max-w-4xl bg-gray-900 border border-white/15 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] text-gray-100">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-white/10 bg-white/5 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span :class="['px-2 py-0.5 text-xs font-semibold rounded border', getBadgeClass(activeItem.type_color)]">
                                {{ activeItem.type_label }}
                            </span>
                            <h3 class="text-lg font-bold text-white">{{ activeItem.title }}</h3>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ __('Original source language: Portuguese (PT)') }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeModal"
                        class="p-2 text-gray-400 hover:text-white rounded-xl hover:bg-white/10 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Subheader: Field selection & Language Tabs & Translate Selected Button -->
                <div class="px-6 py-3 border-b border-white/10 bg-black/40 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <!-- Select all checkbox -->
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-300 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                :checked="isAllFieldsSelected"
                                @change="toggleSelectAllFields"
                                class="rounded bg-black/50 border-gray-600 text-cyan-500 focus:ring-cyan-500"
                            />
                            <span>{{ __('Select All Fields') }}</span>
                        </label>

                        <!-- Language tabs -->
                        <div class="flex items-center bg-white/5 p-1 rounded-xl border border-white/10">
                            <button
                                v-for="loc in summary.target_locales"
                                :key="loc"
                                type="button"
                                @click="modalActiveLocale = loc"
                                :class="[
                                    'px-3 py-1 rounded-lg text-xs font-bold uppercase transition-all duration-150',
                                    modalActiveLocale === loc
                                        ? 'bg-cyan-500/30 text-cyan-300 border border-cyan-400/40 shadow'
                                        : 'text-gray-400 hover:text-white'
                                ]"
                            >
                                {{ loc }}
                            </button>
                            <button
                                type="button"
                                @click="modalActiveLocale = 'all'"
                                :class="[
                                    'px-3 py-1 rounded-lg text-xs font-bold uppercase transition-all duration-150',
                                    modalActiveLocale === 'all'
                                        ? 'bg-purple-500/30 text-purple-300 border border-purple-400/40 shadow'
                                        : 'text-gray-400 hover:text-white'
                                ]"
                            >
                                {{ __('All Languages') }}
                            </button>
                        </div>
                    </div>

                    <!-- Translate Selected Fields Button -->
                    <button
                        type="button"
                        @click="translateSelectedFieldsInModal"
                        :disabled="isSavingModal || selectedFieldKeys.length === 0"
                        class="px-3.5 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-[0_0_15px_rgba(6,182,212,0.3)] transition-all duration-200 disabled:opacity-50"
                    >
                        <svg v-if="isSavingModal" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>{{ __('Auto-Translate Selected Fields') }}</span>
                    </button>
                </div>

                <!-- Modal Body: Scrollable Fields Editor -->
                <div class="px-6 py-5 overflow-y-auto space-y-6 flex-1 max-h-[60vh]">
                    
                    <!-- Standard Model Fields -->
                    <div
                        v-for="field in activeItem.fields"
                        :key="field.field"
                        class="p-4 rounded-2xl bg-black/40 border border-white/10 space-y-3"
                    >
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    :value="field.field"
                                    v-model="selectedFieldKeys"
                                    class="rounded bg-black/50 border-gray-600 text-cyan-500 focus:ring-cyan-500"
                                />
                                <span class="font-bold text-sm text-cyan-300">{{ field.label }}</span>
                                <span class="text-xs text-gray-500 font-mono">({{ field.field }})</span>
                            </label>
                        </div>

                        <!-- Original PT Value Display -->
                        <div class="bg-white/5 border border-white/10 rounded-xl p-3">
                            <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                                <span class="font-semibold text-gray-300 uppercase">Português (Origem):</span>
                            </div>
                            <p class="text-sm text-gray-200 whitespace-pre-wrap">{{ field.original_value || '—' }}</p>
                        </div>

                        <!-- Translation Target Inputs -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <template v-for="loc in summary.target_locales" :key="loc">
                                <div
                                    v-if="modalActiveLocale === 'all' || modalActiveLocale === loc"
                                    class="space-y-1.5"
                                    :class="{ 'md:col-span-2': modalActiveLocale !== 'all' }"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold uppercase tracking-wider text-purple-400">
                                            {{ loc === 'en' ? 'English (EN)' : 'Nederlands (NL)' }}
                                        </span>

                                        <!-- Instant Translate Field Button -->
                                        <button
                                            type="button"
                                            @click="translateSingleFieldOnDemand(field, loc)"
                                            :disabled="translatingSingleField[`${field.field}_${loc}`]"
                                            class="text-[11px] px-2 py-0.5 bg-purple-500/20 hover:bg-purple-500/40 text-purple-300 rounded border border-purple-500/30 transition-colors flex items-center gap-1 disabled:opacity-50"
                                        >
                                            <svg v-if="translatingSingleField[`${field.field}_${loc}`]" class="animate-spin h-3 w-3 text-purple-300" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            <span>{{ __('Translate') }}</span>
                                        </button>
                                    </div>

                                    <!-- Input or Textarea based on length -->
                                    <textarea
                                        v-if="(field.original_value && field.original_value.length > 80) || field.field === 'description' || field.field === 'bio'"
                                        v-model="editableTranslations[`${field.field}_${loc}`]"
                                        rows="3"
                                        :placeholder="__('Enter :locale translation...', { locale: loc.toUpperCase() })"
                                        class="w-full px-3 py-2 bg-black/60 border border-white/10 rounded-xl text-sm text-gray-100 placeholder-gray-600 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                                    ></textarea>
                                    <input
                                        v-else
                                        type="text"
                                        v-model="editableTranslations[`${field.field}_${loc}`]"
                                        :placeholder="__('Enter :locale translation...', { locale: loc.toUpperCase() })"
                                        class="w-full px-3 py-2 bg-black/60 border border-white/10 rounded-xl text-sm text-gray-100 placeholder-gray-600 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                                    />
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Project Gallery Descriptions (if applicable) -->
                    <div v-if="activeItem.gallery_items && activeItem.gallery_items.length > 0" class="space-y-4 pt-4 border-t border-white/10">
                        <h4 class="font-bold text-sm text-purple-400 uppercase tracking-wider">
                            {{ __('Project Gallery Image Descriptions') }}
                        </h4>

                        <div
                            v-for="g in activeItem.gallery_items"
                            :key="g.image_key"
                            class="p-4 rounded-2xl bg-black/40 border border-white/10 space-y-3"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-cyan-300">{{ g.label }}</span>
                                <span class="text-xs text-gray-500 font-mono">Image: {{ g.image_key }}</span>
                            </div>

                            <div class="bg-white/5 border border-white/10 rounded-xl p-3">
                                <div class="text-xs text-gray-400 mb-1 font-semibold uppercase">Português:</div>
                                <p class="text-sm text-gray-200 whitespace-pre-wrap">{{ g.original_value }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                <template v-for="loc in summary.target_locales" :key="loc">
                                    <div
                                        v-if="modalActiveLocale === 'all' || modalActiveLocale === loc"
                                        class="space-y-1.5"
                                        :class="{ 'md:col-span-2': modalActiveLocale !== 'all' }"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider text-purple-400">
                                                {{ loc === 'en' ? 'English (EN)' : 'Nederlands (NL)' }}
                                            </span>

                                            <button
                                                type="button"
                                                @click="translateSingleGalleryOnDemand(g, loc)"
                                                :disabled="translatingSingleField[`gallery_${g.image_key}_${loc}`]"
                                                class="text-[11px] px-2 py-0.5 bg-purple-500/20 hover:bg-purple-500/40 text-purple-300 rounded border border-purple-500/30 transition-colors flex items-center gap-1 disabled:opacity-50"
                                            >
                                                <svg v-if="translatingSingleField[`gallery_${g.image_key}_${loc}`]" class="animate-spin h-3 w-3 text-purple-300" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                                <span>{{ __('Translate') }}</span>
                                            </button>
                                        </div>

                                        <input
                                            type="text"
                                            v-if="editableGalleryTranslations[loc]"
                                            v-model="editableGalleryTranslations[loc][g.image_key]"
                                            :placeholder="__('Enter :locale translation...', { locale: loc.toUpperCase() })"
                                            class="w-full px-3 py-2 bg-black/60 border border-white/10 rounded-xl text-sm text-gray-100 placeholder-gray-600 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                                        />
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-white/10 bg-white/5 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white rounded-xl text-xs font-semibold transition-colors"
                    >
                        {{ __('Cancel') }}
                    </button>

                    <button
                        type="button"
                        @click="saveModalTranslations"
                        :disabled="isSavingModal"
                        class="px-5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-semibold uppercase tracking-wider shadow-[0_0_15px_rgba(16,185,129,0.35)] transition-all duration-200 flex items-center gap-2 disabled:opacity-50"
                    >
                        <svg v-if="isSavingModal" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ isSavingModal ? __('Saving...') : __('Save Translations') }}</span>
                    </button>
                </div>

            </div>
        </div>

    </CyberAdminLayout>
</template>
