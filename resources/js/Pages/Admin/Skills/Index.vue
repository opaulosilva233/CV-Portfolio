<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    skills: Array,
});
</script>

<template>
    <Head title="Skills" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-lg sm:text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate mr-4">
                    Skills Management
                </h2>
                <Link :href="route('admin.skills.create')" class="flex-shrink-0 px-4 py-2 sm:px-5 sm:py-2.5 bg-cyan-600/80 hover:bg-cyan-500 border border-cyan-500/50 rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all duration-300">
                    <span class="hidden sm:inline">Add New Skill</span>
                    <span class="sm:hidden">Add New</span>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden">
                    <div class="p-6">
                        
                        <div class="overflow-x-auto rounded-xl border border-white/10">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-white/5">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Category</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Proficiency</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10 bg-transparent">
                                    <tr v-for="skill in skills" :key="skill.id" class="hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="text-sm font-medium text-white">{{ skill.name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 capitalize shadow-[0_0_10px_rgba(6,182,212,0.2)]">
                                                {{ skill.category }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="w-full bg-gray-900/50 rounded-full h-2.5 border border-white/10 max-w-[100px] overflow-hidden">
                                                <div class="bg-gradient-to-r from-purple-500 to-cyan-500 h-2.5 rounded-full shadow-[0_0_10px_rgba(168,85,247,0.5)]" :style="{ width: skill.proficiency + '%' }"></div>
                                            </div>
                                            <div class="text-xs text-cyan-400 mt-1 font-mono">{{ skill.proficiency }}%</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.skills.edit', skill.id)" class="text-cyan-400 hover:text-cyan-300 mr-4 transition-colors">Edit</Link>
                                            <Link :href="route('admin.skills.destroy', skill.id)" method="delete" as="button" class="text-red-400 hover:text-red-300 transition-colors">Delete</Link>
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
