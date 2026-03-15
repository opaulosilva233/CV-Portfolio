<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');
const isSwitching = ref(false);

const languages = {
    'en': { name: 'English', flag: '🇬🇧' },
    'pt': { name: 'Português', flag: '🇵🇹' },
    'nl': { name: 'Nederlands', flag: '🇳🇱' }
};

const currentLanguage = computed(() => {
    return languages[currentLocale.value] || languages['en'];
});

const switchLanguage = (locale) => {
    if (currentLocale.value === locale) return;
    
    // Show the "Translating System" animation overlay
    isSwitching.value = true;
    
    // Enforce a minimum delay for the cool animation to complete visually
    setTimeout(() => {
        router.post(route('language.switch'), { locale: locale }, {
            preserveScroll: true,
            preserveState: false, // Force reload to get new translations
            onFinish: () => {
                 // Close the overlay after Inertia finishes loading the new page
                 // Use a slight extra delay for a smooth fade out
                 setTimeout(() => { isSwitching.value = false; }, 200);
            }
        });
    }, 800); // 800ms minimum artificial delay
};
</script>

<template>
    <div class="relative">
        <Dropdown align="right" width="48" contentClasses="py-1 bg-transparent shadow-none border-none">
            <template #trigger>
                <button class="flex items-center gap-2 px-3 py-1.5 rounded-full hover:bg-purple-500/10 dark:hover:bg-white/10 transition-colors text-sm font-bold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 group">
                    <span class="text-lg leading-none group-hover:scale-110 transition-transform">{{ currentLanguage.flag }}</span>
                    <span class="hidden sm:inline-block">{{ currentLanguage.name }}</span>
                    <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </template>

            <template #content>
                <div class="bg-gray-900/90 border border-white/10 rounded-xl overflow-hidden shadow-[0_0_15px_rgba(0,0,0,0.5)] backdrop-blur-xl w-48 mt-1">
                    <button 
                        v-for="(lang, code) in languages" 
                        :key="code"
                        @click="switchLanguage(code)"
                        class="w-full text-left flex items-center gap-3 px-4 py-3 text-sm transition-colors"
                        :class="currentLocale === code ? 'bg-purple-500/20 text-purple-300' : 'text-gray-200 hover:bg-white/10'"
                    >
                        <span class="text-xl leading-none">{{ lang.flag }}</span>
                        <span>{{ lang.name }}</span>
                    </button>
                </div>
            </template>
        </Dropdown>
        
        <!-- Full Page Transition Overlay -->
        <Teleport to="body">
            <Transition name="fade-overlay">
                <div v-if="isSwitching" class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-xl flex flex-col items-center justify-center">
                    <!-- Tech/Cyber Grid Overlay behind text -->
                    <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_60%_at_50%_50%,#000_70%,transparent_100%)] pointer-events-none"></div>
                    
                    <div class="relative z-10 flex flex-col items-center">
                         <!-- Cyber Spinner -->
                         <div class="relative w-24 h-24 mb-6">
                            <div class="absolute inset-0 border-t-4 border-purple-500 rounded-full animate-spin shadow-[0_0_15px_rgba(168,85,247,0.5)]"></div>
                            <div class="absolute inset-2 border-r-4 border-cyan-500 rounded-full animate-spin-slow shadow-[0_0_15px_rgba(6,182,212,0.5)]"></div>
                            <div class="absolute inset-4 border-b-4 border-pink-500 rounded-full animate-spin-reverse shadow-[0_0_15px_rgba(236,72,153,0.5)]"></div>
                         </div>
                         
                         <!-- Text -->
                         <h2 class="text-2xl font-black tracking-[0.2em] uppercase bg-clip-text text-transparent bg-gradient-to-r from-purple-400 via-pink-400 to-cyan-400 animate-pulse">
                             Translating System
                         </h2>
                         <p class="text-gray-400 font-mono text-sm mt-2 opacity-80">Loading language module...</p>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.fade-overlay-enter-active,
.fade-overlay-leave-active {
  transition: opacity 0.4s ease;
}

.fade-overlay-enter-from,
.fade-overlay-leave-to {
  opacity: 0;
}

.animate-spin-slow {
    animation: spin 3s linear infinite;
}

.animate-spin-reverse {
    animation: spin 2s linear infinite reverse;
}
</style>
