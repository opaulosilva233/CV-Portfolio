<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useCachedForm } from '@/Composables/useCachedForm';

const props = defineProps({
    settings: Object,
});

const form = useCachedForm('settings_edit', {
    name: props.settings.name?.value || '',
    job_title: props.settings.job_title?.value || '',
    bio: props.settings.bio?.value || '',
    hero_image: props.settings.hero_image?.value || '',
    hero_image_file: null,
    remove_hero_image: false,
    enable_hero_animation: props.settings.enable_hero_animation ? props.settings.enable_hero_animation.value : '1',
    contact_email: props.settings.contact_email?.value || '',
    footer_text: props.settings.footer_text?.value || '',
    seo_title: props.settings.seo_title?.value || '',
    seo_description: props.settings.seo_description?.value || '',
    seo_keywords: props.settings.seo_keywords?.value || '',
    social_github: props.settings.social_github?.value || '',
    social_linkedin: props.settings.social_linkedin?.value || '',
    social_twitter: props.settings.social_twitter?.value || '',
    social_instagram: props.settings.social_instagram?.value || '',
});

import { ref } from 'vue';
const imagePreview = ref(props.settings.hero_image?.value || null);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.hero_image_file = file;
        form.remove_hero_image = false; // Reset removal if they upload a new one
        const reader = new FileReader();
        reader.onload = (ev) => {
            imagePreview.value = ev.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const clearImage = () => {
    form.hero_image_file = null;
    form.remove_hero_image = true;
    imagePreview.value = null;
    form.hero_image = ''; // clear the hidden field if needed
};

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            // Toast or notification handled by layout usually
        },
    });
};
</script>

<template>
    <Head title="Site Settings" />

    <CyberAdminLayout>
        <template #header>
            <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400">
                Global Site Settings
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <form @submit.prevent="submit" class="space-y-6 max-w-4xl">
                    
                    <!-- General Settings -->
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-6">
                        <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">General Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300">Site/Owner Name</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                />
                            </div>

                            <div>
                                <label for="contact_email" class="block text-sm font-medium text-gray-300">Contact Email</label>
                                <input
                                    id="contact_email"
                                    v-model="form.contact_email"
                                    type="email"
                                    class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label for="footer_text" class="block text-sm font-medium text-gray-300">Footer Text</label>
                                <input
                                    id="footer_text"
                                    v-model="form.footer_text"
                                    type="text"
                                    placeholder="System Online."
                                    class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Hero Section -->
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-6">
                        <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Hero Section</h3>
                        <div class="space-y-6">
                            <div>
                                <label for="job_title" class="block text-sm font-medium text-gray-300">Hero Title / Job Title</label>
                                <input
                                    id="job_title"
                                    v-model="form.job_title"
                                    type="text"
                                    class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                />
                            </div>

                            <div>
                                <label for="bio" class="block text-sm font-medium text-gray-300">Hero Bio</label>
                                <textarea
                                    id="bio"
                                    v-model="form.bio"
                                    rows="4"
                                    class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                ></textarea>
                            </div>
                            
                            <!-- Hero Image Upload -->
                            <div class="pb-2">
                                <label class="block text-sm font-medium text-gray-300 mb-3">Hero Image</label>
                                <div class="flex items-start gap-6">
                                    <!-- Preview -->
                                    <div v-if="imagePreview" class="relative flex-shrink-0">
                                        <img :src="imagePreview" alt="Hero preview" class="w-20 h-20 rounded-xl object-contain bg-white/10 border border-white/10 p-1" />
                                        <button type="button" @click="clearImage" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 hover:bg-red-400 rounded-full flex items-center justify-center text-white transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <!-- Upload area -->
                                    <label class="flex-1 flex flex-col items-center justify-center px-6 py-4 rounded-xl bg-gray-900/50 border-2 border-dashed border-white/10 hover:border-purple-500/50 cursor-pointer transition-colors group">
                                        <svg class="w-8 h-8 text-gray-500 group-hover:text-purple-400 transition-colors mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm text-gray-400 group-hover:text-gray-300 transition-colors">Click to upload hero image</span>
                                        <span class="text-xs text-gray-500 mt-1">PNG, JPG, GIF, WEBP, SVG (max 2MB)</span>
                                        <input type="file" accept="image/*" @change="handleImageChange" class="hidden" />
                                    </label>
                                </div>
                                <div v-if="form.errors.hero_image_file" class="text-red-400 text-xs mt-2">{{ form.errors.hero_image_file }}</div>
                            </div>

                            <!-- Cyber Boot & Decrypt Animation Toggle -->
                            <div class="p-4 rounded-xl bg-gray-900/60 border border-purple-500/20 flex items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></div>
                                        <label for="enable_hero_animation" class="text-sm font-bold text-white tracking-wide">
                                            Cyber Boot & Decrypt Animation
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-400 max-w-xl">
                                        Enable/disable the holographic boot sequence, text decrypter, laser scan and 3D interactive avatar effects on the homepage hero.
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                    <input 
                                        type="checkbox" 
                                        id="enable_hero_animation"
                                        :checked="form.enable_hero_animation === '1' || form.enable_hero_animation === true"
                                        @change="form.enable_hero_animation = $event.target.checked ? '1' : '0'"
                                        class="sr-only peer"
                                    >
                                    <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-purple-600 peer-checked:to-cyan-500 shadow-inner"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- SEO & Metadata -->
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-6">
                        <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">SEO & Metadata</h3>
                        <div class="space-y-6">
                            <div>
                                <label for="seo_title" class="block text-sm font-medium text-gray-300">Meta Title</label>
                                <input
                                    id="seo_title"
                                    v-model="form.seo_title"
                                    type="text"
                                    class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                    placeholder="Portfolio"
                                />
                            </div>

                            <div>
                                <label for="seo_description" class="block text-sm font-medium text-gray-300">Meta Description</label>
                                <textarea
                                    id="seo_description"
                                    v-model="form.seo_description"
                                    rows="3"
                                    class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                ></textarea>
                            </div>
                            
                            <div>
                                <label for="seo_keywords" class="block text-sm font-medium text-gray-300">Meta Keywords (comma-separated)</label>
                                <input
                                    id="seo_keywords"
                                    v-model="form.seo_keywords"
                                    type="text"
                                    class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                                    placeholder="developer, portfolio, vue"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-6">
                        <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Social Links</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="social_github" class="block text-sm font-medium text-gray-300">GitHub URL</label>
                                <div class="mt-2 flex rounded-xl shadow-inner bg-gray-900/50 border border-white/10 overflow-hidden">
                                    <span class="inline-flex items-center px-3 border-r border-white/10 text-gray-400 sm:text-sm">
                                        <i class="fa-brands fa-github"></i>
                                    </span>
                                    <input
                                        id="social_github"
                                        v-model="form.social_github"
                                        type="url"
                                        class="flex-1 block w-full min-w-0 bg-transparent text-white border-0 focus:ring-purple-500 transition-colors sm:text-sm"
                                        placeholder="https://github.com/..."
                                    />
                                </div>
                            </div>

                            <div>
                                <label for="social_linkedin" class="block text-sm font-medium text-gray-300">LinkedIn URL</label>
                                <div class="mt-2 flex rounded-xl shadow-inner bg-gray-900/50 border border-white/10 overflow-hidden">
                                    <span class="inline-flex items-center px-3 border-r border-white/10 text-gray-400 sm:text-sm">
                                        <i class="fa-brands fa-linkedin"></i>
                                    </span>
                                    <input
                                        id="social_linkedin"
                                        v-model="form.social_linkedin"
                                        type="url"
                                        class="flex-1 block w-full min-w-0 bg-transparent text-white border-0 focus:ring-purple-500 transition-colors sm:text-sm"
                                        placeholder="https://linkedin.com/in/..."
                                    />
                                </div>
                            </div>

                            <div>
                                <label for="social_twitter" class="block text-sm font-medium text-gray-300">Twitter/X URL</label>
                                <div class="mt-2 flex rounded-xl shadow-inner bg-gray-900/50 border border-white/10 overflow-hidden">
                                    <span class="inline-flex items-center px-3 border-r border-white/10 text-gray-400 sm:text-sm">
                                        <i class="fa-brands fa-x-twitter"></i>
                                    </span>
                                    <input
                                        id="social_twitter"
                                        v-model="form.social_twitter"
                                        type="url"
                                        class="flex-1 block w-full min-w-0 bg-transparent text-white border-0 focus:ring-purple-500 transition-colors sm:text-sm"
                                        placeholder="https://twitter.com/..."
                                    />
                                </div>
                            </div>

                            <div>
                                <label for="social_instagram" class="block text-sm font-medium text-gray-300">Instagram URL</label>
                                <div class="mt-2 flex rounded-xl shadow-inner bg-gray-900/50 border border-white/10 overflow-hidden">
                                    <span class="inline-flex items-center px-3 border-r border-white/10 text-gray-400 sm:text-sm">
                                        <i class="fa-brands fa-instagram"></i>
                                    </span>
                                    <input
                                        id="social_instagram"
                                        v-model="form.social_instagram"
                                        type="url"
                                        class="flex-1 block w-full min-w-0 bg-transparent text-white border-0 focus:ring-purple-500 transition-colors sm:text-sm"
                                        placeholder="https://instagram.com/..."
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center gap-4 bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-500 hover:to-cyan-500 rounded-xl font-bold text-white shadow-[0_0_15px_rgba(168,85,247,0.4)] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save All Settings</span>
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
                                Settings saved successfully.
                            </p>
                        </transition>
                    </div>

                </form>
            </div>
        </div>
    </CyberAdminLayout>
</template>
