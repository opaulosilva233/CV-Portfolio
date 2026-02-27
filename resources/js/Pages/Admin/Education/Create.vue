<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    institution: '',
    degree: '',
    start_date: '',
    end_date: '',
    is_current: false,
    type: 'education',
    url: '',
    description: '',
});

const submit = () => {
    form.post(route('admin.education.index'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Education" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400">
                    Add New Education / Certificate
                </h2>
                <Link :href="route('admin.education.index')" class="text-gray-400 hover:text-white transition-colors text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-6 max-w-3xl">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="institution" class="block text-sm font-medium text-gray-300">Institution / Issuer</label>
                                <input id="institution" v-model="form.institution" type="text" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                <div v-if="form.errors.institution" class="text-red-400 text-xs mt-1">{{ form.errors.institution }}</div>
                            </div>
                            
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-300">Type</label>
                                <select id="type" v-model="form.type" required class="mt-2 block w-full rounded-xl bg-gray-900 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors">
                                    <option value="education">Education / Degree</option>
                                    <option value="certificate">Certificate</option>
                                </select>
                                <div v-if="form.errors.type" class="text-red-400 text-xs mt-1">{{ form.errors.type }}</div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="degree" class="block text-sm font-medium text-gray-300">Degree / Certificate Name</label>
                                <input id="degree" v-model="form.degree" type="text" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                <div v-if="form.errors.degree" class="text-red-400 text-xs mt-1">{{ form.errors.degree }}</div>
                            </div>

                            <div v-if="form.type === 'education'">
                                <label for="start_date" class="block text-sm font-medium text-gray-300">Start Date</label>
                                <input id="start_date" v-model="form.start_date" type="date" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors [color-scheme:dark]" />
                                <div v-if="form.errors.start_date" class="text-red-400 text-xs mt-1">{{ form.errors.start_date }}</div>
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-300" :class="{ 'opacity-50': form.is_current }">End Date</label>
                                <input id="end_date" v-model="form.end_date" type="date" :disabled="form.is_current" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed [color-scheme:dark]" />
                                <div v-if="form.errors.end_date" class="text-red-400 text-xs mt-1">{{ form.errors.end_date }}</div>
                            </div>

                            <div v-if="form.type === 'education'" class="md:col-span-2 flex items-center">
                                <input id="is_current" v-model="form.is_current" @change="() => { if(form.is_current) form.end_date = null }" type="checkbox" class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50" />
                                <label for="is_current" class="ml-2 block text-sm font-medium text-gray-300">{{ __('Currently studying / Present') }}</label>
                            </div>

                            <div class="md:col-span-2">
                                <label for="url" class="block text-sm font-medium text-gray-300">URL (Certificate Link or Credential URL)</label>
                                <input id="url" v-model="form.url" type="url" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                <div v-if="form.errors.url" class="text-red-400 text-xs mt-1">{{ form.errors.url }}</div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-300">Description</label>
                                <textarea id="description" v-model="form.description" rows="4" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"></textarea>
                                <div v-if="form.errors.description" class="text-red-400 text-xs mt-1">{{ form.errors.description }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-500 hover:to-cyan-500 rounded-xl font-bold text-white shadow-[0_0_15px_rgba(168,85,247,0.4)] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50">
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Record</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
