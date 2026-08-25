<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    interests: Array,
});

const draggedIndex = ref(null);

const handleDragStart = (index) => {
    draggedIndex.value = index;
};

const handleDragOver = (e) => {
    e.preventDefault();
};

const handleDrop = (dropIndex) => {
    if (draggedIndex.value === null || draggedIndex.value === dropIndex) {
        return;
    }

    const newInterests = [...interests];
    const draggedItem = newInterests[draggedIndex.value];
    newInterests.splice(draggedIndex.value, 1);
    newInterests.splice(dropIndex, 0, draggedItem);

    // Update order values
    newInterests.forEach((interest, index) => {
        interest.order = index + 1;
    });

    router.post(route('admin.interests.reorder'), {
        interests: newInterests.map(i => ({ id: i.id, order: i.order })),
    }, {
        preserveScroll: true,
    });

    draggedIndex.value = null;
};

const categoryColors = {
    hobby:   { bg: 'bg-purple-500/20',  text: 'text-purple-400',  border: 'border-purple-500/30',  shadow: 'shadow-[0_0_10px_rgba(168,85,247,0.2)]' },
    music:   { bg: 'bg-pink-500/20',    text: 'text-pink-400',    border: 'border-pink-500/30',    shadow: 'shadow-[0_0_10px_rgba(236,72,153,0.2)]' },
    sport:   { bg: 'bg-green-500/20',   text: 'text-green-400',   border: 'border-green-500/30',   shadow: 'shadow-[0_0_10px_rgba(34,197,94,0.2)]' },
    book:    { bg: 'bg-amber-500/20',   text: 'text-amber-400',   border: 'border-amber-500/30',   shadow: 'shadow-[0_0_10px_rgba(245,158,11,0.2)]' },
    travel:  { bg: 'bg-blue-500/20',    text: 'text-blue-400',    border: 'border-blue-500/30',    shadow: 'shadow-[0_0_10px_rgba(59,130,246,0.2)]' },
    other:   { bg: 'bg-gray-500/20',    text: 'text-gray-400',    border: 'border-gray-500/30',    shadow: 'shadow-[0_0_10px_rgba(107,114,128,0.2)]' },
};

const getCategoryClasses = (category) => {
    const c = categoryColors[category] || categoryColors.other;
    return [c.bg, c.text, c.border, c.shadow].join(' ');
};
</script>

<template>
    <Head title="Interests Management" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full gap-3">
                <h2 class="text-base sm:text-lg md:text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate flex-1">
                    Interests & Hobbies
                </h2>
                <Link :href="route('admin.interests.create')" class="flex-shrink-0 px-3 py-2 sm:px-5 sm:py-2.5 bg-cyan-600/80 hover:bg-cyan-500 border border-cyan-500/50 rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all duration-300">
                    <span class="hidden sm:inline">Add New Interest</span>
                    <span class="sm:hidden">Add</span>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden">
                    <div class="p-6">
                        <div v-if="interests.length === 0" class="text-center py-12">
                            <p class="text-gray-400 italic">No interests found. Click the button above to add one.</p>
                        </div>
                        <div v-else class="overflow-x-auto rounded-xl border border-white/10">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-white/5">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Order</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Interest</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Category</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10 bg-transparent">
                                    <tr 
                                        v-for="(interest, index) in interests" 
                                        :key="interest.id" 
                                        class="hover:bg-white/5 transition-colors cursor-move"
                                        draggable="true"
                                        @dragstart="handleDragStart(index)"
                                        @dragover="handleDragOver"
                                        @drop="handleDrop(index)"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-gray-400 text-sm">{{ interest.order }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div v-if="interest.icon" class="w-8 h-8 flex items-center justify-center [&>svg]:w-6 [&>svg]:h-6 text-cyan-400" v-html="interest.icon"></div>
                                                <div class="text-sm font-medium text-white">{{ interest.name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border capitalize" :class="getCategoryClasses(interest.category)">
                                                {{ interest.category }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="interest.is_active" class="px-2 py-1 bg-green-500/10 text-green-400 border border-green-500/30 rounded text-xs">Active</span>
                                            <span v-else class="px-2 py-1 bg-red-500/10 text-red-400 border border-red-500/30 rounded text-xs">Inactive</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.interests.edit', interest.id)" class="text-cyan-400 hover:text-cyan-300 mr-4 transition-colors">Edit</Link>
                                            <Link :href="route('admin.interests.destroy', interest.id)" method="delete" as="button" class="text-red-400 hover:text-red-300 transition-colors" onsubmit="return confirm('Are you sure?')">Delete</Link>
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
