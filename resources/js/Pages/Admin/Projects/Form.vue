<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useCachedForm } from '@/Composables/useCachedForm';

const props = defineProps({
    project: Object,
});

const isEditing = !!props.project;
const cacheKey = isEditing ? `project_edit_${props.project.id}` : 'project_create';

const form = useCachedForm(cacheKey, {
    title: props.project?.title || '',
    description: props.project?.description || '',
    image_url: props.project?.image_url || '',
    project_url: props.project?.project_url || '',
    github_url: props.project?.github_url || '',
    tech_stack: props.project?.tech_stack || [],
    is_featured: props.project?.is_featured || false,
    sort_order: props.project?.sort_order || 0,
});

const submit = () => {
    if (isEditing) {
        form.put(route('admin.projects.update', props.project.id));
    } else {
        form.post(route('admin.projects.store'));
    }
};

const addTag = (e) => {
    const val = e.target.value.trim();
    if (val && !form.tech_stack.includes(val)) {
        form.tech_stack.push(val);
    }
    e.target.value = '';
};

const removeTag = (tag) => {
    form.tech_stack = form.tech_stack.filter(t => t !== tag);
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Project' : 'New Project'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ isEditing ? 'Edit Project' : 'New Project' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                            <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
                            <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea v-model="form.description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required></textarea>
                            <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                        </div>

                         <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Image URL</label>
                            <input v-model="form.image_url" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project URL</label>
                                <input v-model="form.project_url" type="url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">GitHub URL</label>
                                <input v-model="form.github_url" type="url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tech Stack (Press Enter to add)</label>
                            <input @keydown.enter.prevent="addTag" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Vue.js, Laravel..." />
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span v-for="tag in form.tech_stack" :key="tag" class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm flex items-center gap-1">
                                    {{ tag }}
                                    <button type="button" @click="removeTag(tag)" class="text-indigo-500 hover:text-indigo-900">&times;</button>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2">
                                <input v-model="form.is_featured" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Featured Project</span>
                            </label>
                            
                             <label class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Order:</span>
                                <input v-model="form.sort_order" type="number" class="w-20 rounded border-gray-300 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <Link :href="route('admin.projects.index')" class="text-gray-600 dark:text-gray-400 hover:text-gray-900">Cancel</Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white"
                            >
                                {{ isEditing ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
