<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    skill: Object,
});

const form = useForm({
    name: props.skill.name,
    category: props.skill.category,
    proficiency: props.skill.proficiency,
    icon: props.skill.icon,
    sort_order: props.skill.sort_order,
});

const submit = () => {
    form.put(route('admin.skills.update', props.skill.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Skill" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400">
                    Edit Skill
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
                            <label for="name" class="block text-sm font-medium text-gray-300">Skill Name</label>
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
                                <input id="icon" v-model="form.icon" type="text" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-cyan-500 focus:ring-cyan-500 transition-colors" />
                            </div>
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-300">List Order</label>
                                <input id="sort_order" v-model="form.sort_order" type="number" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-cyan-500 focus:ring-cyan-500 transition-colors" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 rounded-xl font-bold text-white shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:opacity-50">
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
