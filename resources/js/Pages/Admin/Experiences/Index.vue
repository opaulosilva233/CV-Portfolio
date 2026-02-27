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
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Role') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Dates') }}</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10 bg-transparent">
                                    <tr v-for="experience in experiences" :key="experience.id" class="hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white">{{ experience.company }}</div>
                                            <div class="text-sm text-gray-400">{{ experience.location }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-300">{{ experience.role }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-500/20 text-green-400 border border-green-500/30 shadow-[0_0_10px_rgba(34,197,94,0.2)]" v-if="experience.is_current">
                                                {{ __('Current') }}
                                            </span>
                                            <div class="text-sm text-gray-400 mt-1" v-if="!experience.is_current">{{ formatDate(experience.start_date) }} - {{ formatDate(experience.end_date) }}</div>
                                            <div class="text-sm text-gray-400 mt-1" v-else>{{ formatDate(experience.start_date) }} - {{ __('Present') }}</div>
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
