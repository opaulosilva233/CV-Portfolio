<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import CyberLayout from '@/Layouts/CyberLayout.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

defineProps({
    hero: Object,
    projects: Array,
    skills: Object,
    experiences: Array,
    educations: Array,
    socials: [Object, Array],
    canLogin: Boolean,
});

const activeSection = ref('about');
const sectionsList = ['about', 'skills', 'experience', 'education', 'projects'];
const navButtons = ref([]);
const indicatorLeft = ref(0);
const indicatorWidth = ref(0);
const scrollProgress = ref(0);
let observer = null;

const isDragging = ref(false);

const activeColorClass = computed(() => {
    switch (activeSection.value) {
        case 'about': return 'from-purple-600 to-indigo-600 shadow-purple-500/50';
        case 'skills': return 'from-cyan-500 to-blue-500 shadow-cyan-500/50';
        case 'experience': return 'from-pink-500 to-rose-500 shadow-pink-500/50';
        case 'education': return 'from-teal-500 to-emerald-500 shadow-teal-500/50';
        case 'projects': return 'from-orange-500 to-red-500 shadow-orange-500/50';
        default: return 'from-purple-600 to-indigo-600';
    }
});

const activeTextClass = computed(() => {
    switch (activeSection.value) {
        case 'about': return 'text-purple-600 dark:text-purple-400';
        case 'skills': return 'text-cyan-600 dark:text-cyan-400';
        case 'experience': return 'text-pink-600 dark:text-pink-400';
        case 'education': return 'text-teal-600 dark:text-teal-400';
        case 'projects': return 'text-orange-600 dark:text-orange-400';
        default: return 'text-purple-600 dark:text-purple-400';
    }
});

const updateIndicator = () => {
    const activeIndex = sectionsList.indexOf(activeSection.value);
    if (activeIndex !== -1 && navButtons.value[activeIndex]) {
        const el = navButtons.value[activeIndex];
        indicatorLeft.value = el.offsetLeft;
        indicatorWidth.value = el.offsetWidth;
    }
};

const updateScrollProgress = () => {
    // Determine the scroll progress percentage
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    scrollProgress.value = height > 0 ? (winScroll / height) * 100 : 0;
};

// --- Custom Draggable Scroll Logic ---
const trackElement = ref(null);

const startDrag = (e) => {
    e.preventDefault();
    isDragging.value = true;
    document.body.style.userSelect = 'none'; // prevent text selection while dragging
    window.addEventListener('mousemove', onDrag);
    window.addEventListener('mouseup', endDrag);
};

const onDrag = (e) => {
    if (!isDragging.value || !trackElement.value) return;
    
    const trackRect = trackElement.value.getBoundingClientRect();
    const trackHeight = trackRect.height;
    
    // Calculate where mouse is relative to the track
    let offsetY = e.clientY - trackRect.top;
    
    // Bound the values
    offsetY = Math.max(0, Math.min(offsetY, trackHeight));
    
    // Calculate percentage
    const percentage = offsetY / trackHeight;
    
    // Convert percentage to actual page scroll value
    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
    window.scrollTo({
        top: percentage * maxScroll,
        // Disable smooth behavior to prevent stuttering while dragging
        behavior: 'auto' 
    });
};

const endDrag = () => {
    isDragging.value = false;
    document.body.style.userSelect = '';
    window.removeEventListener('mousemove', onDrag);
    window.removeEventListener('mouseup', endDrag);
};
// ------------------------------------

watch(activeSection, async () => {
    await nextTick();
    updateIndicator();
});

const scrollTo = (id) => {
    const element = document.getElementById(id);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-PT', { month: 'short', year: 'numeric' });
};

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    activeSection.value = entry.target.id;
                }
            });
        },
        { rootMargin: '-30% 0px -70% 0px' }
    );

    sectionsList.forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            observer.observe(el);
        }
    });

    window.addEventListener('scroll', updateScrollProgress);
    window.addEventListener('resize', updateIndicator);
    
    // Initial updates
    setTimeout(() => {
        updateIndicator();
        updateScrollProgress();
    }, 100);
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
    window.removeEventListener('scroll', updateScrollProgress);
    window.removeEventListener('resize', updateIndicator);
});
</script>

<style scoped>
.terminal-fade-enter-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.terminal-fade-leave-active {
    transition: all 0.2s ease;
}
.terminal-fade-enter-from, .terminal-fade-leave-to {
    transform: translateY(10px) rotateX(90deg);
    opacity: 0;
    filter: blur(4px);
}
</style>

<template>
    <CyberLayout>
        <Head title="Portfolio" />
        
        <!-- Out of the Box: Interactive HUD Header -->
        <header class="fixed top-0 w-full z-50 bg-white/70 dark:bg-[#030712]/70 backdrop-blur-2xl transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-24 gap-4 xl:gap-8">
                    <!-- Logo & Cyber HUD Area -->
                    <div class="flex items-center gap-5 cursor-pointer group" @click="scrollTo('about')">
                        <div class="relative">
                            <img src="/images/Logotipo.png" alt="Logotipo" class="h-12 w-auto transform group-hover:scale-110 group-hover:rotate-[-5deg] transition-all duration-500 drop-shadow-lg" />
                            <div class="absolute inset-0 bg-white dark:bg-black mix-blend-color opacity-0 group-hover:opacity-20 transition-opacity"></div>
                        </div>
                        
                        <!-- High Emphasis Dynamic Name with HUD -->
                        <div class="flex flex-col justify-center translate-y-1">
                            <span class="font-black text-3xl lg:text-4xl tracking-tighter text-transparent bg-clip-text bg-gradient-to-r transition-all duration-700 hidden sm:block whitespace-nowrap drop-shadow-sm group-hover:drop-shadow-lg"
                                  :class="activeColorClass">
                                {{ hero.name || 'PORTFOLIO' }}
                            </span>
                            <div class="hidden sm:flex items-center gap-3 h-6 mt-0.5">
                                <span class="text-[10px] font-black tracking-[0.3em] uppercase transition-colors duration-500 flex items-center gap-1"
                                      :class="activeTextClass">
                                    <div class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></div>
                                    SYS.NAV
                                </span>
                                <div class="w-12 h-[1px] bg-gradient-to-r from-gray-300 to-transparent dark:from-white/20 dark:to-transparent"></div>
                                <transition name="terminal-fade" mode="out-in">
                                    <span :key="activeSection" class="text-[11px] font-bold tracking-[0.2em] uppercase text-gray-400 dark:text-gray-500">
                                        // CURRENT_LOC: <span class="text-gray-800 dark:text-gray-200">{{ __(activeSection) }}</span>
                                    </span>
                                </transition>
                            </div>
                        </div>
                    </div>

                    <!-- Center Navigation Links: Gliding Pill -->
                    <nav class="hidden lg:flex relative items-center p-1.5 bg-gray-200/40 dark:bg-white/5 rounded-2xl border border-gray-300/50 dark:border-white/10 shadow-inner">
                        
                        <!-- The Liquid Gliding Pill -->
                        <div class="absolute top-1.5 bottom-1.5 left-0 bg-gradient-to-r rounded-xl transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] z-0"
                             :class="activeColorClass"
                             :style="{
                                 transform: `translateX(${indicatorLeft}px)`,
                                 width: `${indicatorWidth}px`
                             }">
                             <!-- Inner Glow for that sleek modern look -->
                             <div class="absolute inset-0 bg-white/20 dark:bg-black/10 rounded-xl"></div>
                        </div>

                        <!-- Buttons dynamically bound to refs -->
                        <button 
                            v-for="(section, index) in sectionsList"
                            :key="section"
                            :ref="el => navButtons[index] = el"
                            @click="scrollTo(section)"
                            class="relative z-10 px-3 xl:px-5 py-2 xl:py-2.5 rounded-xl text-[10px] xl:text-[11px] font-black tracking-wider xl:tracking-[0.15em] uppercase transition-all duration-300"
                            :class="activeSection === section ? 'text-white scale-100 xl:scale-105' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:scale-100 xl:hover:scale-105'"
                        >
                            {{ __(section.charAt(0).toUpperCase() + section.slice(1)) }}
                        </button>
                    </nav>

                    <!-- Right Options & Admin -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 p-1 bg-gray-100 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10">
                            <LanguageSwitcher />
                            <div class="w-px h-5 bg-gray-300 dark:bg-gray-700"></div>
                            <ThemeToggle variant="cyber" />
                        </div>
                        
                        <Link v-if="canLogin" :href="route('dashboard')" class="hidden md:flex ml-2 px-5 py-2 rounded-xl bg-gray-900 dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 text-white dark:text-gray-900 text-xs font-black uppercase tracking-widest transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5">
                            {{ __('Admin') }}
                        </Link>
                    </div>
                </div>
            </div>
            
            <!-- Laser Scroll Progress Bar -->
            <div class="absolute bottom-0 left-0 w-full h-[2px] bg-gray-200/50 dark:bg-white/5 overflow-hidden shadow-[inset_0_1px_2px_rgba(0,0,0,0.1)]">
                <div class="h-full bg-gradient-to-r relative transition-all duration-100 ease-out"
                     :class="activeColorClass"
                     :style="{ width: scrollProgress + '%' }">
                     <!-- Laser Head Glow -->
                     <div class="absolute right-0 top-1/2 -translate-y-1/2 w-8 h-[2px] bg-white opacity-80 blur-[2px]"></div>
                     <div class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-[2px] bg-white"></div>
                </div>
            </div>
        </header>

        <!-- Sleek & Minimal Custom DOM Scrollbar -->
        <div class="fixed right-2 top-32 bottom-12 z-40 flex items-center justify-center w-8 pointer-events-none hidden md:flex transition-all duration-500"
             :class="isDragging ? 'opacity-100' : 'opacity-20 hover:opacity-100'">
            <!-- Scroll Track Base -->
            <div class="relative h-full w-[3px] bg-gray-300/50 dark:bg-gray-700/50 rounded-full" ref="trackElement">
                
                <!-- The Elevator Thumb (Interactive) -->
                <!-- Added extra padding so it's easier to grab -->
                <div class="absolute left-1/2 -translate-x-1/2 flex flex-col items-center pointer-events-auto cursor-ns-resize px-4 py-2 group/thumb"
                     :style="{ top: `calc(${scrollProgress}% - 20px)` }"
                     @mousedown="startDrag">
                    
                    <!-- Sleek Core Capsule -->
                    <div class="w-1.5 h-8 rounded-full transition-all duration-300"
                         :class="isDragging ? 'bg-gray-900 dark:bg-white scale-y-125' : 'bg-gray-500 dark:bg-gray-400 group-hover/thumb:bg-gray-800 dark:group-hover/thumb:bg-gray-200'">
                    </div>
                </div>
            </div>
        </div>

        <!-- Hero Section -->
        <section id="about" class="pt-32 pb-20 px-4 min-h-screen flex items-center">
            <div class="max-w-7xl mx-auto flex flex-col-reverse lg:flex-row items-center gap-12">
                <div class="lg:w-1/2 space-y-6">
                    <h2 class="text-sm font-bold text-cyan-600 dark:text-cyan-400 tracking-[0.2em] uppercase animate-pulse">
                        {{ hero.title || 'Full Stack Developer' }}
                    </h2>
                    <h1 class="text-5xl lg:text-7xl font-black tracking-tight text-gray-900 dark:text-white">
                        Hi, I'm <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 via-pink-600 to-cyan-600">{{ hero.name }}</span>
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed max-w-lg font-medium">
                        {{ hero.bio }}
                    </p>
                    <div class="pt-4 flex gap-4">
                        <a v-for="(link, platform) in socials" :key="platform" :href="link" target="_blank" class="px-6 py-3 bg-gray-900 dark:bg-white/10 dark:hover:bg-white/20 backdrop-blur-md border border-white/10 text-white dark:text-white rounded-xl font-bold uppercase tracking-wide hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-purple-500/30">
                            {{ platform }}
                        </a>
                    </div>
                </div>
                <div class="lg:w-1/2 flex justify-center">
                    <div class="relative w-72 h-72 lg:w-96 lg:h-96">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-cyan-600 rounded-full blur-2xl opacity-50 animate-pulse"></div>
                        <div class="relative w-full h-full rounded-full overflow-hidden border-4 border-white/20 dark:border-white/10 shadow-2xl">
                            <img 
                                :src="hero.image || 'https://ui-avatars.com/api/?name=' + (hero.name || 'User') + '&background=random'" 
                                alt="Profile" 
                                class="w-full h-full object-cover"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Skills Section -->
        <section id="skills" class="py-20 px-4">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl font-black mb-12 text-center text-gray-900 dark:text-white uppercase tracking-widest"><span class="text-purple-500">#</span> {{ __('Technical Skills') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(categorySkills, category) in skills" :key="category" class="bg-white/50 dark:bg-black/40 backdrop-blur-xl p-6 rounded-2xl shadow-lg border border-white/20 dark:border-white/5 hover:border-purple-500/50 transition-colors duration-300 group">
                        <h3 class="text-xl font-bold mb-6 capitalize border-b-2 border-purple-500/30 pb-2 w-max group-hover:border-purple-500 transition-colors text-gray-800 dark:text-gray-100">{{ category }}</h3>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="skill in categorySkills" :key="skill.id" class="px-3 py-1 bg-white/60 dark:bg-white/10 rounded-lg text-sm font-bold text-gray-700 dark:text-gray-200 border border-transparent hover:border-cyan-500/50 transition-all">
                                {{ skill.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Experience Section -->
        <section id="experience" class="py-20 px-4">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-black mb-12 text-center text-gray-900 dark:text-white uppercase tracking-widest"><span class="text-cyan-500">#</span> {{ __('Experience') }}</h2>
                <div class="space-y-12">
                    <div v-for="exp in experiences" :key="exp.id" class="relative pl-8 border-l-2 border-purple-200 dark:border-purple-900/50">
                        <!-- Company Node -->
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-purple-600 ring-4 ring-white dark:ring-black shadow-[0_0_10px_rgba(147,51,234,0.5)]"></div>
                        
                        <div class="flex items-center gap-3 mb-1">
                            <img v-if="exp.image_url" :src="exp.image_url" :alt="exp.company" class="w-10 h-10 rounded-lg object-contain bg-white/80 dark:bg-white/10 border border-gray-200 dark:border-white/10 p-0.5" />
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ exp.company }}</h3>
                        </div>
                        <div class="text-md font-medium text-gray-500 dark:text-gray-400 mb-4 pb-4 border-b border-gray-100 dark:border-white/5" v-if="exp.location">{{ exp.location }}</div>
                        <div class="text-md font-medium text-gray-500 dark:text-gray-400 mb-4 pb-4 border-b border-gray-100 dark:border-white/5" v-else></div>
                        
                        <!-- Nested Roles -->
                        <div class="space-y-6 mt-4 relative">
                            <!-- Inner vertical line to connect roles -->
                            <div class="absolute left-[7px] top-4 bottom-4 w-px bg-gradient-to-b from-cyan-500/50 via-cyan-500/20 to-transparent dark:from-cyan-500/30 dark:to-transparent" v-if="exp.roles && exp.roles.length > 1"></div>
                            
                            <div v-for="(role, index) in exp.roles" :key="index" class="relative pl-8">
                                <!-- Role Node bullet -->
                                <div class="absolute left-[3.5px] top-2.5 w-2 h-2 rounded-full bg-cyan-500 ring-2 ring-white dark:ring-black" v-if="exp.roles && exp.roles.length > 1"></div>
                                
                                <h4 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                                    {{ role.role }}
                                    <span v-if="role.employment_type" class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-500 dark:text-purple-400 border border-purple-500/20 align-middle">
                                        {{ {full_time: __('Full Time'), part_time: __('Part Time'), temporary: __('Temporary Work'), internship: __('Internship')}[role.employment_type] }}
                                    </span>
                                </h4>
                                <div class="mb-2 mt-1 text-sm text-cyan-600 dark:text-cyan-400 font-bold uppercase tracking-wide flex items-center gap-2">
                                    {{ formatDate(role.start_date) }} - {{ role.is_current ? __('Present') : formatDate(role.end_date) }}
                                    <span v-if="role.is_current" class="px-2 py-0.5 inline-flex text-[10px] leading-4 font-semibold rounded bg-green-500/20 text-green-400 border border-green-500/30 shadow-[0_0_10px_rgba(34,197,94,0.2)]">
                                        {{ __('Current') }}
                                    </span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line leading-relaxed text-sm">{{ role.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Education && Certificates Section -->
        <section id="education" class="py-20 px-4">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-black mb-12 text-center text-gray-900 dark:text-white uppercase tracking-widest"><span class="text-teal-500">#</span> {{ __('Education & Certificates') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="edu in educations" :key="edu.id" class="bg-white/50 dark:bg-black/40 backdrop-blur-xl p-8 rounded-2xl shadow-lg border border-white/20 dark:border-white/5 hover:border-teal-500/50 hover:shadow-teal-500/20 transition-all duration-300 group relative overflow-hidden">
                        
                        <!-- Glowing accent line -->
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r" :class="edu.type === 'certificate' ? 'from-cyan-500 to-blue-500' : 'from-teal-500 to-emerald-500'"></div>

                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span 
                                    class="inline-block px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full mb-3 border"
                                    :class="edu.type === 'certificate' ? 'bg-cyan-500/10 text-cyan-500 border-cyan-500/30' : 'bg-teal-500/10 text-teal-500 border-teal-500/30'"
                                >
                                    {{ edu.type === 'certificate' ? __('Certificate') : __('Education') }}
                                </span>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-teal-400 transition-colors">{{ edu.degree || edu.institution }}</h3>
                                <h4 class="text-md font-medium text-gray-600 dark:text-gray-300 mt-1" v-if="edu.degree">{{ edu.institution }}</h4>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-sm font-bold text-gray-500 dark:text-gray-400 tracking-wide uppercase mb-4" v-if="edu.start_date || edu.end_date || edu.is_current">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span v-if="edu.start_date && edu.end_date">{{ formatDate(edu.start_date) }} - {{ formatDate(edu.end_date) }}</span>
                            <span v-else-if="edu.start_date && edu.is_current">{{ formatDate(edu.start_date) }} - {{ __('Present') }}</span>
                            <span v-else-if="edu.is_current">{{ __('Present') }}</span>
                            <span v-else-if="edu.start_date">{{ formatDate(edu.start_date) }} - {{ __('Present') }}</span>
                            <span v-else-if="edu.end_date">{{ formatDate(edu.end_date) }}</span>
                        </div>

                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-6" v-if="edu.description">
                            {{ edu.description }}
                        </p>

                        <a v-if="edu.url" :href="edu.url" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-teal-500 hover:text-teal-400 transition-colors mt-auto">
                            <span>{{ __('View Credential') }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    </div>
                </div>

                <div v-if="!educations || educations.length === 0" class="text-center text-gray-500 dark:text-gray-400 italic">
                    {{ __('No education or certificates found.') }}
                </div>
            </div>
        </section>

        <!-- Projects Section -->
        <section id="projects" class="py-20 px-4">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black mb-12 text-center text-gray-900 dark:text-white uppercase tracking-widest"><span class="text-pink-500">#</span> {{ __('Featured Projects') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="project in projects" :key="project.id" class="group bg-white/50 dark:bg-black/40 backdrop-blur-xl rounded-2xl overflow-hidden shadow-lg border border-white/20 dark:border-white/5 hover:border-pink-500/50 hover:shadow-pink-500/20 transition-all duration-300 hover:-translate-y-2">
                        <div class="h-48 overflow-hidden bg-gray-200 dark:bg-gray-800 relative">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent z-10 opacity-60"></div>
                            <img 
                                :src="project.image_url || 'https://via.placeholder.com/400x300?text=' + project.title" 
                                :alt="project.title"
                                class="w-full h-full object-cover transition duration-500 group-hover:scale-110"
                            />
                        </div>
                        <div class="p-6 relative z-20">
                            <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white group-hover:text-pink-500 transition-colors">{{ project.title }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">{{ project.description }}</p>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span v-for="skill in project.skills" :key="skill.id" class="text-[10px] uppercase font-bold px-2 py-1 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded border border-transparent group-hover:border-pink-500/30 transition-colors">
                                    {{ skill.name }}
                                </span>
                            </div>
                            <div class="flex gap-4 mt-auto">
                                <a v-if="project.project_url" :href="project.project_url" target="_blank" class="text-pink-600 font-bold hover:underline uppercase text-xs tracking-wider">{{ __('View Live') }}</a>
                                <a v-if="project.github_url" :href="project.github_url" target="_blank" class="text-gray-500 hover:text-gray-900 dark:hover:text-white font-bold uppercase text-xs tracking-wider">GitHub</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="py-10 text-center text-gray-500 dark:text-gray-400 text-sm font-medium border-t border-white/10">
            &copy; {{ new Date().getFullYear() }} {{ hero.name }}. <span class="opacity-50">{{ __('System Online.') }}</span>
        </footer>
    </CyberLayout>
</template>
