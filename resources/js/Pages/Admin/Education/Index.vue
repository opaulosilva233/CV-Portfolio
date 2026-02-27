<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    educations: Array,
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-PT', { month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Education" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-lg sm:text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate mr-4">
                    {{ __('Education & Certificates Management') }}
                </h2>
                <Link :href="route('admin.education.create')" class="flex-shrink-0 px-4 py-2 sm:px-5 sm:py-2.5 bg-purple-600/80 hover:bg-purple-500 border border-purple-500/50 rounded-xl font-semibold text-xs text-white uppercase tracking-wider shadow-[0_0_15px_rgba(168,85,247,0.4)] transition-all duration-300">
                    <span class="hidden sm:inline">{{ __('Add New') }}</span>
                    <span class="sm:hidden">{{ __('Add') }}</span>
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
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Institution & Degree') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Dates') }}</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Type') }}</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10 bg-transparent">
                                    <tr v-for="education in educations" :key="education.id" class="hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white">{{ education.institution }}</div>
                                            <div class="text-sm text-gray-400">{{ education.degree }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-xs text-gray-400 flex items-center gap-2">
                                                <span class="px-2 py-0.5 inline-flex font-semibold rounded bg-green-500/20 text-green-400 border border-green-500/30 text-[10px]" v-if="education.is_current">
                                                    {{ __('Current') }}
                                                </span>
                                                <span v-if="!education.is_current && (education.start_date || education.end_date)">
                                                    <template v-if="education.start_date && education.end_date">
                                                        {{ formatDate(education.start_date) }} - {{ formatDate(education.end_date) }}
                                                    </template>
                                                    <template v-else-if="education.start_date">
                                                        {{ formatDate(education.start_date) }} - {{ __('Present') }}
                                                    </template>
                                                    <template v-else-if="education.end_date">
                                                        {{ formatDate(education.end_date) }}
                                                    </template>
                                                </span>
                                                <span v-else-if="education.is_current">
                                                    {{ education.start_date ? formatDate(education.start_date) + ' - ' : '' }}{{ __('Present') }}
                                                </span>
                                                <span v-else class="text-gray-500 italic">{{ __('No dates provided') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <span 
                                                class="px-3 py-1 text-[10px] font-bold uppercase rounded-full border border-white/10"
                                                :class="education.type === 'certificate' ? 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30' : 'bg-purple-500/20 text-purple-400 border-purple-500/30'"
                                            >
                                                {{ education.type === 'certificate' ? __('Certificate') : __('Education') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.education.edit', education.id)" class="text-cyan-400 hover:text-cyan-300 mr-4 transition-colors">{{ __('Edit') }}</Link>
                                            <Link :href="route('admin.education.destroy', education.id)" method="delete" as="button" class="text-red-400 hover:text-red-300 transition-colors">{{ __('Delete') }}</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="educations.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-sm">
                                            {{ __('No records found. Click "Add New" to get started.') }}
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
