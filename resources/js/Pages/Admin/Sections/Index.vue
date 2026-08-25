<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import draggable from 'vuedraggable';
import axios from 'axios';

const props = defineProps({
    sections: Array,
});

const localSections = ref([...props.sections]);
const isReordering = ref(false);

watch(() => props.sections, (newSections) => {
    localSections.value = [...newSections];
}, { deep: true });

const form = useForm({
    is_visible: true,
});

const toggleVisibility = (section) => {
    form.is_visible = !section.is_visible;
    form.put(route('admin.sections.update', section.id), {
        preserveScroll: true,
    });
};

const handleReorder = async () => {
    isReordering.value = true;
    try {
        const ids = localSections.value.map(s => s.id);
        await axios.post(route('admin.sections.reorder'), { ids });
    } catch (error) {
        console.error('Reorder failed:', error);
    } finally {
        isReordering.value = false;
    }
};
</script>

<template>
    <Head title="Page Sections" />

    <CyberAdminLayout>
        <template #header>
            <h2 class="text-base sm:text-lg md:text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate">
                Page Sections Configuration
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden">
                    <div class="p-6">
                        <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
                            <p class="text-sm text-gray-400">
                                Manage the visibility and order of the sections on your public CV page.
                            </p>
                            <div class="text-xs text-gray-500 italic flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                {{ __('Drag items to reorder') }}
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto rounded-xl border border-white/10">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-white/5">
                                    <tr>
                                        <th scope="col" class="w-10 px-2 py-4"></th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Section Name</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Title (Public)</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Order</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Visible</th>
                                    </tr>
                                </thead>
                                <draggable
                                    v-model="localSections"
                                    tag="tbody"
                                    item-key="id"
                                    handle=".drag-handle"
                                    @end="handleReorder"
                                    :disabled="isReordering"
                                    class="divide-y divide-white/10 bg-transparent"
                                >
                                    <template #item="{ element: section, index }">
                                        <tr class="hover:bg-white/5 transition-colors group cursor-default">
                                            <td class="px-2 py-4 whitespace-nowrap text-center">
                                                <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-600 hover:text-cyan-400 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-white capitalize">{{ section.name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-300">{{ section.title }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-cyan-400 font-mono bg-white/5 inline-block px-2 py-1 rounded-md border border-white/10">{{ index + 1 }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    @click="toggleVisibility(section)"
                                                    class="px-4 py-1.5 rounded-full text-xs font-bold transition-all duration-300 border shadow-lg"
                                                    :class="section.is_visible
                                                        ? 'bg-green-500/20 text-green-400 border-green-500/30 hover:bg-green-500/30 shadow-[0_0_10px_rgba(34,197,94,0.2)]'
                                                        : 'bg-red-500/20 text-red-400 border-red-500/30 hover:bg-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.2)]'"
                                                >
                                                    {{ section.is_visible ? 'Visible' : 'Hidden' }}
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </draggable>
                                <tbody v-if="localSections.length === 0">
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                                            No page sections found.
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
