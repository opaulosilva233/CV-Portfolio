<script setup>
import CyberLayout from '@/Layouts/CyberLayout.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};

// --- 3D Tilt Logic (Specific to Card) ---
const cardRef = ref(null);
const cardTransform = ref('');

const handleCardMouseMove = (e) => {
    if (!cardRef.value) return;

    const { left, top, width, height } = cardRef.value.getBoundingClientRect();
    const centerX = left + width / 2;
    const centerY = top + height / 2;
    
    // Reduced multiplier for subtle movement
    const rotateX = ((e.clientY - centerY) / (height / 2)) * -4;
    const rotateY = ((e.clientX - centerX) / (width / 2)) * 4;

    cardTransform.value = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.01, 1.01, 1.01)`;
}

const handleCardMouseLeave = () => {
    cardTransform.value = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
}
</script>

<template>
    <CyberLayout>
        <Head title="Forgot Password" />

        <div class="min-h-screen grid items-center justify-center p-4" @mousemove="handleCardMouseMove" @mouseleave="handleCardMouseLeave">
            <!-- 3D Glassmorphism Card -->
            <div 
                ref="cardRef"
                class="relative w-full max-w-md transform-gpu transition-transform duration-100 ease-out"
                :style="{ transform: cardTransform }"
            >
                <!-- Border Gradient / Neon Glow -->
                <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 via-purple-600 to-pink-600 rounded-[2rem] opacity-75 blur-sm animate-tilt"></div>
                
                <div class="relative p-8 bg-white/70 dark:bg-black/60 backdrop-blur-2xl border border-white/40 dark:border-white/10 rounded-[1.8rem] shadow-2xl dark:shadow-cyan-900/40 h-full">
                
                    <div class="flex flex-col items-center mb-6 relative">
                        <Link href="/" class="transform hover:scale-110 transition-transform duration-300 relative group">
                            <div class="absolute -inset-4 bg-cyan-500/30 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <ApplicationLogo class="relative h-16 w-16 fill-current text-gray-800 dark:text-white drop-shadow-md" />
                        </Link>
                        
                        <!-- Glitch Text Effect -->
                        <h2 class="mt-4 text-3xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-gray-900 via-cyan-800 to-gray-900 dark:from-white dark:via-cyan-200 dark:to-white text-center glitch-wrapper relative" :data-text="__('Password Reset')">
                            {{ __('Password Reset') }}
                        </h2>
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center font-bold tracking-widest uppercase mt-2 opacity-80">
                            {{ __('Identity Verification') }}
                        </p>
                    </div>

                    <div class="mb-6 text-sm text-gray-600 dark:text-gray-300 text-center leading-relaxed">
                        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                    </div>

                    <div v-if="status" class="mb-6 text-sm font-medium text-green-600 dark:text-green-400 text-center p-3 bg-green-100 dark:bg-green-900/30 rounded-lg backdrop-blur-md border border-green-500/20">
                        {{ status }}
                    </div>
                    
                    <div v-if="form.errors.email" class="mb-6 text-sm font-medium text-pink-600 dark:text-pink-400 text-center p-3 bg-pink-100 dark:bg-pink-900/30 rounded-lg backdrop-blur-md border border-pink-500/20">
                        {{ form.errors.email }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Email -->
                        <div class="group relative">
                            <label for="email" class="block text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 mb-1 ml-1 tracking-[0.2em]">{{ __('Email Address') }}</label>
                            <div class="relative">
                                 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-cyan-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                 </div>
                                <input
                                    id="email"
                                    type="email"
                                    class="block w-full pl-10 pr-4 py-3 bg-gray-50/50 dark:bg-black/50 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white rounded-xl focus:border-cyan-500 focus:ring-cyan-500 transition-all outline-none backdrop-blur-sm"
                                    :class="{'border-pink-500/50 focus:border-pink-500 focus:ring-pink-500': form.errors.email}"
                                    v-model="form.email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="name@example.com"
                                />
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            class="w-full relative overflow-hidden group py-4 px-6 bg-gradient-to-r from-gray-900 to-gray-800 dark:from-white dark:to-gray-200 text-white dark:text-black font-black uppercase tracking-[0.2em] rounded-xl shadow-lg hover:shadow-cyan-500/50 dark:hover:shadow-cyan-500/50 transform transition-all hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed border-2 border-transparent hover:border-cyan-500 dark:hover:border-cyan-500"
                            :class="{ 'opacity-75': form.processing }"
                            :disabled="form.processing"
                        >
                            <span class="relative z-10 flex items-center justify-center gap-2 group-hover:text-white dark:group-hover:text-white transition-colors duration-300">
                                {{ __('Send Reset Link') }}
                            </span>
                            <!-- Stronger Animated Gradient on Hover -->
                            <div class="absolute inset-0 h-full w-full bg-gradient-to-r from-cyan-600 via-blue-600 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </button>
                        
                        <div class="text-center mt-4">
                            <Link
                                :href="route('login')"
                                class="text-[10px] text-cyan-600 dark:text-cyan-400 hover:text-purple-500 dark:hover:text-purple-400 font-black uppercase tracking-[0.2em] transition-colors hover:underline"
                            >
                                {{ __('Back to Login') }}
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </CyberLayout>
</template>

<style scoped>
/* Tilt Glow Animation */
@keyframes tilt {
    0%, 50%, 100% {
        transform: rotate(0deg);
    }
    25% {
        transform: rotate(0.5deg);
    }
    75% {
        transform: rotate(-0.5deg);
    }
}
.animate-tilt {
    animation: tilt 10s infinite linear;
}

/* Glitch Effect */
.glitch-wrapper {
    position: relative;
}
.glitch-wrapper::before,
.glitch-wrapper::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: transparent;
}
.glitch-wrapper::before {
    left: 2px;
    text-shadow: -1px 0 #00ffff;
    clip-path: inset(20% 0 30% 0);
    animation: glitch-anim-1 2s infinite linear alternate-reverse;
}
.glitch-wrapper::after {
    left: -2px;
    text-shadow: -1px 0 #ff00ff;
    clip-path: inset(50% 0 10% 0);
    animation: glitch-anim-2 2s infinite linear alternate-reverse;
}

@keyframes glitch-anim-1 {
    0% { clip-path: inset(20% 0 80% 0); }
    20% { clip-path: inset(60% 0 10% 0); }
    40% { clip-path: inset(40% 0 50% 0); }
    60% { clip-path: inset(80% 0 5% 0); }
    80% { clip-path: inset(10% 0 70% 0); }
    100% { clip-path: inset(30% 0 20% 0); }
}

@keyframes glitch-anim-2 {
    0% { clip-path: inset(10% 0 60% 0); }
    20% { clip-path: inset(80% 0 5% 0); }
    40% { clip-path: inset(30% 0 20% 0); }
    60% { clip-path: inset(10% 0 80% 0); }
    80% { clip-path: inset(50% 0 30% 0); }
    100% { clip-path: inset(70% 0 10% 0); }
}
</style>
