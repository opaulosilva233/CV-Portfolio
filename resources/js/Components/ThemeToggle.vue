<script setup>
import { useDarkTheme } from '@/Composables/useDarkTheme';

const { isDark, toggleTheme } = useDarkTheme();
</script>

<template>
    <button
        @click="toggleTheme"
        class="p-2 rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        :class="[
            isDark ? 'bg-gray-800 text-yellow-400 hover:bg-gray-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
        ]"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        title="Toggle Theme"
    >
        <!-- Sun Icon (Show in Dark Mode) -->
        <svg
            v-if="isDark"
            xmlns="http://www.w3.org/2000/svg"
            class="w-6 h-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
            />
        </svg>
        
        <!-- Moon Icon (Show in Light Mode) -->
        <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            class="w-6 h-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
            />
        </svg>
    </button>
</template>
