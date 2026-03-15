<script setup>
import { onMounted, ref, onUnmounted } from 'vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
// --- Advanced Interactive Logic (Shared) ---
const spotlightStyle = ref({ opacity: 0, left: '0px', top: '0px' });
const containerRef = ref(null);
const bgParallax = ref({ x: 0, y: 0 });

const handleMouseMove = (e) => {
    // 1. Spotlight Effect
    spotlightStyle.value = {
        opacity: 0.8,
        left: `${e.clientX}px`,
        top: `${e.clientY}px`,
    };
}

const handleMouseLeave = () => {
    // Reset Spotlight
    spotlightStyle.value.opacity = 0;
}

const updateParallax = (e) => {
    // Reduced parallax movement for global layout
    const x = (window.innerWidth - e.pageX * 2) / 150;
    const y = (window.innerHeight - e.pageY * 2) / 150;
    bgParallax.value = { x, y };
}

onMounted(() => {
    window.addEventListener('mousemove', updateParallax);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', updateParallax);
});
</script>

<template>
    <div 
        ref="containerRef"
        class="min-h-screen relative overflow-hidden bg-gray-100 dark:bg-[#030303] transition-colors duration-500 font-sans text-gray-900 dark:text-gray-100"
        @mousemove="handleMouseMove"
        @mouseleave="handleMouseLeave"
    >
        
        <!-- Interactive Spotlight (Follows Mouse) -->
        <div 
            class="pointer-events-none fixed z-30 w-[600px] h-[600px] rounded-full bg-radial-gradient from-purple-500/20 to-transparent blur-[100px] transition-opacity duration-300"
            :style="spotlightStyle"
            style="transform: translate(-50%, -50%);"
        ></div>

        <!-- Animated Background Blobs with Parallax -->
        <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none transition-transform duration-200 ease-out" 
             :style="{ transform: `translate(${bgParallax.x}px, ${bgParallax.y}px)` }">
             <!-- Purple Blob -->
             <div class="absolute top-[-20%] left-[-10%] w-[50rem] h-[50rem] bg-purple-500/40 rounded-full mix-blend-screen filter blur-[100px] opacity-40 animate-blob dark:bg-purple-600/20"></div>
             <!-- Blue Blob -->
             <div class="absolute top-[-10%] right-[-10%] w-[50rem] h-[50rem] bg-cyan-500/40 rounded-full mix-blend-screen filter blur-[100px] opacity-40 animate-blob animation-delay-2000 dark:bg-cyan-600/20"></div>
             <!-- Pink Blob -->
             <div class="absolute bottom-[-20%] left-[20%] w-[50rem] h-[50rem] bg-pink-500/40 rounded-full mix-blend-screen filter blur-[100px] opacity-40 animate-blob animation-delay-4000 dark:bg-pink-600/20"></div>
             
             <!-- Cyber Grid Overlay -->
             <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-30 brightness-100 contrast-150"></div>
             
             <!-- Tech/Grid Lines -->
             <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_60%_at_50%_50%,#000_70%,transparent_100%)]"></div>
        </div>


        <!-- Main Content Area -->
        <div class="relative z-40 min-h-screen">
            <slot />
        </div>
        
    </div>
</template>

<style scoped>
/* Blob Animation */
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 10s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}

/* Radial Gradient for Spotlight */
.bg-radial-gradient {
    background-image: radial-gradient(circle at center, var(--tw-gradient-from) 0%, var(--tw-gradient-to) 70%);
}
</style>
