<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    project: Object,
});

const form = useForm({
    title: props.project.title,
    description: props.project.description,
    tech_stack: Array.isArray(props.project.tech_stack) ? props.project.tech_stack.join(', ') : props.project.tech_stack,
    github_url: props.project.github_url,
    project_url: props.project.project_url,
    image_url: props.project.image_url,
    is_featured: props.project.is_featured,
    sort_order: props.project.sort_order,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        tech_stack: data.tech_stack ? data.tech_stack.split(',').map(s => s.trim()).filter(s => s) : []
    })).put(route('admin.projects.update', props.project.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Project" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400">
                    Edit Project
                </h2>
                <Link :href="route('admin.projects.index')" class="text-gray-400 hover:text-white transition-colors text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-6 max-w-3xl">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300">Project Title</label>
                            <input id="title" v-model="form.title" type="text" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                            <div v-if="form.errors.title" class="text-red-400 text-xs mt-1">{{ form.errors.title }}</div>
                        </div>

                        <div>
                            <label for="tech_stack" class="block text-sm font-medium text-gray-300">Tech Stack (comma separated)</label>
                            <input id="tech_stack" v-model="form.tech_stack" type="text" placeholder="Vue, Laravel, Tailwind" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                            <div v-if="form.errors.tech_stack" class="text-red-400 text-xs mt-1">{{ form.errors.tech_stack }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="github_url" class="block text-sm font-medium text-gray-300">GitHub URL</label>
                                <input id="github_url" v-model="form.github_url" type="url" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                            </div>
                            <div>
                                <label for="project_url" class="block text-sm font-medium text-gray-300">Live Project URL</label>
                                <input id="project_url" v-model="form.project_url" type="url" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                            </div>
                        </div>

                        <div>
                            <label for="image_url" class="block text-sm font-medium text-gray-300">Cover Image URL</label>
                            <input id="image_url" v-model="form.image_url" type="text" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300">Project Description</label>
                            <textarea id="description" v-model="form.description" rows="5" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-blue-500 focus:ring-blue-500 transition-colors"></textarea>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="flex items-center">
                                <input id="is_featured" v-model="form.is_featured" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                <label for="is_featured" class="ml-2 block text-sm font-medium text-gray-300">Feature this project</label>
                            </div>

                            <div class="flex-1 flex justify-end items-center gap-2">
                                <label for="sort_order" class="block text-sm font-medium text-gray-300">List Order</label>
                                <input id="sort_order" v-model="form.sort_order" type="number" class="w-24 rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-blue-500 focus:ring-blue-500 transition-colors" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 rounded-xl font-bold text-white shadow-[0_0_15px_rgba(59,130,246,0.4)] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>Save Changes</span>
                            </button>
                            <transition
                                enter-active-class="transition ease-out duration-300"
                                enter-from-class="opacity-0 -translate-y-2"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-300"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 -translate-y-2"
                            >
                                <p v-if="form.recentlySuccessful" class="text-sm font-medium text-green-400 bg-green-500/10 px-3 py-1 rounded-lg border border-green-500/20">
                                    Saved successfully.
                                </p>
                            </transition>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
