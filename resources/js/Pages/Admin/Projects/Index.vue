<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    projects: Array,
});
</script>

<template>
    <Head title="Projects" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-lg sm:text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate mr-4">
                    Projects Management
                </h2>
                <Link :href="route('admin.projects.create')" class="flex-shrink-0 px-4 py-2 sm:px-5 sm:py-2.5 bg-blue-600/80 hover:bg-blue-500 border border-blue-500/50 rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-[0_0_15px_rgba(59,130,246,0.4)] transition-all duration-300">
                    <span class="hidden sm:inline">Add New Project</span>
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
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Title</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Tech Stack</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10 bg-transparent">
                                    <tr v-for="project in projects" :key="project.id" class="hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <img v-if="project.main_image_url" :src="project.main_image_url" :alt="project.title" class="w-10 h-10 rounded-lg object-contain bg-white/10 border border-white/10 p-0.5 flex-shrink-0" />
                                                <div v-else class="w-10 h-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-white">{{ project.title }}</div>
                                                    <div class="text-xs text-cyan-400 hover:text-cyan-300 mt-1">
                                                        <a :href="project.project_url" target="_blank" v-if="project.project_url">{{ project.project_url }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 flex flex-wrap gap-2">
                                            <span v-for="tech in project.tech_stack" :key="tech" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-white/5 text-gray-300 border border-white/10">
                                                {{ tech }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 shadow-[0_0_10px_rgba(234,179,8,0.2)]" v-if="project.is_featured">
                                                Featured
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.projects.edit', project.id)" class="text-cyan-400 hover:text-cyan-300 mr-4 transition-colors">Edit</Link>
                                            <Link :href="route('admin.projects.destroy', project.id)" method="delete" as="button" class="text-red-400 hover:text-red-300 transition-colors">Delete</Link>
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
