<script setup>
import { onMounted, ref, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

// --- Advanced Interactive Logic (Shared from CyberLayout) ---
const spotlightStyle = ref({ opacity: 0, left: '0px', top: '0px' });
const containerRef = ref(null);
const bgParallax = ref({ x: 0, y: 0 });
const isSidebarOpen = ref(true);

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

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

// Menu Items
const menuItems = [
    { name: 'Dashboard', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Experiences', route: 'admin.experiences.index', icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { name: 'Education', route: 'admin.education.index', icon: 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z' },
    { name: 'Skills', route: 'admin.skills.index', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
    { name: 'Projects', route: 'admin.projects.index', icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' },
    { name: 'Sections', route: 'admin.sections.index', icon: 'M4 6h16M4 12h16m-7 6h7' },
    { name: 'Settings', route: 'admin.settings.edit', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
];
</script>

<template>
    <div 
        ref="containerRef"
        class="min-h-screen relative overflow-hidden bg-gray-100 dark:bg-[#030303] transition-colors duration-500 font-sans text-gray-900 dark:text-gray-100 flex"
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

        <!-- Sidebar (Glassmorphism) -->
        <aside 
            :class="[
                'fixed inset-y-0 left-0 z-50 transition-all duration-300 ease-in-out border-r border-white/10 bg-white/5 backdrop-blur-xl',
                isSidebarOpen ? 'w-64' : 'w-20'
            ]"
        >
            <div class="flex h-16 items-center justify-between px-4 border-b border-white/10 bg-white/5">
                <Link :href="route('dashboard')" class="flex items-center gap-3 overflow-hidden justify-center w-full">
                    <ApplicationLogo class="h-10 w-auto flex-shrink-0 scale-110 transform" />
                    <span v-if="isSidebarOpen" class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 whitespace-nowrap">
                        CTRL PANEL
                    </span>
                </Link>
            </div>

            <nav class="p-4 space-y-2 mt-4">
                <Link 
                    v-for="item in menuItems" 
                    :key="item.name"
                    v-bind:href="route(item.route)"
                    :class="[
                        'flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group relative',
                        route().current(item.route + '*') 
                            ? 'bg-gradient-to-r from-purple-600/40 to-cyan-600/40 border border-white/10 text-white shadow-[0_0_15px_rgba(168,85,247,0.2)]' 
                            : 'text-gray-400 hover:text-white hover:bg-white/10 border border-transparent'
                    ]"
                >
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                    </svg>
                    <span v-if="isSidebarOpen" class="font-medium whitespace-nowrap">{{ __(item.name) }}</span>

                    <!-- Tooltip when closed -->
                    <div v-if="!isSidebarOpen" class="absolute left-full ml-4 px-2 py-1 bg-gray-900 border border-gray-700 text-white text-sm rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">
                        {{ __(item.name) }}
                    </div>
                </Link>
            </nav>
        </aside>

        <!-- Main Content Area Wrapper -->
        <div 
            class="flex-1 flex flex-col min-h-screen transition-all duration-300 relative z-40"
            :class="isSidebarOpen ? 'ml-64' : 'ml-20'"
        >
            <!-- Top Navbar (Glassmorphism) -->
            <header class="h-16 border-b border-white/10 bg-white/5 backdrop-blur-xl sticky top-0 z-50 flex items-center justify-between px-6">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <button @click="toggleSidebar" class="text-gray-400 hover:text-white transition-colors flex-shrink-0" :class="{ 'sm:hidden': isSidebarOpen }">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <!-- Page Heading slot (optional) -->
                    <div class="flex-1 min-w-0" v-if="$slots.header">
                        <slot name="header" />
                    </div>
                </div>

                <div class="flex items-center gap-4 flex-shrink-0 ml-4">
                    <LanguageSwitcher />
                    <ThemeToggle variant="default" />
                    
                    <!-- User Dropdown -->
                    <div class="relative">
                        <Dropdown align="right" width="48" contentClasses="py-1 bg-transparent shadow-none border-none">
                            <template #trigger>
                                <button class="flex items-center gap-2 px-3 py-2 border border-white/10 rounded-xl bg-white/5 hover:bg-white/10 transition-colors text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-900">
                                    <span>{{ $page.props.auth.user.name }}</span>
                                    <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="bg-gray-900/90 border border-white/10 rounded-xl overflow-hidden shadow-[0_0_15px_rgba(0,0,0,0.5)] backdrop-blur-xl w-48 mt-1">
                                    <DropdownLink :href="route('logout')" method="post" as="button" class="hover:bg-red-500/20 text-red-400 hover:text-red-300 w-full text-left block px-4 py-2 text-sm transition-colors">
                                        {{ __('Log Out') }}
                                    </DropdownLink>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Page Content Slot -->
            <main class="flex-1 p-6 z-40 relative">
                <slot />
            </main>
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
