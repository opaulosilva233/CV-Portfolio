<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    name: props.settings.name?.value || '',
    job_title: props.settings.job_title?.value || '',
    bio: props.settings.bio?.value || '',
    hero_image: props.settings.hero_image?.value || '',
    contact_email: props.settings.contact_email?.value || '',
    // Add other fields as needed
});

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
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
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-6 max-w-3xl">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300">Name</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                            />
                        </div>

                         <div>
                            <label for="job_title" class="block text-sm font-medium text-gray-300">Job Title</label>
                            <input
                                id="job_title"
                                v-model="form.job_title"
                                type="text"
                                class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                            />
                        </div>

                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-300">Bio</label>
                            <textarea
                                id="bio"
                                v-model="form.bio"
                                rows="4"
                                class="mt-2 block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-purple-500 focus:ring-purple-500 transition-colors"
                            ></textarea>
                        </div>
                        
                        <div>
                            <label for="hero_image" class="block text-sm font-medium text-gray-300">Hero Image URL</label>
                            <input
                                id="hero_image"
                                v-model="form.hero_image"
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

                        <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-500 hover:to-cyan-500 rounded-xl font-bold text-white shadow-[0_0_15px_rgba(168,85,247,0.4)] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>Save Settings</span>
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
        </div>
    </CyberAdminLayout>
</template>
