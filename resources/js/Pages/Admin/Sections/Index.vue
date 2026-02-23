<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    sections: Array,
});

const form = useForm({
    is_visible: true,
});

const toggleVisibility = (section) => {
    form.is_visible = !section.is_visible;
    form.put(route('admin.sections.update', section.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Page Sections" />

    <CyberAdminLayout>
        <template #header>
            <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400">
                Page Sections Configuration
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden">
                    <div class="p-6">
                        <p class="mb-6 text-sm text-gray-400">
                            Manage the visibility and order of the sections on your public CV page.
                        </p>
                        
                        <div class="overflow-x-auto rounded-xl border border-white/10">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-white/5">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Section Name</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Title (Public)</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Order</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Visible</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10 bg-transparent">
                                    <tr v-for="section in sections" :key="section.id" class="hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white capitalize">{{ section.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-300">{{ section.title }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-cyan-400 font-mono bg-white/5 inline-block px-2 py-1 rounded-md border border-white/10">{{ section.sort_order }}</div>
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
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
