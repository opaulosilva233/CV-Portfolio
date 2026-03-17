<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from '@/Composables/useDebounce';
import draggable from 'vuedraggable';
import axios from 'axios';

const props = defineProps({
    skills: Array,
    filters: Object,
});

const search = ref(props.filters.search);
const localSkills = ref([...props.skills]);
const selectedIds = ref([]);

// Update local skills when props change
watch(() => props.skills, (newSkills) => {
    localSkills.value = [...newSkills];
}, { deep: true });

const categoryColors = {
    frontend:  { bg: 'bg-blue-500/20',    text: 'text-blue-400',    border: 'border-blue-500/30',    shadow: 'shadow-[0_0_10px_rgba(59,130,246,0.2)]' },
    backend:   { bg: 'bg-green-500/20',   text: 'text-green-400',   border: 'border-green-500/30',   shadow: 'shadow-[0_0_10px_rgba(34,197,94,0.2)]' },
    database:  { bg: 'bg-amber-500/20',   text: 'text-amber-400',   border: 'border-amber-500/30',   shadow: 'shadow-[0_0_10px_rgba(245,158,11,0.2)]' },
    tools:     { bg: 'bg-purple-500/20',  text: 'text-purple-400',  border: 'border-purple-500/30',  shadow: 'shadow-[0_0_10px_rgba(168,85,247,0.2)]' },
    soft:      { bg: 'bg-pink-500/20',    text: 'text-pink-400',    border: 'border-pink-500/30',    shadow: 'shadow-[0_0_10px_rgba(236,72,153,0.2)]' },
    other:     { bg: 'bg-gray-500/20',    text: 'text-gray-400',    border: 'border-gray-500/30',    shadow: 'shadow-[0_0_10px_rgba(107,114,128,0.2)]' },
};

const getCategoryClasses = (category) => {
    const c = categoryColors[category] || categoryColors.other;
    return [c.bg, c.text, c.border, c.shadow].join(' ');
};

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Skills', active: true },
];

watch(search, debounce((value) => {
    router.get(route('admin.skills.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));

const isReordering = ref(false);

const handleReorder = async () => {
    if (search.value) return;
    
    isReordering.value = true;
    try {
        const ids = localSkills.value.map(s => s.id);
        await axios.post(route('admin.skills.reorder'), { ids });
    } catch (error) {
        console.error('Reorder failed:', error);
    } finally {
        isReordering.value = false;
    }
};

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedIds.value = props.skills.map(s => s.id);
    } else {
        selectedIds.value = [];
    }
};

const isAllSelected = computed(() => {
    return props.skills.length > 0 && selectedIds.value.length === props.skills.length;
});

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    
    if (confirm(__('Are you sure you want to delete the selected skills?'))) {
        router.post(route('admin.skills.bulk-delete'), {
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
    <Head title="Skills" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex flex-col">
                    <Breadcrumbs :items="breadcrumbs" />
                    <h2 class="text-lg sm:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate mr-4">
                        {{ __('Skills Management') }}
                    </h2>
                </div>
                <Link :href="route('admin.skills.create')" class="flex-shrink-0 px-4 py-2 sm:px-5 sm:py-2.5 bg-cyan-600/80 hover:bg-cyan-500 border border-cyan-500/50 rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all duration-300">
                    <span class="hidden sm:inline">{{ __('Add New Skill') }}</span>
                    <span class="sm:hidden">{{ __('Add New') }}</span>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                
                <!-- Search & Filters -->
                <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
                    <div class="relative w-full max-md:max-w-none max-w-md group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            v-model="search"
                            type="text" 
                            :placeholder="__('Search by skill name or category...')" 
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

                    <div v-else-if="!search" class="text-xs text-gray-500 italic flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                        {{ __('Drag items to reorder') }}
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
                                        <th scope="col" class="w-10 px-2 py-4"></th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Name') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Category') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Proficiency') }}</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <draggable 
                                    v-model="localSkills" 
                                    tag="tbody" 
                                    item-key="id"
                                    handle=".drag-handle"
                                    @end="handleReorder"
                                    :disabled="!!search || isReordering"
                                    class="divide-y divide-white/10 bg-transparent"
                                >
                                    <template #item="{ element: skill }">
                                        <tr class="hover:bg-white/5 transition-colors group cursor-default" :class="{ 'bg-cyan-500/5': selectedIds.includes(skill.id) }">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input 
                                                    type="checkbox" 
                                                    v-model="selectedIds" 
                                                    :value="skill.id"
                                                    class="rounded border-white/10 bg-white/5 text-purple-600 focus:ring-purple-500 focus:ring-offset-gray-900"
                                                >
                                            </td>
                                            <td class="px-2 py-4 whitespace-nowrap text-center">
                                                <div :class="{ 'cursor-grab active:cursor-grabbing text-gray-600 hover:text-cyan-400': !search, 'opacity-20 cursor-not-allowed': search }" class="drag-handle transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="text-sm font-medium text-white group-hover:text-cyan-400 transition-colors">{{ skill.name }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-[10px] leading-5 font-bold uppercase rounded-full border border-white/10 transition-all group-hover:scale-105" :class="getCategoryClasses(skill.category)">
                                                    {{ skill.category }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-0.5">
                                                    <svg v-for="star in 5" :key="star" class="w-4 h-4 transition-all duration-300" :class="star <= skill.proficiency ? 'text-yellow-400 drop-shadow-[0_0_6px_rgba(250,204,21,0.5)] group-hover:scale-110' : 'text-gray-600'" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                    <span class="ml-2 text-cyan-400 font-mono text-xs">{{ skill.proficiency }} / 5</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <Link :href="route('admin.skills.edit', skill.id)" class="text-cyan-400 hover:text-cyan-300 transition-colors">{{ __('Edit') }}</Link>
                                                    <Link :href="route('admin.skills.destroy', skill.id)" method="delete" as="button" class="text-red-400 hover:text-red-300 transition-colors" onclick="return confirm(__('Are you sure?'))">{{ __('Delete') }}</Link>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </draggable>
                                <tbody v-if="localSkills.length === 0">
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                            {{ __('No skills found matching your search.') }}
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
