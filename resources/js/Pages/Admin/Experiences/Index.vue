<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from '@/Composables/useDebounce';

const props = defineProps({
    experiences: Array,
    filters: Object,
});

const search = ref(props.filters.search);
const selectedIds = ref([]);

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Experiences', active: true },
];

watch(search, debounce((value) => {
    router.get(route('admin.experiences.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedIds.value = props.experiences.map(exp => exp.id);
    } else {
        selectedIds.value = [];
    }
};

const isAllSelected = computed(() => {
    return props.experiences.length > 0 && selectedIds.value.length === props.experiences.length;
});

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    
    if (confirm(__('Are you sure you want to delete the selected experiences?'))) {
        router.post(route('admin.experiences.bulk-delete'), {
            ids: selectedIds.value
        }, {
            onSuccess: () => {
                selectedIds.value = [];
            }
        });
    }
};
</script>

<template>
    <Head title="Experiences" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex flex-col">
                    <Breadcrumbs :items="breadcrumbs" />
                    <h2 class="text-lg sm:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate mr-4">
                        {{ __('Work Experience Management') }}
                    </h2>
                </div>
                <Link :href="route('admin.experiences.create')" class="flex-shrink-0 px-4 py-2 sm:px-5 sm:py-2.5 bg-purple-600/80 hover:bg-purple-500 border border-purple-500/50 rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-[0_0_15px_rgba(168,85,247,0.4)] transition-all duration-300">
                    <span class="hidden sm:inline">{{ __('Add New') }}</span>
                    <span class="sm:hidden">{{ __('Add') }}</span>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                
                <!-- Search & Filters -->
                <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
                    <div class="relative w-full max-w-md group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            v-model="search"
                            type="text" 
                            :placeholder="__('Search by company or role...')" 
                            class="block w-full pl-10 pr-3 py-2 border border-white/10 rounded-xl leading-5 bg-white/5 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-cyan-500/50 focus:border-cyan-500/50 sm:text-sm transition-all backdrop-blur-sm"
                        >
                    </div>

                    <!-- Bulk Actions -->
                    <div v-if="selectedIds.length > 0" class="flex items-center gap-4 animate-in fade-in slide-in-from-right-4 duration-300">
                        <span class="text-xs font-bold text-cyan-400 uppercase tracking-widest bg-cyan-400/10 px-3 py-1.5 rounded-lg border border-cyan-400/20 shadow-[0_0_10px_rgba(34,211,238,0.1)]">
                            {{ selectedIds.length }} {{ __('selected') }}
                        </span>
                        <button 
                            @click="bulkDelete"
                            class="px-4 py-2 bg-red-500/20 hover:bg-red-500/40 border border-red-500/50 rounded-xl font-bold text-xs text-red-400 uppercase tracking-widest transition-all backdrop-blur-sm"
                        >
                            {{ __('Delete Selected') }}
                        </button>
                    </div>
                </div>

                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden">
                    <div class="p-6">
                        
                        <div class="overflow-x-auto rounded-xl border border-white/10">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-white/5">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left">
                                            <input 
                                                type="checkbox" 
                                                :checked="isAllSelected"
                                                @change="toggleSelectAll"
                                                class="rounded border-white/10 bg-white/5 text-purple-600 focus:ring-purple-500 focus:ring-offset-gray-900"
                                            >
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Company') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Latest Role') }}</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10 bg-transparent">
                                    <tr v-for="experience in experiences" :key="experience.id" class="hover:bg-white/5 transition-colors group" :class="{ 'bg-cyan-500/5': selectedIds.includes(experience.id) }">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input 
                                                type="checkbox" 
                                                v-model="selectedIds" 
                                                :value="experience.id"
                                                class="rounded border-white/10 bg-white/5 text-purple-600 focus:ring-purple-500 focus:ring-offset-gray-900"
                                            >
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <img v-if="experience.image_url" :src="experience.image_url" :alt="experience.company" class="w-10 h-10 rounded-lg object-contain bg-white/10 border border-white/10 p-0.5 flex-shrink-0 group-hover:border-cyan-500/30 transition-colors" />
                                                <div v-else class="w-10 h-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center flex-shrink-0 group-hover:border-cyan-500/30 transition-colors">
                                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                </div>
                                                <div class="text-sm font-medium text-white group-hover:text-cyan-400 transition-colors">{{ experience.company }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-400 font-mono" v-if="experience.roles && experience.roles.length > 0">
                                                {{ experience.roles[0].role }}
                                            </div>
                                            <div v-else class="text-xs text-gray-600 italic">{{ __('No roles defined') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <Link :href="route('admin.experiences.edit', experience.id)" class="text-cyan-400 hover:text-cyan-300 transition-colors">{{ __('Edit') }}</Link>
                                                <Link :href="route('admin.experiences.destroy', experience.id)" method="delete" as="button" class="text-red-400 hover:text-red-300 transition-colors" onclick="return confirm(__('Are you sure?'))">{{ __('Delete') }}</Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="experiences.length === 0">
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic">
                                            {{ __('No experiences found.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
