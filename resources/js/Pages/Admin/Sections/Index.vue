<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    sections: Array,
});

const localSections = ref([...props.sections]);
const isSaving = ref(false);
const saveSuccess = ref(false);
const draggedIndex = ref(null);
const dragOverIndex = ref(null);

watch(() => props.sections, (newSections) => {
    localSections.value = [...newSections];
}, { deep: true });

const toggleVisibility = async (section) => {
    const previousState = section.is_visible;
    section.is_visible = !previousState;
    try {
        await axios.put(route('admin.sections.update', section.id), {
            is_visible: section.is_visible,
        });
    } catch (error) {
        console.error('Failed to update visibility:', error);
        section.is_visible = previousState;
    }
};

const saveOrder = async () => {
    isSaving.value = true;
    saveSuccess.value = false;
    try {
        const ids = localSections.value.map(s => s.id);
        await axios.post(route('admin.sections.reorder'), { ids });
        saveSuccess.value = true;
        setTimeout(() => {
            saveSuccess.value = false;
        }, 2000);
    } catch (error) {
        console.error('Reorder failed:', error);
    } finally {
        isSaving.value = false;
    }
};

const moveUp = (index) => {
    if (index <= 0 || isSaving.value) return;
    const item = localSections.value.splice(index, 1)[0];
    localSections.value.splice(index - 1, 0, item);
    saveOrder();
};

const moveDown = (index) => {
    if (index >= localSections.value.length - 1 || isSaving.value) return;
    const item = localSections.value.splice(index, 1)[0];
    localSections.value.splice(index + 1, 0, item);
    saveOrder();
};

// Drag and drop handlers
const handleDragStart = (e, index) => {
    draggedIndex.value = index;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', index.toString());
};

const handleDragOver = (e, index) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    if (dragOverIndex.value !== index) {
        dragOverIndex.value = index;
    }
};

const handleDrop = (e, dropIndex) => {
    e.preventDefault();
    if (draggedIndex.value === null || draggedIndex.value === dropIndex) {
        draggedIndex.value = null;
        dragOverIndex.value = null;
        return;
    }

    const item = localSections.value.splice(draggedIndex.value, 1)[0];
    localSections.value.splice(dropIndex, 0, item);

    draggedIndex.value = null;
    dragOverIndex.value = null;

    saveOrder();
};

const handleDragEnd = () => {
    draggedIndex.value = null;
    dragOverIndex.value = null;
};
</script>

<template>
    <Head title="Page Sections" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-base sm:text-lg md:text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate">
                    {{ __('Page Sections Configuration') }}
                </h2>
                <div class="flex items-center gap-3">
                    <span v-if="isSaving" class="text-xs text-cyan-400 font-mono animate-pulse flex items-center gap-1.5 bg-cyan-500/10 px-3 py-1.5 rounded-lg border border-cyan-500/20">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Saving order...') }}
                    </span>
                    <span v-else-if="saveSuccess" class="text-xs text-green-400 font-mono flex items-center gap-1.5 bg-green-500/10 px-3 py-1.5 rounded-lg border border-green-500/20">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ __('Saved') }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden">
                    <div class="p-6">
                        <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
                            <p class="text-sm text-gray-400">
                                {{ __('Manage the visibility and order of the sections on your public CV page.') }}
                            </p>
                            <div class="text-xs text-gray-500 italic flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                {{ __('Drag items or use arrow buttons to reorder') }}
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto rounded-xl border border-white/10">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-white/5">
                                    <tr>
                                        <th scope="col" class="w-24 px-3 py-4 text-center text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Move') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Section Name') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Title (Public)') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Order') }}</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Visible') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10 bg-transparent">
                                    <tr
                                        v-for="(section, index) in localSections"
                                        :key="section.id"
                                        draggable="true"
                                        @dragstart="handleDragStart($event, index)"
                                        @dragover="handleDragOver($event, index)"
                                        @drop="handleDrop($event, index)"
                                        @dragend="handleDragEnd"
                                        class="hover:bg-white/5 transition-all duration-150 group cursor-move select-none"
                                        :class="{
                                            'opacity-40 bg-white/5': draggedIndex === index,
                                            'border-t-2 border-t-cyan-400 bg-cyan-500/10': dragOverIndex === index && draggedIndex !== index,
                                        }"
                                    >
                                        <td class="px-3 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-500 hover:text-cyan-400 p-1.5 rounded transition-colors" title="Drag to reorder">
                                                    <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                                </div>
                                                <div class="flex flex-col gap-0.5">
                                                    <button
                                                        type="button"
                                                        @click.stop="moveUp(index)"
                                                        :disabled="index === 0 || isSaving"
                                                        class="text-gray-500 hover:text-cyan-400 disabled:opacity-20 disabled:hover:text-gray-500 p-0.5 transition-colors"
                                                        title="Move up"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click.stop="moveDown(index)"
                                                        :disabled="index === localSections.length - 1 || isSaving"
                                                        class="text-gray-500 hover:text-cyan-400 disabled:opacity-20 disabled:hover:text-gray-500 p-0.5 transition-colors"
                                                        title="Move down"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white capitalize group-hover:text-cyan-300 transition-colors">{{ section.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-300">{{ section.title }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-cyan-400 font-mono bg-white/5 inline-block px-2.5 py-1 rounded-md border border-white/10 font-bold shadow-inner">{{ index + 1 }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                type="button"
                                                @click.stop="toggleVisibility(section)"
                                                class="px-4 py-1.5 rounded-full text-xs font-bold transition-all duration-300 border shadow-lg cursor-pointer"
                                                :class="section.is_visible
                                                    ? 'bg-green-500/20 text-green-400 border-green-500/30 hover:bg-green-500/30 shadow-[0_0_10px_rgba(34,197,94,0.2)]'
                                                    : 'bg-red-500/20 text-red-400 border-red-500/30 hover:bg-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.2)]'"
                                            >
                                                {{ section.is_visible ? __('Visible') : __('Hidden') }}
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="localSections.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                                            {{ __('No page sections found.') }}
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
