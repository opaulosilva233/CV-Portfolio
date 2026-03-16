<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useCachedForm } from '@/Composables/useCachedForm';
import { computed, ref } from 'vue';

const props = defineProps({
    education: Object,
    availableSkills: Array,
});

const form = useCachedForm(`education_edit_${props.education.id}`, {
    _method: 'PUT',
    institution: props.education.institution,
    degree: props.education.degree || '',
    image: null,
    remove_image: false,
    start_date: props.education.start_date ? props.education.start_date.substring(0, 10) : '',
    end_date: props.education.end_date ? props.education.end_date.substring(0, 10) : '',
    is_current: props.education.is_current || false,
    type: props.education.type || 'education',
    url: props.education.url || '',
    description: props.education.description || '',
    skills: props.education.skills ? props.education.skills.map(s => s.id) : [],
});

const imagePreview = ref(null);
const currentImageUrl = ref(props.education.image_url || null);

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

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        form.remove_image = false;
        const reader = new FileReader();
        reader.onload = (ev) => {
            imagePreview.value = ev.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const clearImage = () => {
    form.image = null;
    imagePreview.value = null;
    if (currentImageUrl.value) {
        form.remove_image = true;
        currentImageUrl.value = null;
    }
};

const submit = () => {
    form.post(route('admin.education.update', props.education.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Education" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400">
                    Edit Education / Certificate
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
                        
                        <!-- Institution Logo Upload -->
                        <div class="pb-6 border-b border-white/10">
                            <label class="block text-sm font-medium text-gray-300 mb-3">Institution Logo</label>
                            <div class="flex items-start gap-6">
                                <!-- Existing image -->
                                <div v-if="currentImageUrl && !imagePreview" class="relative flex-shrink-0">
                                    <img :src="currentImageUrl" alt="Current logo" class="w-20 h-20 rounded-xl object-contain bg-white/10 border border-white/10 p-1" />
                                    <button type="button" @click="clearImage" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 hover:bg-red-400 rounded-full flex items-center justify-center text-white transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                                <!-- New preview -->
                                <div v-if="imagePreview" class="relative flex-shrink-0">
                                    <img :src="imagePreview" alt="Logo preview" class="w-20 h-20 rounded-xl object-contain bg-white/10 border border-white/10 p-1" />
                                    <button type="button" @click="clearImage" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 hover:bg-red-400 rounded-full flex items-center justify-center text-white transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                                <!-- Upload area -->
                                <label class="flex-1 flex flex-col items-center justify-center px-6 py-4 rounded-xl bg-gray-900/50 border-2 border-dashed border-white/10 hover:border-purple-500/50 cursor-pointer transition-colors group">
                                    <svg class="w-8 h-8 text-gray-500 group-hover:text-purple-400 transition-colors mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm text-gray-400 group-hover:text-gray-300 transition-colors">Click to upload logo</span>
                                    <span class="text-xs text-gray-500 mt-1">PNG, JPG, GIF, WEBP, SVG (max 2MB)</span>
                                    <input type="file" accept="image/*" @change="handleImageChange" class="hidden" />
                                </label>
                            </div>
                            <div v-if="form.errors.image" class="text-red-400 text-xs mt-2">{{ form.errors.image }}</div>
                        </div>

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

                        <!-- Skills Selection -->
                        <div>
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
