<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const __ = (key) => page.props.language?.[key] || key;

const props = defineProps({
    email: String,
    phone: String,
    address: String,
});

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const isSubmitting = ref(false);
const showSuccess = ref(false);

const submitForm = () => {
    isSubmitting.value = true;
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess.value = true;
            form.reset();
            setTimeout(() => {
                showSuccess.value = false;
            }, 5000);
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <section id="contact" class="py-24 px-4 relative overflow-hidden group">
        <!-- Background Elements -->
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-purple-500/5 to-transparent pointer-events-none"></div>
        <div class="absolute -right-64 top-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-cyan-500/10 blur-[120px] rounded-full pointer-events-none group-hover:bg-cyan-500/20 transition-all duration-1000"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="flex flex-col lg:flex-row gap-16 items-start">
                <!-- Left Side: Info & Aesthetic -->
                <div class="lg:w-1/2 space-y-12">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-[2px] bg-purple-500"></div>
                            <span class="text-xs font-black tracking-[0.4em] uppercase text-purple-600 dark:text-purple-400">{{ __('Secure Transmission') }}</span>
                        </div>
                        <h2 class="text-5xl lg:text-7xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                            {{ __('Get In') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-cyan-400">{{ __('Touch') }}</span>
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-400 font-medium max-w-md">
                            {{ __('Establish a secure connection for collaborations, inquiries, or just to say hello. System status is currently ONLINE.') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                        <div v-if="email" class="bg-white/40 dark:bg-white/5 backdrop-blur-md p-6 rounded-2xl border border-white/20 dark:border-white/10 hover:border-purple-500/50 transition-all duration-300 group/card">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 block">// EMAIL.ADR</span>
                            <a :href="'mailto:' + email" class="text-lg font-bold text-gray-800 dark:text-gray-200 hover:text-purple-500 transition-colors break-all">{{ email }}</a>
                        </div>
                        <div v-if="phone" class="bg-white/40 dark:bg-white/5 backdrop-blur-md p-6 rounded-2xl border border-white/20 dark:border-white/10 hover:border-cyan-500/50 transition-all duration-300 group/card">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 block">// VOICE.COM</span>
                            <span class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ phone }}</span>
                        </div>
                    </div>

                    <!-- Tech Decor -->
                    <div class="hidden lg:block pt-8">
                        <div class="flex items-center gap-4 text-[10px] font-mono text-gray-400 dark:text-gray-500 opacity-60">
                            <span>ENCRYPTION: AES-256</span>
                            <span class="w-1 h-1 rounded-full bg-current"></span>
                            <span>PROTOCOL: HTTPS/WSS</span>
                            <span class="w-1 h-1 rounded-full bg-current"></span>
                            <span>STATUS: READY</span>
                        </div>
                        <div class="mt-4 flex gap-1">
                            <div v-for="i in 20" :key="i" class="w-2 h-1 bg-gray-200 dark:bg-white/5" :class="{'bg-purple-500/40': i < 8}"></div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Form -->
                <div class="lg:w-1/2 w-full">
                    <div class="relative group/form">
                        <!-- Success Overlay -->
                        <Transition name="fade">
                            <div v-if="showSuccess" class="absolute inset-0 z-20 bg-white/90 dark:bg-[#030712]/90 backdrop-blur-xl flex flex-col items-center justify-center rounded-[2.5rem] border-2 border-cyan-500 shadow-[0_0_30px_rgba(6,182,212,0.2)]">
                                <div class="w-20 h-20 mb-6 relative">
                                    <div class="absolute inset-0 border-4 border-cyan-500 rounded-full animate-ping opacity-20"></div>
                                    <div class="absolute inset-0 flex items-center justify-center text-cyan-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-2xl font-black uppercase tracking-widest text-gray-900 dark:text-white">{{ __('Transmission Complete') }}</h3>
                                <p class="text-gray-500 dark:text-gray-400 mt-2 font-mono text-sm">{{ __('Target has been reached successfully.') }}</p>
                            </div>
                        </Transition>

                        <form @submit.prevent="submitForm" class="bg-white/60 dark:bg-white/5 backdrop-blur-2xl p-8 lg:p-12 rounded-[2.5rem] border border-white/20 dark:border-white/10 shadow-2xl relative overflow-hidden">
                            <!-- Scanning Line Effect when submitting -->
                            <div v-if="isSubmitting" class="absolute left-0 w-full h-1 bg-cyan-500 shadow-[0_0_15px_rgba(6,182,212,0.8)] z-10 animate-scan"></div>

                            <div class="space-y-6">
                                <div class="relative group/input">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 block group-focus-within/input:text-purple-500 transition-colors">{{ __('01 / Full Name') }}</label>
                                    <input 
                                        v-model="form.name"
                                        type="text" 
                                        required
                                        :placeholder="__('IDENTIFY YOURSELF')"
                                        class="w-full bg-gray-100/50 dark:bg-white/5 border-none rounded-xl px-4 py-4 text-gray-900 dark:text-white font-bold placeholder:text-gray-400 dark:placeholder:text-white/10 focus:ring-2 focus:ring-purple-500 transition-all outline-none"
                                    />
                                    <div v-if="form.errors.name" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.name }}</div>
                                </div>

                                <div class="relative group/input">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 block group-focus-within/input:text-purple-500 transition-colors">{{ __('02 / Communication Port (Email)') }}</label>
                                    <input 
                                        v-model="form.email"
                                        type="email" 
                                        required
                                        :placeholder="__('USER@DOMAIN.TLD')"
                                        class="w-full bg-gray-100/50 dark:bg-white/5 border-none rounded-xl px-4 py-4 text-gray-900 dark:text-white font-bold placeholder:text-gray-400 dark:placeholder:text-white/10 focus:ring-2 focus:ring-purple-500 transition-all outline-none"
                                    />
                                    <div v-if="form.errors.email" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.email }}</div>
                                </div>

                                <div class="relative group/input">
                                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-2 block group-focus-within/input:text-purple-500 transition-colors">{{ __('03 / Message Payload') }}</label>
                                    <textarea 
                                        v-model="form.message"
                                        required
                                        rows="4"
                                        :placeholder="__('ENTER DATA PACKET...')"
                                        class="w-full bg-gray-100/50 dark:bg-white/5 border-none rounded-xl px-4 py-4 text-gray-900 dark:text-white font-bold placeholder:text-gray-400 dark:placeholder:text-white/10 focus:ring-2 focus:ring-purple-500 transition-all outline-none resize-none"
                                    ></textarea>
                                    <div v-if="form.errors.message" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors.message }}</div>
                                </div>

                                <button 
                                    type="submit" 
                                    :disabled="isSubmitting"
                                    class="w-full relative group/btn overflow-hidden rounded-xl py-5 transition-all duration-500"
                                >
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-indigo-600 transition-transform duration-500 group-hover/btn:scale-110"></div>
                                    <div class="relative flex items-center justify-center gap-3 text-white font-black uppercase tracking-[0.3em] text-xs">
                                        <span v-if="!isSubmitting">{{ __('Initiate Transmission') }}</span>
                                        <span v-else class="flex items-center gap-2">
                                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            {{ __('Encrypting...') }}
                                        </span>
                                    </div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes scan {
    0% { top: 0; opacity: 0; }
    50% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}
.animate-scan {
    animation: scan 2s linear infinite;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
