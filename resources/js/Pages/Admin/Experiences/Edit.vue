<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    experience: Object,
    availableSkills: Array,
});

const form = useForm({
    company: props.experience.company,
    location: props.experience.location,
    roles: props.experience.roles && props.experience.roles.length > 0
        ? props.experience.roles.map(r => ({ ...r }))
        : [
            {
                role: '',
                start_date: '',
                end_date: '',
                is_current: false,
                description: '',
            }
        ],
    skills: props.experience.skills ? props.experience.skills.map(s => s.id) : [],
});

const groupedSkills = computed(() => {
    const groups = {};
    (props.availableSkills || []).forEach(skill => {
        const cat = skill.category || 'Other';
        if (!groups[cat]) groups[cat] = [];
        groups[cat].push(skill);
    });
    return groups;
});

const toggleSkill = (id) => {
    const idx = form.skills.indexOf(id);
    if (idx === -1) {
        form.skills.push(id);
    } else {
        form.skills.splice(idx, 1);
    }
};

const addRole = () => {
    form.roles.push({
        role: '',
        start_date: '',
        end_date: '',
        is_current: false,
        description: '',
    });
};

const removeRole = (index) => {
    if (form.roles.length > 1) {
        form.roles.splice(index, 1);
    }
};

const submit = () => {
    form.put(route('admin.experiences.update', props.experience.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Experience" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400">
                    Edit Experience
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
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-white/10">
                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-300">Company</label>
                                <input id="company" v-model="form.company" type="text" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                <div v-if="form.errors.company" class="text-red-400 text-xs mt-1">{{ form.errors.company }}</div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="location" class="block text-sm font-medium text-gray-300">Location</label>
                                <input id="location" v-model="form.location" type="text" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                <div v-if="form.errors.location" class="text-red-400 text-xs mt-1">{{ form.errors.location }}</div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-white">Roles</h3>
                                <button type="button" @click="addRole" class="text-xs font-medium bg-purple-600/20 hover:bg-purple-600/40 text-purple-300 px-3 py-1.5 rounded-lg border border-purple-500/30 transition-colors">
                                    + Add Role
                                </button>
                            </div>

                            <div v-for="(role, index) in form.roles" :key="index" class="p-4 rounded-xl bg-white/5 border border-white/10 relative mt-4">
                                <button v-if="form.roles.length > 1" type="button" @click="removeRole(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-400 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label :for="'role_' + index" class="block text-sm font-medium text-gray-300">Role / Job Title</label>
                                        <input :id="'role_' + index" v-model="role.role" type="text" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                        <div v-if="form.errors[`roles.${index}.role`]" class="text-red-400 text-xs mt-1">{{ form.errors[`roles.${index}.role`] }}</div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label :for="'start_date_' + index" class="block text-sm font-medium text-gray-300">Start Date</label>
                                            <input :id="'start_date_' + index" v-model="role.start_date" type="date" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors [color-scheme:dark]" />
                                            <div v-if="form.errors[`roles.${index}.start_date`]" class="text-red-400 text-xs mt-1">{{ form.errors[`roles.${index}.start_date`] }}</div>
                                        </div>

                                        <div>
                                            <label :for="'end_date_' + index" class="block text-sm font-medium text-gray-300" :class="{ 'opacity-50': role.is_current }">End Date</label>
                                            <input :id="'end_date_' + index" v-model="role.end_date" type="date" :disabled="role.is_current" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed [color-scheme:dark]" />
                                            <div v-if="form.errors[`roles.${index}.end_date`]" class="text-red-400 text-xs mt-1">{{ form.errors[`roles.${index}.end_date`] }}</div>
                                        </div>
                                    </div>

                                    <div class="flex items-center">
                                        <input :id="'is_current_' + index" v-model="role.is_current" @change="() => { if(role.is_current) role.end_date = null }" type="checkbox" class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50" />
                                        <label :for="'is_current_' + index" class="ml-2 block text-sm font-medium text-gray-300">I currently work here</label>
                                    </div>

                                    <div>
                                        <label :for="'description_' + index" class="block text-sm font-medium text-gray-300">Description / Responsibilities</label>
                                        <textarea :id="'description_' + index" v-model="role.description" rows="4" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"></textarea>
                                        <div v-if="form.errors[`roles.${index}.description`]" class="text-red-400 text-xs mt-1">{{ form.errors[`roles.${index}.description`] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Skills Selection -->
                        <div class="pt-6 border-t border-white/10">
                            <label class="block text-sm font-medium text-gray-300 mb-3">Associated Skills</label>
                            <div v-if="Object.keys(groupedSkills).length === 0" class="text-gray-500 text-sm italic">
                                No skills created yet. Create skills first in the Skills section.
                            </div>
                            <div v-for="(skills, category) in groupedSkills" :key="category" class="mb-4">
                                <p class="text-xs font-semibold text-purple-400 uppercase tracking-wider mb-2">{{ category }}</p>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="skill in skills"
                                        :key="skill.id"
                                        type="button"
                                        @click="toggleSkill(skill.id)"
                                        :class="[
                                            'px-3 py-1.5 rounded-lg text-sm font-medium border transition-all duration-200',
                                            form.skills.includes(skill.id)
                                                ? 'bg-purple-600/30 border-purple-500/60 text-purple-200 shadow-[0_0_8px_rgba(168,85,247,0.3)]'
                                                : 'bg-white/5 border-white/10 text-gray-400 hover:border-white/30 hover:text-gray-200'
                                        ]"
                                    >
                                        {{ skill.name }}
                                    </button>
                                </div>
                            </div>
                            <div v-if="form.errors.skills" class="text-red-400 text-xs mt-1">{{ form.errors.skills }}</div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-500 hover:to-cyan-500 rounded-xl font-bold text-white shadow-[0_0_15px_rgba(168,85,247,0.4)] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50">
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
