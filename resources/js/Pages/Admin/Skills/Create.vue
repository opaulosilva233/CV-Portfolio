<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    category: '',
    proficiency: 50,
    icon: '',
    sort_order: 0,
});

const submit = () => {
    form.post(route('admin.skills.index'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Skill" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400">
                    Add New Skill
                </h2>
                <Link :href="route('admin.skills.index')" class="text-gray-400 hover:text-white transition-colors text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-6 max-w-2xl">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300">Skill Name (e.g. Vue.js, Laravel)</label>
                            <input id="name" v-model="form.name" type="text" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-cyan-500 focus:ring-cyan-500 transition-colors" />
                            <div v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-300">Category</label>
                            <select id="category" v-model="form.category" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-cyan-500 focus:ring-cyan-500 transition-colors [&>option]:bg-gray-900">
                                <option value="" disabled>Select a category</option>
                                <option value="frontend">Frontend</option>
                                <option value="backend">Backend</option>
                                <option value="database">Database</option>
                                <option value="tools">Tools & DevOps</option>
                                <option value="soft">Soft Skills</option>
                                <option value="other">Other</option>
                            </select>
                            <div v-if="form.errors.category" class="text-red-400 text-xs mt-1">{{ form.errors.category }}</div>
                        </div>

                        <div>
                            <div class="flex justify-between">
                                <label for="proficiency" class="block text-sm font-medium text-gray-300">Proficiency (%)</label>
                                <span class="text-cyan-400 font-mono text-sm">{{ form.proficiency }}%</span>
                            </div>
                            <input id="proficiency" v-model="form.proficiency" type="range" min="0" max="100" class="mt-2 block w-full accent-cyan-500" />
                            <div v-if="form.errors.proficiency" class="text-red-400 text-xs mt-1">{{ form.errors.proficiency }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-300">Icon (SVG or Icon Class)</label>
                                <input id="icon" v-model="form.icon" type="text" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-cyan-500 focus:ring-cyan-500 transition-colors" placeholder="<svg>...</svg>" />
                            </div>
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-300">List Order</label>
                                <input id="sort_order" v-model="form.sort_order" type="number" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-cyan-500 focus:ring-cyan-500 transition-colors" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 rounded-xl font-bold text-white shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:opacity-50">
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Skill</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
