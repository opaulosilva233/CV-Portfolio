<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    company: '',
    role: '',
    start_date: '',
    end_date: '',
    is_current: false,
    description: '',
    location: '',
    sort_order: 0,
});

const submit = () => {
    form.post(route('admin.experiences.index'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Experience" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400">
                    Add New Experience
                </h2>
                <Link :href="route('admin.experiences.index')" class="text-gray-400 hover:text-white transition-colors text-sm font-medium flex items-center gap-2">
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
                                <label for="company" class="block text-sm font-medium text-gray-300">Company</label>
                                <input id="company" v-model="form.company" type="text" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                <div v-if="form.errors.company" class="text-red-400 text-xs mt-1">{{ form.errors.company }}</div>
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-300">Role / Job Title</label>
                                <input id="role" v-model="form.role" type="text" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                <div v-if="form.errors.role" class="text-red-400 text-xs mt-1">{{ form.errors.role }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-300">Start Date</label>
                                <input id="start_date" v-model="form.start_date" type="date" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors [color-scheme:dark]" />
                                <div v-if="form.errors.start_date" class="text-red-400 text-xs mt-1">{{ form.errors.start_date }}</div>
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-300" :class="{ 'opacity-50': form.is_current }">End Date</label>
                                <input id="end_date" v-model="form.end_date" type="date" :disabled="form.is_current" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed [color-scheme:dark]" />
                                <div v-if="form.errors.end_date" class="text-red-400 text-xs mt-1">{{ form.errors.end_date }}</div>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input id="is_current" v-model="form.is_current" type="checkbox" class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50" />
                            <label for="is_current" class="ml-2 block text-sm font-medium text-gray-300">I currently work here</label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-300">Location</label>
                                <input id="location" v-model="form.location" type="text" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                            </div>
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-300">List Order (0 is first)</label>
                                <input id="sort_order" v-model="form.sort_order" type="number" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300">Description / Responsibilities</label>
                            <textarea id="description" v-model="form.description" rows="5" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"></textarea>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-500 hover:to-cyan-500 rounded-xl font-bold text-white shadow-[0_0_15px_rgba(168,85,247,0.4)] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50">
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Experience</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
