<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    availableSkills: Array,
});

const form = useForm({
    title: '',
    description: '',
    project_url: '',
    github_url: '',
    tech_stack: [],
    is_featured: false,
    sort_order: 0,
    skills: [],
    // Gallery fields
    images: [], // File objects
    image_metadata: '', // JSON string
});

const galleryItems = ref([]); // { file, preview, description, is_principal, id }
const nextItemId = ref(1);

const addGalleryItem = (e) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const id = nextItemId.value++;
        
        const reader = new FileReader();
        reader.onload = (ev) => {
            galleryItems.value.push({
                id,
                file: file,
                preview: ev.target.result,
                description: '',
                is_principal: galleryItems.value.length === 0, // Mark first as principal by default
            });
        };
        reader.readAsDataURL(file);
    }
};

const removeGalleryItem = (id) => {
    galleryItems.value = galleryItems.value.filter(item => item.id !== id);
    // Ensure one is principal if list not empty
    if (galleryItems.value.length > 0 && !galleryItems.value.some(item => item.is_principal)) {
        galleryItems.value[0].is_principal = true;
    }
};

const setPrincipal = (id) => {
    galleryItems.value.forEach(item => {
        item.is_principal = item.id === id;
    });
};

const toggleSkill = (id) => {
    const idx = form.skills.indexOf(id);
    if (idx === -1) {
        form.skills.push(id);
    } else {
        form.skills.splice(idx, 1);
    }
};

const groupedSkills = computed(() => {
    const groups = {};
    (props.availableSkills || []).forEach(skill => {
        const cat = skill.category || 'Other';
        if (!groups[cat]) groups[cat] = [];
        groups[cat].push(skill);
    });
    return groups;
});

const submit = () => {
    // Prepare images and metadata
    const files = [];
    const metadata = {
        descriptions: {}, // for new images
        new_info: {}, // which indices are principal
    };

    galleryItems.value.forEach((item, index) => {
        files.push(item.file);
        metadata['new_' + index] = {
            is_principal: item.is_principal
        };
        // We'll store description by name later in controller, 
        // but frontend sends it now mapped to the temp index for new ones
        metadata.descriptions['new_' + index] = item.description;
    });

    form.images = files;
    form.image_metadata = JSON.stringify(metadata);

    form.post(route('admin.projects.index'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Create Project" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400">
                    Create New Project
                </h2>
                <Link :href="route('admin.projects.index')" class="text-gray-400 hover:text-white transition-colors text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-6">
                    <form @submit.prevent="submit" class="space-y-8">
                        
                        <!-- Gallery Manager -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label class="block text-sm font-medium text-gray-300">Project Gallery (Screenshots)</label>
                                <label class="px-4 py-2 bg-purple-600 hover:bg-purple-500 rounded-xl text-xs font-bold text-white cursor-pointer transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Add Images
                                    <input type="file" multiple accept="image/*" @change="addGalleryItem" class="hidden" />
                                </label>
                            </div>

                            <div v-if="galleryItems.length === 0" class="flex flex-col items-center justify-center py-12 px-6 rounded-2xl bg-gray-900/50 border-2 border-dashed border-white/10">
                                <svg class="w-12 h-12 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <p class="text-gray-500 text-sm">No images added yet. Add prints of your work!</p>
                            </div>

                            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="item in galleryItems" :key="item.id" class="relative bg-white/5 border border-white/10 rounded-2xl overflow-hidden group">
                                    <div class="aspect-video relative overflow-hidden bg-black/40">
                                        <img :src="item.preview" class="w-full h-full object-contain" />
                                        <div class="absolute top-2 right-2 flex gap-2">
                                            <button type="button" @click="removeGalleryItem(item.id)" class="p-1.5 bg-red-500/80 hover:bg-red-500 rounded-lg text-white shadow-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                        <div class="absolute bottom-2 left-2">
                                            <button type="button" @click="setPrincipal(item.id)" 
                                                :class="[
                                                    'px-2 py-1 rounded-md text-[10px] uppercase font-bold transition-all shadow-lg',
                                                    item.is_principal ? 'bg-cyan-500 text-white' : 'bg-gray-800/80 text-gray-400 hover:text-white'
                                                ]">
                                                {{ item.is_principal ? 'Principal' : 'Set Principal' }}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <textarea 
                                            v-model="item.description" 
                                            placeholder="Image description..."
                                            rows="2"
                                            class="w-full text-xs bg-gray-900/50 border border-white/5 rounded-lg text-gray-300 focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.images" class="text-red-400 text-xs mt-2">{{ form.errors.images }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="title" class="block text-sm font-medium text-gray-300">Project Title</label>
                                <input id="title" v-model="form.title" type="text" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                <div v-if="form.errors.title" class="text-red-400 text-xs mt-1">{{ form.errors.title }}</div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-300">Description</label>
                                <textarea id="description" v-model="form.description" rows="5" required class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"></textarea>
                                <div v-if="form.errors.description" class="text-red-400 text-xs mt-1">{{ form.errors.description }}</div>
                            </div>

                            <div>
                                <label for="project_url" class="block text-sm font-medium text-gray-300">Project URL (Live Demo)</label>
                                <input id="project_url" v-model="form.project_url" type="url" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                <div v-if="form.errors.project_url" class="text-red-400 text-xs mt-1">{{ form.errors.project_url }}</div>
                            </div>

                            <div>
                                <label for="github_url" class="block text-sm font-medium text-gray-300">GitHub URL</label>
                                <input id="github_url" v-model="form.github_url" type="url" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                <div v-if="form.errors.github_url" class="text-red-400 text-xs mt-1">{{ form.errors.github_url }}</div>
                            </div>

                            <div class="flex items-center gap-6">
                                <div class="flex items-center">
                                    <input id="is_featured" v-model="form.is_featured" type="checkbox" class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50" />
                                    <label for="is_featured" class="ml-2 block text-sm font-medium text-gray-300">Featured Project</label>
                                </div>
                                
                                <div class="flex-1">
                                    <label for="sort_order" class="block text-sm font-medium text-gray-300">Sort Order</label>
                                    <input id="sort_order" v-model="form.sort_order" type="number" class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors" />
                                </div>
                            </div>
                        </div>

                        <!-- Skills Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-3">Associated Skills</label>
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

                        <div class="flex items-center gap-4 pt-6 border-t border-white/10">
                            <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-500 hover:to-cyan-500 rounded-xl font-bold text-white shadow-[0_0_20px_rgba(168,85,247,0.4)] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50">
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Project</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
