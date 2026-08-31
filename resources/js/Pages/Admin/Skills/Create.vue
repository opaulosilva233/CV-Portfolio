<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useCachedForm } from '@/Composables/useCachedForm';
import IconPicker from '@/Components/IconPicker.vue';

const props = defineProps({
    availableProjects: Array,
    availableEducations: Array,
    availableExperiences: Array,
});

const form = useCachedForm('skill_create', {
    name: '',
    category: '',
    proficiency: 1,
    icon: '',
    projects: [],
    educations: [],
    experiences: [],
});

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Skills', href: route('admin.skills.index') },
    { label: 'Create', active: true },
];

const setStars = (n) => {
    form.proficiency = n;
};

const toggleItem = (list, id) => {
    const idx = list.indexOf(id);
    if (idx === -1) {
        list.push(id);
    } else {
        list.splice(idx, 1);
    }
};

const submit = () => {
    form.post(route('admin.skills.index'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="__('Add New Skill')" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex flex-col">
                    <Breadcrumbs :items="breadcrumbs" />
                    <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400">
                        {{ __('Add New Skill') }}
                    </h2>
                </div>
                <Link :href="route('admin.skills.index')" class="text-gray-400 hover:text-white transition-colors text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    {{ __('Back to List') }}
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-6 max-w-2xl">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300">{{ __('Skill Name (e.g. Vue.js, Laravel)') }}</label>
                            <input id="name" v-model="form.name" type="text" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-cyan-500 focus:ring-cyan-500 transition-colors" />
                            <div v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-300">{{ __('Category') }}</label>
                            <select id="category" v-model="form.category" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-cyan-500 focus:ring-cyan-500 transition-colors [&>option]:bg-gray-900">
                                <option value="" disabled>{{ __('Select a category') }}</option>
                                <option value="frontend">{{ __('Frontend') }}</option>
                                <option value="backend">{{ __('Backend') }}</option>
                                <option value="database">{{ __('Database') }}</option>
                                <option value="tools">{{ __('Tools & DevOps') }}</option>
                                <option value="soft">{{ __('Soft Skills') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                            <div v-if="form.errors.category" class="text-red-400 text-xs mt-1">{{ form.errors.category }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('Proficiency') }}</label>
                            <div class="flex items-center gap-1">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    @click="setStars(star)"
                                    class="transition-all duration-200 hover:scale-110 focus:outline-none"
                                >
                                    <svg class="w-8 h-8" :class="star <= form.proficiency ? 'text-yellow-400 drop-shadow-[0_0_6px_rgba(250,204,21,0.5)]' : 'text-gray-600'" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </button>
                                <span class="ml-3 text-cyan-400 font-mono text-sm">{{ form.proficiency }} / 5</span>
                            </div>
                            <div v-if="form.errors.proficiency" class="text-red-400 text-xs mt-1">{{ form.errors.proficiency }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-1 md:col-span-2">
                                <label for="icon" class="block text-sm font-medium text-gray-300 mb-2">{{ __('Icon') }}</label>
                                <IconPicker id="icon" v-model="form.icon" :category="form.category" />
                            </div>
                        </div>

                        <!-- Associations -->
                        <div class="pt-4 border-t border-white/10 space-y-5">
                            <h3 class="text-lg font-semibold text-white">{{ __('Associate with') }}</h3>

                            <!-- Projects -->
                            <div>
                                <label class="block text-xs font-semibold text-blue-400 uppercase tracking-wider mb-2">{{ __('Projects') }}</label>
                                <div v-if="availableProjects && availableProjects.length > 0" class="flex flex-wrap gap-2">
                                    <button
                                        v-for="project in availableProjects"
                                        :key="project.id"
                                        type="button"
                                        @click="toggleItem(form.projects, project.id)"
                                        :class="[
                                            'px-3 py-1.5 rounded-lg text-sm font-medium border transition-all duration-200',
                                            form.projects.includes(project.id)
                                                ? 'bg-blue-600/30 border-blue-500/60 text-blue-200 shadow-[0_0_8px_rgba(59,130,246,0.3)]'
                                                : 'bg-white/5 border-white/10 text-gray-400 hover:border-white/30 hover:text-gray-200'
                                        ]"
                                    >
                                        {{ project.title }}
                                    </button>
                                </div>
                                <p v-else class="text-gray-500 text-sm italic">{{ __('No projects created yet.') }}</p>
                            </div>

                            <!-- Education -->
                            <div>
                                <label class="block text-xs font-semibold text-purple-400 uppercase tracking-wider mb-2">{{ __('Education / Certificates') }}</label>
                                <div v-if="availableEducations && availableEducations.length > 0" class="flex flex-wrap gap-2">
                                    <button
                                        v-for="edu in availableEducations"
                                        :key="edu.id"
                                        type="button"
                                        @click="toggleItem(form.educations, edu.id)"
                                        :class="[
                                            'px-3 py-1.5 rounded-lg text-sm font-medium border transition-all duration-200',
                                            form.educations.includes(edu.id)
                                                ? 'bg-purple-600/30 border-purple-500/60 text-purple-200 shadow-[0_0_8px_rgba(168,85,247,0.3)]'
                                                : 'bg-white/5 border-white/10 text-gray-400 hover:border-white/30 hover:text-gray-200'
                                        ]"
                                    >
                                        {{ edu.institution }} {{ edu.degree ? '— ' + edu.degree : '' }}
                                    </button>
                                </div>
                                <p v-else class="text-gray-500 text-sm italic">{{ __('No education records created yet.') }}</p>
                            </div>

                            <!-- Experiences -->
                            <div>
                                <label class="block text-xs font-semibold text-green-400 uppercase tracking-wider mb-2">{{ __('Experiences') }}</label>
                                <div v-if="availableExperiences && availableExperiences.length > 0" class="flex flex-wrap gap-2">
                                    <button
                                        v-for="exp in availableExperiences"
                                        :key="exp.id"
                                        type="button"
                                        @click="toggleItem(form.experiences, exp.id)"
                                        :class="[
                                            'px-3 py-1.5 rounded-lg text-sm font-medium border transition-all duration-200',
                                            form.experiences.includes(exp.id)
                                                ? 'bg-green-600/30 border-green-500/60 text-green-200 shadow-[0_0_8px_rgba(34,197,94,0.3)]'
                                                : 'bg-white/5 border-white/10 text-gray-400 hover:border-white/30 hover:text-gray-200'
                                        ]"
                                    >
                                        {{ exp.company }} {{ exp.roles && exp.roles.length > 0 ? '— ' + exp.roles.map(r => r.role).join(', ') : '' }}
                                    </button>
                                </div>
                                <p v-else class="text-gray-500 text-sm italic">{{ __('No experiences created yet.') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 rounded-xl font-bold text-white shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:opacity-50">
                                <span v-if="form.processing">{{ __('Creating...') }}</span>
                                <span v-else>{{ __('Create Skill') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
