<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    experiences: Array,
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <Head title="Experiences" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-lg sm:text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate mr-4">
                    {{ __('Experiences Management') }}
                </h2>
                <Link :href="route('admin.experiences.create')" class="flex-shrink-0 px-4 py-2 sm:px-5 sm:py-2.5 bg-purple-600/80 hover:bg-purple-500 border border-purple-500/50 rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-[0_0_15px_rgba(168,85,247,0.4)] transition-all duration-300">
                    <span class="hidden sm:inline">{{ __('Add New Experience') }}</span>
                    <span class="sm:hidden">{{ __('Add New') }}</span>
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
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Company') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Roles & Dates') }}</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10 bg-transparent">
                                    <tr v-for="experience in experiences" :key="experience.id" class="hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <img v-if="experience.image_url" :src="experience.image_url" :alt="experience.company" class="w-10 h-10 rounded-lg object-contain bg-white/10 border border-white/10 p-0.5 flex-shrink-0" />
                                                <div v-else class="w-10 h-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-white">{{ experience.company }}</div>
                                                    <div class="text-sm text-gray-400">{{ experience.location }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div v-for="(role, index) in experience.roles" :key="index" class="mb-3 last:mb-0">
                                                <div class="text-sm text-gray-200 font-medium">{{ role.role }}</div>
                                                <div class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                                                    <span class="px-2 py-0.5 inline-flex font-semibold rounded bg-green-500/20 text-green-400 border border-green-500/30 text-[10px]" v-if="role.is_current">
                                                        {{ __('Current') }}
                                                    </span>
                                                    <span v-if="!role.is_current">{{ formatDate(role.start_date) }} - {{ formatDate(role.end_date) }}</span>
                                                    <span v-else>{{ formatDate(role.start_date) }} - {{ __('Present') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.experiences.edit', experience.id)" class="text-cyan-400 hover:text-cyan-300 mr-4 transition-colors">{{ __('Edit') }}</Link>
                                            <Link :href="route('admin.experiences.destroy', experience.id)" method="delete" as="button" class="text-red-400 hover:text-red-300 transition-colors">{{ __('Delete') }}</Link>
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
