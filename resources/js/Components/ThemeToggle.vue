<script setup>
import { ref } from 'vue';
import { useDarkTheme } from '@/Composables/useDarkTheme';

const { isDark, toggleTheme } = useDarkTheme();
const isAnimating = ref(false);

const handleClick = (event) => {
    isAnimating.value = true;
    toggleTheme(event);
    setTimeout(() => {
        isAnimating.value = false;
    }, 600);
};
</script>

<template>
    <button
        @click="handleClick"
        class="toggle"
        :class="{ 
            'toggle--dark': isDark,
            'toggle--animating': isAnimating 
        }"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    >
        <div class="toggle__container">
            <!-- Stars (only visible in dark mode) -->
            <div class="toggle__stars">
                <span class="toggle__star" style="--delay: 0s; --x: 20%; --y: 25%;"></span>
                <span class="toggle__star" style="--delay: 0.3s; --x: 75%; --y: 20%;"></span>
                <span class="toggle__star" style="--delay: 0.6s; --x: 60%; --y: 70%;"></span>
                <span class="toggle__star" style="--delay: 0.15s; --x: 35%; --y: 65%;"></span>
            </div>
            
            <!-- Sun/Moon morph -->
            <div class="toggle__celestial">
                <div class="toggle__sun-moon">
                    <!-- Crater overlays for moon effect -->
                    <div class="toggle__crater toggle__crater--1"></div>
                    <div class="toggle__crater toggle__crater--2"></div>
                    <div class="toggle__crater toggle__crater--3"></div>
                </div>
                
                <!-- Sun rays -->
                <div class="toggle__rays">
                    <span class="toggle__ray"></span>
                    <span class="toggle__ray"></span>
                    <span class="toggle__ray"></span>
                    <span class="toggle__ray"></span>
                    <span class="toggle__ray"></span>
                    <span class="toggle__ray"></span>
                    <span class="toggle__ray"></span>
                    <span class="toggle__ray"></span>
                </div>
            </div>
        </div>
    </button>
</template>

<style scoped>
.toggle {
    --bg-light: linear-gradient(135deg, #74b9ff 0%, #a29bfe 100%);
    --bg-dark: linear-gradient(135deg, #0c1445 0%, #1a1a3e 100%);
    --sun-color: #ffeaa7;
    --sun-glow: #fdcb6e;
    --moon-color: #dfe6e9;
    --moon-shadow: #b2bec3;
    
    position: relative;
    width: 72px;
    height: 36px;
    border-radius: 50px;
    border: none;
    cursor: pointer;
    background: var(--bg-light);
    box-shadow: 
        inset 0 -2px 4px rgba(0, 0, 0, 0.1),
        0 4px 12px rgba(116, 185, 255, 0.3);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.toggle--dark {
    background: var(--bg-dark);
    box-shadow: 
        inset 0 -2px 4px rgba(0, 0, 0, 0.3),
        0 4px 16px rgba(99, 110, 255, 0.2),
        0 0 30px rgba(99, 110, 255, 0.1);
}

.toggle__container {
    width: 100%;
    height: 100%;
    position: relative;
}


/* Stars */
.toggle__stars {
    position: absolute;
    inset: 0;
    transition: opacity 0.5s ease;
    opacity: 0;
}

.toggle--dark .toggle__stars {
    opacity: 1;
    transition-delay: 0.2s;
}

.toggle__star {
    position: absolute;
    width: 2px;
    height: 2px;
    background: white;
    border-radius: 50%;
    left: var(--x);
    top: var(--y);
    /* Stars always animate, opacity controls visibility */
    animation: twinkle 2s ease-in-out infinite; 
    animation-delay: var(--delay);
}

@keyframes twinkle {
    0%, 100% { opacity: 0.4; transform: scale(0.8); }
    50% { opacity: 1; transform: scale(1.2); }
}

/* Celestial body container */
.toggle__celestial {
    position: absolute;
    top: 50%;
    left: 4px; /* Starting position (Light Mode) */
    transform: translateY(-50%);
    /* Sync with global wipe animation (1.5s ease-in-out) */
    transition: left 1.5s ease-in-out;
    z-index: 10;
}

.toggle--dark .toggle__celestial {
    left: calc(100% - 28px); /* End position (Dark Mode) */
}

/* Sun/Moon circle */
.toggle__sun-moon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--sun-color);
    box-shadow: 
        0 0 15px rgba(253, 203, 110, 0.8),
        inset -2px -2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.5s ease;
    position: relative;
    overflow: hidden;
}

.toggle--dark .toggle__sun-moon {
    background: var(--moon-color);
    box-shadow: 
        0 0 10px rgba(255, 255, 255, 0.4),
        inset -3px -2px 6px var(--moon-shadow);
    transform: rotate(360deg); /* Rotate effect on toggle */
}

/* Moon craters */
.toggle__crater {
    position: absolute;
    background: var(--moon-shadow);
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.toggle--dark .toggle__crater {
    opacity: 0.6;
}

.toggle__crater--1 { width: 5px; height: 5px; top: 6px; right: 6px; }
.toggle__crater--2 { width: 3px; height: 3px; bottom: 6px; left: 6px; }
.toggle__crater--3 { width: 4px; height: 4px; top: 12px; left: 8px; }

/* Sun rays */
.toggle__rays {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 36px;
    height: 36px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.toggle--dark .toggle__rays {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.5) rotate(90deg);
}

.toggle__ray {
    position: absolute;
    width: 2px;
    height: 6px;
    background: var(--sun-glow);
    border-radius: 2px;
    top: 0;
    left: 50%;
    transform-origin: center 18px;
    opacity: 0.9;
}

/* Rays positioning ... same as before but ensured */
.toggle__ray:nth-child(1) { transform: translateX(-50%) rotate(0deg); }
.toggle__ray:nth-child(2) { transform: translateX(-50%) rotate(45deg); }
.toggle__ray:nth-child(3) { transform: translateX(-50%) rotate(90deg); }
.toggle__ray:nth-child(4) { transform: translateX(-50%) rotate(135deg); }
.toggle__ray:nth-child(5) { transform: translateX(-50%) rotate(180deg); }
.toggle__ray:nth-child(6) { transform: translateX(-50%) rotate(225deg); }
.toggle__ray:nth-child(7) { transform: translateX(-50%) rotate(270deg); }
.toggle__ray:nth-child(8) { transform: translateX(-50%) rotate(315deg); }

/* Hover */
.toggle:hover .toggle__sun-moon {
    transform: scale(1.1);
}
.toggle:hover .toggle__rays {
    animation: rays-spin 3s linear infinite;
}

@keyframes rays-spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Focus */
.toggle:focus-visible {
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.5);
}

/* View Transition Escape Hatch */
.toggle {
    view-transition-name: theme-toggle-btn;
}

::view-transition-group(theme-toggle-btn) {
    animation-duration: 1.5s; /* Sync with global wipe */
    z-index: 10000; /* Ensure it stays above the wipe */
}
</style>
