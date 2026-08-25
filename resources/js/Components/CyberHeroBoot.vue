<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';

const props = defineProps({
    hero: {
        type: Object,
        required: true,
    },
    socials: {
        type: [Object, Array],
        default: () => ({}),
    },
    resumeUrl: {
        type: String,
        default: '',
    },
    enabled: {
        type: Boolean,
        default: true,
    },
});

// Translation helper fallback
const __ = (key) => {
    if (typeof window !== 'undefined' && typeof window.__ === 'function') {
        return window.__(key);
    }
    return key;
};

// --- Boot Sequence States ---
const isBooting = ref(props.enabled);
const bootPhase = ref(props.enabled ? 0 : 3); // 0: Init, 1: Scan & Reticle, 2: Decrypting, 3: Complete / Idle
const telemetryLog = ref('SYS.INIT // CORE_LOAD');
const scanActive = ref(props.enabled);

// --- Decrypted Text States ---
const targetTitle = computed(() => props.hero?.title || 'Full Stack Developer');
const targetName = computed(() => props.hero?.name || 'Paulo Silva');

const displayTitle = ref(props.enabled ? '' : targetTitle.value);
const displayName = ref(props.enabled ? '' : targetName.value);
const isHoverGlitchingTitle = ref(false);
const isHoverGlitchingName = ref(false);

const cyberChars = 'ABCDEF0123456789$#@!%&*?/><~[]{}';

// Function to scramble and decrypt text smoothly
const decryptText = (originalText, updateFn, duration = 1200, delay = 0) => {
    if (!props.enabled) {
        updateFn(originalText);
        return Promise.resolve();
    }

    return new Promise((resolve) => {
        setTimeout(() => {
            const length = originalText.length;
            let iteration = 0;
            const totalSteps = 24;
            const stepInterval = duration / totalSteps;

            const interval = setInterval(() => {
                const scrambled = originalText
                    .split('')
                    .map((char, index) => {
                        if (char === ' ') return ' ';
                        if (index < (iteration / totalSteps) * length) {
                            return originalText[index];
                        }
                        return cyberChars[Math.floor(Math.random() * cyberChars.length)];
                    })
                    .join('');

                updateFn(scrambled);
                iteration += 1;

                if (iteration > totalSteps) {
                    clearInterval(interval);
                    updateFn(originalText);
                    resolve();
                }
            }, stepInterval);
        }, delay);
    });
};

// Micro scramble on hover
const triggerMicroGlitch = (type) => {
    if (isBooting.value) return;
    
    if (type === 'name' && !isHoverGlitchingName.value) {
        isHoverGlitchingName.value = true;
        decryptText(targetName.value, (val) => displayName.value = val, 400, 0).then(() => {
            isHoverGlitchingName.value = false;
        });
    } else if (type === 'title' && !isHoverGlitchingTitle.value) {
        isHoverGlitchingTitle.value = true;
        decryptText(targetTitle.value, (val) => displayTitle.value = val, 400, 0).then(() => {
            isHoverGlitchingTitle.value = false;
        });
    }
};

// --- 3D Avatar Tilt Logic ---
const avatarContainer = ref(null);
const tiltX = ref(0);
const tiltY = ref(0);
const glareX = ref(50);
const glareY = ref(50);
const isAvatarHovered = ref(false);

const handleAvatarMouseMove = (e) => {
    if (!avatarContainer.value) return;
    const rect = avatarContainer.value.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    
    // Tilt calculations (-15 to 15 deg)
    tiltX.value = ((y - centerY) / centerY) * -12;
    tiltY.value = ((x - centerX) / centerX) * 12;
    
    glareX.value = (x / rect.width) * 100;
    glareY.value = (y / rect.height) * 100;
};

const handleAvatarMouseEnter = () => {
    isAvatarHovered.value = true;
};

const handleAvatarMouseLeave = () => {
    isAvatarHovered.value = false;
    tiltX.value = 0;
    tiltY.value = 0;
};

// --- Boot Master Controller ---
let bootTimeouts = [];

const clearBootTimers = () => {
    bootTimeouts.forEach(t => clearTimeout(t));
    bootTimeouts = [];
};

const startBootSequence = () => {
    clearBootTimers();
    
    if (!props.enabled) {
        isBooting.value = false;
        bootPhase.value = 3;
        displayTitle.value = targetTitle.value;
        displayName.value = targetName.value;
        telemetryLog.value = 'SYS.STATUS // ALL SYSTEMS ONLINE';
        scanActive.value = false;
        return;
    }

    isBooting.value = true;
    bootPhase.value = 0;
    scanActive.value = true;
    telemetryLog.value = 'INITIALIZING SYS.NEURAL_LINK [PROTOCOL: 0x9F4]...';
    displayTitle.value = '';
    displayName.value = '';

    // Phase 1: Laser Scan & Reticle Lock (300ms)
    bootTimeouts.push(setTimeout(() => {
        bootPhase.value = 1;
        telemetryLog.value = 'CALIBRATING HUD_OPTICS & BIOMETRICS... [TARGET: ACQUIRED]';
    }, 350));

    // Phase 2: Text Decrypt Starts (700ms)
    bootTimeouts.push(setTimeout(() => {
        bootPhase.value = 2;
        telemetryLog.value = 'DECRYPTING IDENTITY PAYLOAD // ACCESS GRANTED';
        
        decryptText(targetTitle.value, (val) => displayTitle.value = val, 700, 0);
        decryptText(targetName.value, (val) => displayName.value = val, 1000, 150);
    }, 700));

    // Phase 3: Finalize & Armed (1800ms)
    bootTimeouts.push(setTimeout(() => {
        bootPhase.value = 3;
        isBooting.value = false;
        scanActive.value = false;
        telemetryLog.value = 'SYS.STATUS: ONLINE // READY FOR INTERACTION';
    }, 1900));
};

// Trigger boot when component mounts or enabled prop changes
onMounted(() => {
    startBootSequence();
});

watch(() => props.enabled, (newVal) => {
    if (newVal) {
        startBootSequence();
    } else {
        clearBootTimers();
        isBooting.value = false;
        bootPhase.value = 3;
        displayTitle.value = targetTitle.value;
        displayName.value = targetName.value;
        scanActive.value = false;
    }
});

watch([targetTitle, targetName], () => {
    if (!isBooting.value) {
        displayTitle.value = targetTitle.value;
        displayName.value = targetName.value;
    }
});

onUnmounted(() => {
    clearBootTimers();
});
</script>

<template>
    <div class="relative w-full max-w-7xl mx-auto">
        <!-- Laser Calibration Bar Sweep (Only during boot) -->
        <transition enter-active-class="transition-opacity duration-300" leave-active-class="transition-opacity duration-500">
            <div 
                v-if="scanActive && enabled" 
                class="pointer-events-none absolute -inset-x-8 -top-12 h-[2px] bg-gradient-to-r from-transparent via-cyan-400 to-transparent shadow-[0_0_15px_#22d3ee] animate-laser-sweep z-50 opacity-80"
            ></div>
        </transition>

        <!-- Top Telemetry Stream Line -->
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4 border-b border-gray-200/40 dark:border-white/10 pb-3 font-mono text-[11px]">
            <div class="flex items-center gap-2 text-cyan-600 dark:text-cyan-400">
                <span class="inline-block w-2 h-2 rounded-full bg-cyan-500 animate-ping"></span>
                <span class="font-bold tracking-widest uppercase">SYS.LINK //</span>
                <span class="text-gray-600 dark:text-gray-400 tracking-wider transition-all duration-300">
                    {{ telemetryLog }}
                </span>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="text-gray-500 dark:text-gray-400 hidden sm:inline-block">
                    FPS: <span class="text-emerald-500 dark:text-emerald-400 font-semibold">60</span> | LAT: <span class="text-purple-600 dark:text-purple-400">38.72° N</span>
                </span>
                <!-- Interactive Re-Boot Button -->
                <button 
                    v-if="enabled"
                    @click="startBootSequence" 
                    title="Re-run boot sequence protocol"
                    class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-200/60 dark:bg-white/5 hover:bg-cyan-500/20 border border-gray-300/60 dark:border-white/10 hover:border-cyan-400/40 text-[10px] text-gray-700 dark:text-gray-300 hover:text-cyan-600 dark:hover:text-cyan-300 transition-all active:scale-95 group shadow-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-500 group-hover:rotate-180 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="font-bold tracking-wider uppercase">RE-BOOT</span>
                </button>
            </div>
        </div>

        <div class="flex flex-col-reverse lg:flex-row items-center gap-12 lg:gap-16">
            <!-- Left Info Column -->
            <div class="lg:w-1/2 space-y-6">
                <!-- Role / Job Title with Scramble -->
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded bg-cyan-500/10 border border-cyan-500/30 text-cyan-600 dark:text-cyan-400 font-mono text-xs font-black tracking-widest uppercase">
                        &gt;_
                    </span>
                    <h2 
                        @mouseenter="triggerMicroGlitch('title')"
                        class="text-sm font-black text-cyan-600 dark:text-cyan-400 tracking-[0.25em] uppercase font-mono cursor-pointer select-none transition-colors hover:text-cyan-500"
                    >
                        {{ displayTitle || targetTitle }}
                    </h2>
                </div>

                <!-- Name with Master Decrypt -->
                <h1 
                    @mouseenter="triggerMicroGlitch('name')"
                    class="text-5xl sm:text-6xl lg:text-7xl font-black tracking-tight text-gray-900 dark:text-white cursor-pointer select-none"
                >
                    {{ __('Hi, I\'m') }} 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 via-pink-600 to-cyan-500 drop-shadow-sm hover:brightness-125 transition-all">
                        {{ displayName || targetName }}
                    </span>
                </h1>

                <!-- Bio Content -->
                <p class="text-base sm:text-lg text-gray-600 dark:text-gray-300 leading-relaxed max-w-xl font-medium transition-opacity duration-700"
                   :class="bootPhase >= 2 ? 'opacity-100' : 'opacity-40 blur-[1px]'">
                    {{ hero.bio }}
                </p>

                <!-- Action Controls / Social Links -->
                <div class="pt-4 flex flex-wrap items-center gap-4 transition-all duration-700"
                     :class="bootPhase >= 2 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                    <a 
                        v-for="(link, platform) in socials" 
                        :key="platform" 
                        :href="link" 
                        target="_blank" 
                        class="relative group px-6 py-3 bg-gray-900 dark:bg-white/10 dark:hover:bg-white/20 backdrop-blur-md border border-white/10 text-white rounded-xl font-bold uppercase tracking-wide hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-purple-500/30 overflow-hidden"
                    >
                        <span class="relative z-10">{{ __(platform) }}</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>

                    <!-- FETCH_RESUME.EXE Button -->
                    <a 
                        v-if="resumeUrl" 
                        :href="resumeUrl" 
                        target="_blank" 
                        class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-cyan-500 text-white rounded-xl font-black uppercase tracking-widest text-xs hover:scale-105 hover:rotate-1 transition-all duration-300 shadow-xl shadow-emerald-500/20 group/resume flex items-center gap-3"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>{{ __('FETCH_RESUME.EXE') }}</span>
                    </a>
                </div>
            </div>

            <!-- Right Holo-Avatar Column -->
            <div class="lg:w-1/2 flex justify-center items-center select-none">
                <div 
                    ref="avatarContainer"
                    @mousemove="handleAvatarMouseMove"
                    @mouseenter="handleAvatarMouseEnter"
                    @mouseleave="handleAvatarMouseLeave"
                    class="relative w-80 h-80 sm:w-96 sm:h-96 flex items-center justify-center cursor-crosshair perspective-1000"
                >
                    <!-- Outer Rotating HUD Orbit Ring -->
                    <div 
                        class="pointer-events-none absolute inset-0 rounded-full border border-dashed border-cyan-500/30 dark:border-cyan-400/30 transition-all duration-700"
                        :class="isBooting ? 'animate-spin-fast scale-105 border-cyan-400' : 'animate-spin-slow'"
                    ></div>

                    <!-- Counter-Rotating Tech Ring -->
                    <div 
                        class="pointer-events-none absolute inset-4 rounded-full border border-purple-500/30 transition-transform duration-700 animate-reverse-spin"
                        style="border-left-color: rgba(168, 85, 247, 0.8); border-right-color: rgba(34, 211, 238, 0.8);"
                    ></div>

                    <!-- Target Reticle Corners -->
                    <div class="pointer-events-none absolute inset-2 transition-opacity duration-500" :class="bootPhase >= 1 ? 'opacity-80' : 'opacity-0'">
                        <span class="absolute top-0 left-0 text-cyan-500 dark:text-cyan-400 font-mono text-xs font-bold leading-none">[+]</span>
                        <span class="absolute top-0 right-0 text-cyan-500 dark:text-cyan-400 font-mono text-xs font-bold leading-none">[+]</span>
                        <span class="absolute bottom-0 left-0 text-purple-500 dark:text-purple-400 font-mono text-xs font-bold leading-none">[-]</span>
                        <span class="absolute bottom-0 right-0 text-purple-500 dark:text-purple-400 font-mono text-xs font-bold leading-none">[-]</span>
                    </div>

                    <!-- Glow Aura -->
                    <div class="absolute inset-8 bg-gradient-to-tr from-purple-600 via-pink-600 to-cyan-500 rounded-full blur-3xl opacity-40 animate-pulse pointer-events-none"></div>

                    <!-- Floating HUD Telemetry Badge: Top Right -->
                    <div 
                        class="absolute -top-3 -right-3 z-30 px-3 py-1 bg-white/90 dark:bg-gray-950/80 backdrop-blur-md rounded-lg border border-cyan-500/40 shadow-lg font-mono text-[10px] text-cyan-600 dark:text-cyan-400 font-bold flex items-center gap-1.5 transition-all duration-500"
                        :class="bootPhase >= 1 ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 -translate-y-4 scale-75'"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-ping"></span>
                        <span>BIOMETRIC: OK</span>
                    </div>

                    <!-- Floating HUD Telemetry Badge: Bottom Left -->
                    <div 
                        class="absolute -bottom-3 -left-3 z-30 px-3 py-1 bg-white/90 dark:bg-gray-950/80 backdrop-blur-md rounded-lg border border-purple-500/40 shadow-lg font-mono text-[10px] text-purple-700 dark:text-purple-300 font-bold flex items-center gap-1.5 transition-all duration-500"
                        :class="bootPhase >= 2 ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-4 scale-75'"
                    >
                        <span class="text-cyan-500 dark:text-cyan-400">&lt;/&gt;</span>
                        <span>FULLSTACK // VUE3</span>
                    </div>

                    <!-- Interactive 3D Avatar Card Holder -->
                    <div 
                        class="relative w-64 h-64 sm:w-80 sm:h-80 rounded-full p-1.5 transition-transform duration-150 ease-out will-change-transform"
                        :style="{
                            transform: `perspective(800px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale3d(${isAvatarHovered ? 1.04 : 1}, ${isAvatarHovered ? 1.04 : 1}, 1)`,
                        }"
                    >
                        <!-- Holographic Glare Filter Overlay -->
                        <div 
                            class="pointer-events-none absolute inset-0 rounded-full opacity-0 transition-opacity duration-300 z-20"
                            :class="isAvatarHovered ? 'opacity-30' : 'opacity-0'"
                            :style="{
                                background: `radial-gradient(circle at ${glareX}% ${glareY}%, rgba(255,255,255,0.8), transparent 60%)`
                            }"
                        ></div>

                        <!-- Inner Avatar Ring & Photo -->
                        <div class="relative w-full h-full rounded-full overflow-hidden border-2 border-white/30 dark:border-white/10 shadow-2xl bg-gray-950">
                            <img 
                                :src="hero.image || 'https://ui-avatars.com/api/?name=' + (hero.name || 'User') + '&background=random'" 
                                :alt="`Foto de perfil de ${hero.name || 'portfolio owner'}`"
                                class="w-full h-full object-cover transition-all duration-700"
                                :class="bootPhase >= 1 ? 'opacity-100 scale-100 filter-none' : 'opacity-0 scale-110 blur-md'"
                            />
                            
                            <!-- Continuous Scanline on Avatar -->
                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-transparent via-cyan-400/20 to-transparent opacity-40 animate-avatar-scan"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.perspective-1000 {
    perspective: 1000px;
}

@keyframes laserSweep {
    0% {
        top: -10%;
        opacity: 0;
    }
    20% {
        opacity: 1;
    }
    80% {
        opacity: 1;
    }
    100% {
        top: 110%;
        opacity: 0;
    }
}

.animate-laser-sweep {
    animation: laserSweep 1.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes avatarScan {
    0% {
        transform: translateY(-100%);
    }
    100% {
        transform: translateY(100%);
    }
}

.animate-avatar-scan {
    animation: avatarScan 4s linear infinite;
}

@keyframes spinSlow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin-slow {
    animation: spinSlow 20s linear infinite;
}

.animate-spin-fast {
    animation: spinSlow 3s linear infinite;
}

@keyframes reverseSpin {
    from {
        transform: rotate(360deg);
    }
    to {
        transform: rotate(0deg);
    }
}

.animate-reverse-spin {
    animation: reverseSpin 25s linear infinite;
}
</style>
