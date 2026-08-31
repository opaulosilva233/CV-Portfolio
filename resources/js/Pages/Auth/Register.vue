<script setup>
import CyberLayout from '@/Layouts/CyberLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <CyberLayout>
        <Head :title="__('Register')" />

        <div class="min-h-screen grid items-center justify-center relative overflow-hidden">
             <!-- 3D Glassmorphism Card Wrapper (Simplified Tilt for Register) -->
             <div class="relative z-40 w-[90vw] max-w-md">
                 <!-- Border Gradient / Neon Glow -->
                <div class="absolute -inset-0.5 bg-gradient-to-r from-pink-600 via-purple-600 to-cyan-600 rounded-[2rem] opacity-75 blur-sm animate-tilt"></div>
                
                <div class="relative p-8 bg-white/70 dark:bg-black/60 backdrop-blur-2xl border border-white/40 dark:border-white/10 rounded-[1.8rem] shadow-2xl dark:shadow-purple-900/40">
                    
                    <div class="text-center mb-8">
                         <h2 class="text-3xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-gray-900 via-purple-800 to-gray-900 dark:from-white dark:via-purple-200 dark:to-white text-center">
                            {{ __('NEW USER') }}
                        </h2>
                         <p class="text-sm text-gray-500 dark:text-gray-400 text-center font-bold tracking-widest uppercase mt-2 opacity-80">
                            {{ __('Initialize Protocol') }}
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Name -->
                        <div class="group">
                            <InputLabel for="name" :value="__('Name')" class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 mb-1 ml-1 tracking-[0.2em]" />
                            <TextInput
                                id="name"
                                type="text"
                                class="block w-full px-4 py-3 bg-gray-50/50 dark:bg-black/50 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white rounded-xl focus:border-purple-500 focus:ring-purple-500 transition-all outline-none backdrop-blur-sm"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Email -->
                        <div class="group">
                            <InputLabel for="email" :value="__('Email Address')" class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 mb-1 ml-1 tracking-[0.2em]" />
                            <TextInput
                                id="email"
                                type="email"
                                class="block w-full px-4 py-3 bg-gray-50/50 dark:bg-black/50 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white rounded-xl focus:border-purple-500 focus:ring-purple-500 transition-all outline-none backdrop-blur-sm"
                                v-model="form.email"
                                required
                                autocomplete="username"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div class="group">
                             <InputLabel for="password" :value="__('Password')" class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 mb-1 ml-1 tracking-[0.2em]" />
                            <TextInput
                                id="password"
                                type="password"
                                class="block w-full px-4 py-3 bg-gray-50/50 dark:bg-black/50 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white rounded-xl focus:border-purple-500 focus:ring-purple-500 transition-all outline-none backdrop-blur-sm"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="group">
                             <InputLabel for="password_confirmation" :value="__('Confirm Password')" class="text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 mb-1 ml-1 tracking-[0.2em]" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                class="block w-full px-4 py-3 bg-gray-50/50 dark:bg-black/50 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white rounded-xl focus:border-purple-500 focus:ring-purple-500 transition-all outline-none backdrop-blur-sm"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <Link
                                :href="route('login')"
                                class="text-sm text-gray-500 dark:text-gray-400 hover:text-purple-600 dark:hover:text-cyan-400 font-bold transition-colors uppercase tracking-wider"
                            >
                                {{ __('Already registered?') }}
                            </Link>

                            <button
                                class="relative overflow-hidden group py-3 px-6 bg-gradient-to-r from-gray-900 to-gray-800 dark:from-white dark:to-gray-200 text-white dark:text-black font-black uppercase tracking-[0.1em] rounded-xl shadow-lg hover:shadow-cyan-500/50 dark:hover:shadow-purple-500/50 transform transition-all hover:scale-[1.02] disabled:opacity-50"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                <span class="relative z-10 flex items-center justify-center gap-2 group-hover:text-white dark:group-hover:text-white transition-colors duration-300">
                                    {{ __('Register') }}
                                </span>
                                <div class="absolute inset-0 h-full w-full bg-gradient-to-r from-purple-600 via-pink-600 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </button>
                        </div>
                    </form>
                </div>
             </div>
        </div>
    </CyberLayout>
</template>

<style scoped>
.animate-tilt {
    animation: tilt 10s infinite linear;
}
@keyframes tilt {
    0%, 50%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(0.5deg); }
    75% { transform: rotate(-0.5deg); }
}
</style>
