<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    skills: Array,
});

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
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border capitalize" :class="getCategoryClasses(skill.category)">
                                                {{ skill.category }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-0.5">
                                                <svg v-for="star in 5" :key="star" class="w-5 h-5" :class="star <= skill.proficiency ? 'text-yellow-400 drop-shadow-[0_0_6px_rgba(250,204,21,0.5)]' : 'text-gray-600'" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                                <span class="ml-2 text-cyan-400 font-mono text-xs">{{ skill.proficiency }} / 5</span>
                                            </div>
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
