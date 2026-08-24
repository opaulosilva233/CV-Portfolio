<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from '@/Composables/useDebounce';
import draggable from 'vuedraggable';
import axios from 'axios';

const props = defineProps({
    projects: Array,
    filters: Object,
});

const search = ref(props.filters.search);
const localProjects = ref([...props.projects]);
const selectedIds = ref([]);

// Update local projects when props change
watch(() => props.projects, (newProjects) => {
    localProjects.value = [...newProjects];
}, { deep: true });

const breadcrumbs = [
    { label: 'Projects Portfolio', active: true },
];

watch(search, debounce((value) => {
    router.get(route('admin.projects.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));

const isReordering = ref(false);

const handleReorder = async () => {
    if (search.value) return;
    
    isReordering.value = true;
    try {
        const ids = localProjects.value.map(p => p.id);
        await axios.post(route('admin.projects.reorder'), { ids });
    } catch (error) {
        console.error('Reorder failed:', error);
    } finally {
        isReordering.value = false;
    }
};

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedIds.value = props.projects.map(p => p.id);
    } else {
        selectedIds.value = [];
    }
};

const isAllSelected = computed(() => {
    return props.projects.length > 0 && selectedIds.value.length === props.projects.length;
});

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    
    if (confirm(__('Are you sure you want to delete the selected projects?'))) {
        router.post(route('admin.projects.bulk-delete'), {
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
    <Head title="Projects" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex flex-col">
                    <Breadcrumbs :items="breadcrumbs" />
                    <h2 class="text-lg sm:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate mr-4">
                        {{ __('Projects Management') }}
                    </h2>
                </div>
                <Link :href="route('admin.projects.create')" class="flex-shrink-0 px-4 py-2 sm:px-5 sm:py-2.5 bg-blue-600/80 hover:bg-blue-500 border border-blue-500/50 rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-[0_0_15px_rgba(59,130,246,0.4)] transition-all duration-300">
                    <span class="hidden sm:inline">{{ __('Add New Project') }}</span>
                    <span class="sm:hidden">{{ __('Add New') }}</span>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                
                <!-- Search & Filters -->
                <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
                    <div class="relative w-full max-sm:max-w-none max-w-md group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            v-model="search"
                            type="text" 
                            :placeholder="__('Search by title or description...')" 
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
                                                class="rounded border-white/10 bg-white/5 text-blue-600 focus:ring-blue-500 focus:ring-offset-gray-900"
                                            >
                                        </th>
                                        <th scope="col" class="w-10 px-2 py-4"></th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Title') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Completed At') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Status') }}</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <draggable 
                                    v-model="localProjects" 
                                    tag="tbody" 
                                    item-key="id"
                                    handle=".drag-handle"
                                    @end="handleReorder"
                                    :disabled="!!search || isReordering"
                                    class="divide-y divide-white/10 bg-transparent"
                                >
                                    <template #item="{ element: project }">
                                        <tr class="hover:bg-white/5 transition-colors group cursor-default" :class="{ 'bg-cyan-500/5': selectedIds.includes(project.id) }">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input 
                                                    type="checkbox" 
                                                    v-model="selectedIds" 
                                                    :value="project.id"
                                                    class="rounded border-white/10 bg-white/5 text-blue-600 focus:ring-blue-500 focus:ring-offset-gray-900"
                                                >
                                            </td>
                                            <td class="px-2 py-4 whitespace-nowrap text-center">
                                                <div :class="{ 'cursor-grab active:cursor-grabbing text-gray-600 hover:text-cyan-400': !search, 'opacity-20 cursor-not-allowed': search }" class="drag-handle transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <img v-if="project.main_image_url" :src="project.main_image_url" :alt="project.title" class="w-10 h-10 rounded-lg object-contain bg-white/10 border border-white/10 p-0.5 flex-shrink-0 group-hover:border-cyan-500/30 transition-colors" />
                                                    <div v-else class="w-10 h-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center flex-shrink-0 group-hover:border-cyan-500/30 transition-colors">
                                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-medium text-white truncate group-hover:text-cyan-400 transition-colors">{{ project.title }}</div>
                                                        <div class="text-[10px] text-cyan-400/70 truncate mt-0.5 font-mono" v-if="project.project_url">
                                                            <a :href="project.project_url" target="_blank" class="hover:underline">{{ project.project_url.replace('https://', '') }}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-mono text-gray-300">
                                                    <span v-if="project.in_progress" class="text-purple-400 font-semibold italic">{{ __('In Progress') }}</span>
                                                    <span v-else>{{ project.completed_at ? new Date(project.completed_at).toLocaleDateString() : 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex justify-center gap-2">
                                                    <span class="px-2 py-0.5 inline-flex text-[10px] font-bold uppercase rounded bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 shadow-[0_0_10px_rgba(234,179,8,0.2)]" v-if="project.is_featured">
                                                        {{ __('Featured') }}
                                                    </span>
                                                    <span class="px-2 py-0.5 inline-flex text-[10px] font-bold uppercase rounded bg-purple-500/20 text-purple-400 border border-purple-500/30 shadow-[0_0_10px_rgba(168,85,247,0.2)]" v-if="project.in_progress">
                                                        {{ __('Active') }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <Link :href="route('admin.projects.edit', project.id)" class="text-cyan-400 hover:text-cyan-300 transition-colors">{{ __('Edit') }}</Link>
                                                    <Link :href="route('admin.projects.destroy', project.id)" method="delete" as="button" class="text-red-400 hover:text-red-300 transition-colors" onclick="return confirm(__('Are you sure?'))">{{ __('Delete') }}</Link>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </draggable>
                                <tbody v-if="localProjects.length === 0">
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                            {{ __('No projects found matching your search.') }}
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
