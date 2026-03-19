<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import CyberLayout from '@/Layouts/CyberLayout.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import ContactSection from '@/Components/ContactSection.vue';
import CyberTerminal from '@/Components/CyberTerminal.vue';

const props = defineProps({
    hero: Object,
    projects: Array,
    skills: Object,
    experiences: Array,
    educations: Array,
    interests: Object,
    socials: [Object, Array],
    canLogin: Boolean,
    seo: Object,
    footer_text: String,
    contact_email: String,
    contact_phone: String,
    contact_address: String,
    resume_url: String,
});

const activeSection = ref('about');
const sectionsList = ['about', 'interests', 'skills', 'timeline', 'terminal', 'contact'];
const allSectionsList = ['about', 'interests', 'skills', 'timeline', 'experience', 'education', 'projects', 'terminal', 'contact'];
const navButtons = ref([]);
const indicatorLeft = ref(0);
const indicatorWidth = ref(0);
const scrollProgress = ref(0);
let observer = null;

const isDragging = ref(false);

const activeColorClass = computed(() => {
    switch (activeSection.value) {
        case 'about': return 'from-purple-600 to-indigo-600 shadow-purple-500/50';
        case 'interests': return 'from-pink-500 to-purple-500 shadow-pink-500/50';
        case 'skills': return 'from-cyan-500 to-blue-500 shadow-cyan-500/50';
        case 'timeline': return 'from-pink-500 to-rose-500 shadow-pink-500/50';
        case 'experience': return 'from-purple-500 to-violet-600 shadow-purple-500/50';
        case 'education': return 'from-cyan-500 to-blue-600 shadow-cyan-500/50';
        case 'terminal': return 'from-emerald-500 to-teal-500 shadow-emerald-500/50';
        case 'contact': return 'from-orange-500 to-red-500 shadow-orange-500/50';
        default: return 'from-purple-600 to-indigo-600';
    }
});

const activeTextClass = computed(() => {
    switch (activeSection.value) {
        case 'about': return 'text-purple-600 dark:text-purple-400';
        case 'interests': return 'text-pink-600 dark:text-pink-400';
        case 'skills': return 'text-cyan-600 dark:text-cyan-400';
        case 'timeline': return 'text-pink-600 dark:text-pink-400';
        case 'experience': return 'text-purple-600 dark:text-purple-400';
        case 'education': return 'text-cyan-600 dark:text-cyan-400';
        case 'terminal': return 'text-emerald-600 dark:text-emerald-400';
        case 'contact': return 'text-orange-600 dark:text-orange-400';
        default: return 'text-purple-600 dark:text-purple-400';
    }
});

const timelineItems = computed(() => {
    const items = [];

    // Add projects
    props.projects.forEach(p => {
        items.push({
            id: `project-${p.id}`,
            type: 'project',
            date: new Date(p.completed_at || p.created_at),
            title: p.title,
            description: p.description,
            image: p.image_url,
            tags: p.skills,
            project_url: p.project_url,
            github_url: p.github_url,
            in_progress: p.in_progress
        });
    });

    // Add experiences
    props.experiences.forEach(exp => {
        exp.roles.forEach((role, idx) => {
            items.push({
                id: `exp-${exp.id}-${idx}`,
                type: 'experience',
                date: new Date(role.start_date),
                endDate: role.is_current ? new Date() : new Date(role.end_date),
                title: role.role,
                subtitle: exp.company,
                description: role.description,
                image: exp.image_url,
                location: exp.location,
                is_current: role.is_current,
                employment_type: role.employment_type
            });
        });
    });

    // Add education
    props.educations.forEach(edu => {
        items.push({
            id: `edu-${edu.id}`,
            type: 'education',
            date: new Date(edu.start_date || edu.end_date),
            title: edu.degree || edu.institution,
            subtitle: edu.degree ? edu.institution : '',
            description: edu.description,
            eduType: edu.type,
            url: edu.url,
            is_current: edu.is_current
        });
    });

    return items.sort((a, b) => b.date - a.date);
});

const timelineContainer = ref(null);
const horizontalTarget = ref(null);
const translateX = ref(0);
const scrollProgressTimeline = ref(0);

const handleTimelineScroll = () => {
    if (!timelineContainer.value) return;

    const rect = timelineContainer.value.getBoundingClientRect();
    const windowHeight = window.innerHeight;
    
    // Calculate progress within the timeline container
    // Start measuring when the container starts entering the viewport
    // End when it finishes
    const start = 0;
    const end = Math.max(1, rect.height - windowHeight);
    const current = Math.max(0, -rect.top);
    
    let progress = current / end;
    progress = Math.max(0, Math.min(1, progress));
    
    scrollProgressTimeline.value = progress * 100;
    
    // We want to translate from 0 to -(totalWidth - viewportWidth)
    if (horizontalTarget.value) {
        const totalWidth = horizontalTarget.value.scrollWidth;
        const viewportWidth = window.innerWidth;
        const maxTranslate = totalWidth - viewportWidth;
        translateX.value = progress * maxTranslate;
    }
};

const trackElement = ref(null);

const updateIndicator = () => {
    const activeIndex = sectionsList.indexOf(activeSection.value);
    if (activeIndex !== -1 && navButtons.value[activeIndex]) {
        const el = navButtons.value[activeIndex];
        indicatorLeft.value = el.offsetLeft;
        indicatorWidth.value = el.offsetWidth;
    }
};

const updateScrollProgress = () => {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    scrollProgress.value = height > 0 ? (winScroll / height) * 100 : 0;
};

// Intersection Observer for Timeline Items
const itemRefs = ref([]);
const visibleItems = ref(new Set());

onMounted(() => {
    const itemObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                visibleItems.value.add(entry.target.dataset.id);
            }
        });
    }, { threshold: 0.2, rootMargin: '0px 0px -100px 0px' });

    itemRefs.value.forEach(el => {
        if (el) itemObserver.observe(el);
    });

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const id = entry.target.id;
                    if (['experience', 'education', 'projects'].includes(id)) {
                        activeSection.value = 'timeline';
                    } else {
                        activeSection.value = id;
                    }
                }
            });
        },
        { rootMargin: '-30% 0px -70% 0px' }
    );

    allSectionsList.forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            observer.observe(el);
        }
    });

    window.addEventListener('scroll', updateScrollProgress);
    window.addEventListener('scroll', handleTimelineScroll);
    window.addEventListener('resize', updateIndicator);
    window.addEventListener('resize', handleTimelineScroll);
    
    setTimeout(() => {
        updateIndicator();
        updateScrollProgress();
        handleTimelineScroll();
    }, 100);
});

onUnmounted(() => {
    if (observer) observer.disconnect();
    window.removeEventListener('scroll', updateScrollProgress);
    window.removeEventListener('scroll', handleTimelineScroll);
    window.removeEventListener('resize', updateIndicator);
    window.removeEventListener('resize', handleTimelineScroll);
});

const startDrag = (e) => {
    e.preventDefault();
    isDragging.value = true;
    document.body.style.userSelect = 'none';
    window.addEventListener('mousemove', onDrag);
    window.addEventListener('mouseup', endDrag);
};
const onDrag = (e) => {
    if (!isDragging.value || !trackElement.value) return;
    const trackRect = trackElement.value.getBoundingClientRect();
    const trackHeight = trackRect.height;
    let offsetY = e.clientY - trackRect.top;
    offsetY = Math.max(0, Math.min(offsetY, trackHeight));
    const percentage = offsetY / trackHeight;
    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
    window.scrollTo({ top: percentage * maxScroll, behavior: 'auto' });
};
const endDrag = () => {
    isDragging.value = false;
    document.body.style.userSelect = '';
    window.removeEventListener('mousemove', onDrag);
    window.removeEventListener('mouseup', endDrag);
};

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

const formatDate = (date) => {
    if (!date) return '';
    return date.toLocaleDateString('pt-PT', { month: 'short', year: 'numeric' });
};
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

.timeline-line-grow {
    width: v-bind('scrollProgressTimeline + "%"');
    transition: width 0.1s ease-out;
}

.timeline-item {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.timeline-item.is-visible {
    opacity: 1;
    transform: translateY(0);
}

@media (min-width: 768px) {
    .timeline-item {
        transform: translateX(50px);
    }
    .timeline-item.is-visible {
        transform: translateX(0);
    }
}
</style>

<template>
    <CyberLayout>
        <Head :title="seo?.title || 'Portfolio'">
            <meta name="description" :content="seo?.description" v-if="seo?.description" />
            <meta name="keywords" :content="seo?.keywords" v-if="seo?.keywords" />
        </Head>
        
        <header class="fixed top-0 w-full z-50 bg-white/70 dark:bg-[#030712]/70 backdrop-blur-2xl transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-24 gap-4 xl:gap-8">
                    <div class="flex items-center gap-5 cursor-pointer group" @click="scrollTo('about')">
                        <div class="relative">
                            <img src="/images/Logotipo.png" alt="Logotipo" class="h-12 w-auto transform group-hover:scale-110 group-hover:rotate-[-5deg] transition-all duration-500 drop-shadow-lg" />
                            <div class="absolute inset-0 bg-white dark:bg-black mix-blend-color opacity-0 group-hover:opacity-20 transition-opacity"></div>
                        </div>
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
                                    <div :key="activeSection" class="flex items-center gap-4">
                                        <span class="text-[11px] font-bold tracking-[0.2em] uppercase text-gray-400 dark:text-gray-500">
                                            // CURRENT_LOC: <span class="text-gray-800 dark:text-gray-200">{{ __(activeSection) }}</span>
                                        </span>
                                        <div class="h-4 w-px bg-gray-300 dark:bg-white/10 hidden xl:block"></div>
                                        <span class="text-[10px] font-mono text-gray-400 hidden xl:block">
                                            REGION: {{ new Intl.DateTimeFormat().resolvedOptions().timeZone }}
                                        </span>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>

                    <nav class="hidden lg:flex relative items-center p-1.5 bg-gray-200/40 dark:bg-white/5 rounded-2xl border border-gray-300/50 dark:border-white/10 shadow-inner">
                        <div class="absolute top-1.5 bottom-1.5 left-0 bg-gradient-to-r rounded-xl transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] z-0"
                             :class="activeColorClass"
                             :style="{
                                 transform: `translateX(${indicatorLeft}px)`,
                                 width: `${indicatorWidth}px`
                             }">
                             <div class="absolute inset-0 bg-white/20 dark:bg-black/10 rounded-xl"></div>
                        </div>
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
            
            <div class="absolute bottom-0 left-0 w-full h-[2px] bg-gray-200/50 dark:bg-white/5 overflow-hidden shadow-[inset_0_1px_2px_rgba(0,0,0,0.1)]">
                <div class="h-full bg-gradient-to-r relative transition-all duration-100 ease-out"
                     :class="activeColorClass"
                     :style="{ width: scrollProgress + '%' }">
                     <div class="absolute right-0 top-1/2 -translate-y-1/2 w-8 h-[2px] bg-white opacity-80 blur-[2px]"></div>
                     <div class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-[2px] bg-white"></div>
                </div>
            </div>
        </header>

        <div class="fixed right-2 top-32 bottom-12 z-40 flex items-center justify-center w-8 pointer-events-none hidden md:flex transition-all duration-500"
             :class="isDragging ? 'opacity-100' : 'opacity-20 hover:opacity-100'">
            <div class="relative h-full w-[3px] bg-gray-300/50 dark:bg-gray-700/50 rounded-full" ref="trackElement">
                <div class="absolute left-1/2 -translate-x-1/2 flex flex-col items-center pointer-events-auto cursor-ns-resize px-4 py-2 group/thumb"
                     :style="{ top: `calc(${scrollProgress}% - 20px)` }"
                     @mousedown="startDrag">
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
                        <!-- FETCH_RESUME.EXE Button -->
                        <a v-if="resume_url" :href="resume_url" target="_blank" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-cyan-500 text-white rounded-xl font-black uppercase tracking-widest text-xs hover:scale-105 hover:rotate-1 transition-all duration-300 shadow-xl shadow-emerald-500/20 group/resume flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ __('FETCH_RESUME.EXE') }}
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
        
        <!-- Interests Section -->
        <section id="interests" class="py-20 px-4" v-if="interests && Object.keys(interests).length > 0">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl font-black mb-12 text-center text-gray-900 dark:text-white uppercase tracking-widest"><span class="text-pink-500">#</span> {{ __('Beyond the Code') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(categoryInterests, category) in interests" :key="category" class="bg-white/30 dark:bg-white/5 backdrop-blur-xl p-8 rounded-3xl border border-white/20 dark:border-white/10 shadow-xl hover:shadow-pink-500/10 transition-all duration-500 group">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-pink-500/20 to-purple-500/20 flex items-center justify-center border border-pink-500/30 group-hover:scale-110 transition-transform duration-500">
                                <span class="text-2xl" v-if="category === 'music'">🎸</span>
                                <span class="text-2xl" v-else-if="category === 'hobby'">🎨</span>
                                <span class="text-2xl" v-else-if="category === 'sport'">⚽</span>
                                <span class="text-2xl" v-else-if="category === 'book'">📚</span>
                                <span class="text-2xl" v-else-if="category === 'travel'">✈️</span>
                                <span class="text-2xl" v-else>✨</span>
                            </div>
                            <h3 class="text-xl font-black capitalize tracking-tight text-gray-800 dark:text-gray-100 group-hover:text-pink-400 transition-colors">{{ __(category) }}</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div v-for="interest in categoryInterests" :key="interest.id" class="relative group/item">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 w-6 h-6 shrink-0 [&>svg]:w-full [&>svg]:h-full text-pink-500" v-if="interest.icon" v-html="interest.icon"></div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white leading-tight">{{ interest.name }}</h4>
                                        <p v-if="interest.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2 transition-all group-hover/item:line-clamp-none">{{ interest.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Skills Section -->
        <section id="skills" class="py-20 px-4 scroll-mt-24">
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

        <!-- Dynamic Timeline Section -->
        <!-- Desktop Horizontal Timeline -->
        <section id="timeline" ref="timelineContainer" class="relative hidden md:block" :style="{ height: (timelineItems.length * 50) + 'vh' }">
            <div class="sticky top-0 h-screen flex flex-col justify-center overflow-hidden bg-gray-50/30 dark:bg-[#030712]/30">
                <div class="max-w-7xl mx-auto w-full px-8 mb-12">
                    <h2 class="text-5xl lg:text-7xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                        {{ __('History') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-500">&</span> {{ __('Projects') }}
                    </h2>
                </div>

                <div class="relative flex items-center transition-transform duration-100 ease-out" ref="horizontalTarget" :style="{ transform: `translateX(-${translateX}px)` }">
                    <!-- The Interactive Growing Horizontal Line -->
                    <div class="absolute top-1/2 left-0 w-full h-[2px] bg-gray-200/50 dark:bg-white/5">
                        <div class="h-full bg-gradient-to-r from-purple-600 via-pink-600 to-cyan-400 timeline-line-grow rounded-full shadow-[0_0_15px_rgba(236,72,153,0.5)]"></div>
                    </div>

                    <!-- Timeline Loop -->
                    <div v-for="(item, index) in timelineItems" :key="item.id" 
                         :ref="el => itemRefs[index] = el"
                         :data-id="item.id"
                         class="timeline-item flex-shrink-0 w-[450px] mx-12 relative"
                         :class="[
                             visibleItems.has(item.id) ? 'is-visible' : '',
                             index % 2 === 0 ? '-translate-y-8' : 'translate-y-8'
                         ]">
                        
                        <!-- Timeline Node -->
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 flex items-center justify-center z-10 transition-transform duration-500"
                             :class="visibleItems.has(item.id) ? 'scale-110' : 'scale-0'">
                            <div class="w-4 h-4 rounded-full ring-4 ring-white dark:ring-[#030712] shadow-xl"
                                 :class="{
                                     'bg-purple-600 shadow-purple-500/50': item.type === 'experience',
                                     'bg-cyan-500 shadow-cyan-500/50': item.type === 'education',
                                     'bg-pink-600 shadow-pink-500/50': item.type === 'project'
                                 }">
                            </div>
                        </div>

                        <!-- Content Card -->
                        <div class="group">
                            <div class="bg-white/60 dark:bg-white/5 backdrop-blur-xl p-6 rounded-[2rem] border border-white/20 dark:border-white/10 shadow-xl group-hover:border-current transition-all duration-300 relative overflow-hidden"
                                 :class="{
                                     'hover:shadow-purple-500/20 text-purple-600 dark:text-purple-400': item.type === 'experience',
                                     'hover:shadow-cyan-500/20 text-cyan-500 dark:text-cyan-400': item.type === 'education',
                                     'hover:shadow-pink-500/20 text-pink-500 dark:text-pink-400': item.type === 'project'
                                 }">
                                
                                <div class="absolute -top-12 -right-12 w-24 h-24 bg-current opacity-5 blur-[40px] rounded-full group-hover:opacity-10 transition-opacity"></div>

                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div v-if="item.image" class="w-10 h-10 rounded-lg overflow-hidden bg-white/80 dark:bg-white/10 border border-current/20 p-1 flex-shrink-0">
                                            <img :src="item.image" class="w-full h-full object-contain" />
                                        </div>
                                        <div>
                                            <span class="text-[9px] font-black uppercase tracking-widest opacity-60 mb-1 block">
                                                {{ __(item.type) }}
                                            </span>
                                            <h3 class="text-lg font-black text-gray-900 dark:text-white leading-tight line-clamp-1">
                                                {{ item.title }}
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <span class="text-[10px] font-black px-2 py-1 bg-current/10 rounded-lg">
                                            {{ item.date.getFullYear() }}
                                        </span>
                                    </div>
                                </div>

                                <div v-if="item.type === 'project' && item.image" class="mt-4 mb-4 rounded-xl overflow-hidden border border-white/10 aspect-video relative group/img">
                                    <img :src="item.image" class="w-full h-full object-cover transition-transform duration-700 group-hover/img:scale-110" />
                                </div>

                                <p class="text-gray-600 dark:text-gray-400 text-xs leading-relaxed mb-4 line-clamp-3 group-hover:line-clamp-none transition-all duration-300">
                                    {{ item.description }}
                                </p>

                                <div class="flex flex-wrap items-center justify-between gap-4 mt-auto">
                                    <div class="flex gap-4">
                                        <a v-if="item.project_url" :href="item.project_url" target="_blank" class="text-[9px] font-black uppercase tracking-wider hover:underline transition-all">{{ __('View Live') }}</a>
                                        <a v-if="item.github_url" :href="item.github_url" target="_blank" class="text-[9px] font-black uppercase tracking-wider opacity-60 hover:opacity-100 transition-all">GitHub</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date Spacer -->
                        <div class="absolute top-full left-1/2 -translate-x-1/2 mt-8 text-4xl font-black text-gray-200 dark:text-white/5 select-none pointer-events-none transform -rotate-12 transition-all duration-500">
                            {{ formatDate(item.date) }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mobile Vertical Timeline -->
        <section id="timeline-mobile" class="py-24 px-4 relative overflow-hidden md:hidden scroll-mt-24">
            <h2 class="text-4xl font-black mb-16 text-center text-gray-900 dark:text-white uppercase tracking-tighter">
                {{ __('History') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-500">&</span> {{ __('Projects') }}
            </h2>
            <div class="relative">
                <div class="absolute left-4 top-0 bottom-0 w-[2px] bg-gray-200 dark:bg-white/5"></div>
                <div v-for="item in timelineItems" :key="item.id" class="pl-10 mb-12 relative">
                    <div class="absolute left-[13px] top-1.5 w-3 h-3 rounded-full ring-2 ring-white dark:ring-black"
                         :class="{
                             'bg-purple-600 shadow-purple-500/50': item.type === 'experience',
                             'bg-cyan-500 shadow-cyan-500/50': item.type === 'education',
                             'bg-pink-600 shadow-pink-500/50': item.type === 'project'
                         }">
                    </div>
                    <div class="bg-white/60 dark:bg-white/5 backdrop-blur-xl p-6 rounded-2xl border border-white/20 dark:border-white/10">
                        <span class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-2 block">{{ item.date.getFullYear() }} // {{ __(item.type) }}</span>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ item.title }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">{{ item.description }}</p>
                        <div class="flex gap-4">
                            <a v-if="item.project_url" :href="item.project_url" target="_blank" class="text-xs font-bold uppercase tracking-wider text-current">{{ __('View Live') }}</a>
                            <a v-if="item.github_url" :href="item.github_url" target="_blank" class="text-xs font-bold uppercase tracking-wider opacity-60">GitHub</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Experience Section -->
        <section id="experience" class="py-24 px-4 scroll-mt-24 bg-gray-50/30 dark:bg-white/[0.02]">
            <div class="max-w-7xl mx-auto">
                <div class="mb-16">
                    <h2 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                        {{ __('Professional') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-500 to-violet-500">{{ __('Journey') }}</span>
                    </h2>
                    <div class="w-24 h-2 bg-purple-500 mt-4 rounded-full"></div>
                </div>

                <div class="space-y-12">
                    <div v-for="exp in experiences" :key="exp.id" class="relative group">
                        <div class="flex flex-col md:flex-row gap-8 items-start">
                            <!-- Company Branding -->
                            <div class="w-full md:w-1/4 flex flex-col items-center md:items-start text-center md:text-left">
                                <div class="w-20 h-20 rounded-2xl bg-white dark:bg-white/5 border border-purple-500/20 p-2 shadow-xl mb-4 group-hover:scale-110 transition-transform duration-500">
                                    <img v-if="exp.image_url" :src="exp.image_url" :alt="exp.company" class="w-full h-full object-contain" />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-purple-500/10 text-purple-500 font-bold text-2xl uppercase">
                                        {{ exp.company.charAt(0) }}
                                    </div>
                                </div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white group-hover:text-purple-500 transition-colors">{{ exp.company }}</h3>
                                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ exp.location }}
                                </div>
                                <!-- Company Skills -->
                                <div v-if="exp.skills && exp.skills.length > 0" class="flex flex-wrap gap-1.5 mt-6 justify-center md:justify-start">
                                    <span v-for="skill in exp.skills" :key="skill.id" class="px-2 py-0.5 bg-purple-500/10 text-purple-600 dark:text-purple-400 text-[9px] font-black uppercase tracking-tight rounded-md border border-purple-500/10">
                                        {{ skill.name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Roles Timeline -->
                            <div class="flex-1 space-y-8 relative">
                                <div class="absolute left-0 md:left-[-2rem] top-0 bottom-0 w-px bg-gradient-to-b from-purple-500/50 via-purple-500/10 to-transparent hidden md:block"></div>
                                
                                <div v-for="role in exp.roles" :key="role.id" class="relative pl-0 md:pl-8">
                                    <!-- Timeline Dot -->
                                    <div class="absolute left-[-2.25rem] top-2 w-2 h-2 rounded-full bg-purple-500 shadow-[0_0_10px_rgba(168,85,247,0.5)] hidden md:block group-hover:scale-150 transition-transform"></div>
                                    
                                    <div class="bg-white/40 dark:bg-white/5 backdrop-blur-xl p-6 rounded-3xl border border-white/20 dark:border-white/10 hover:border-purple-500/40 transition-all duration-300">
                                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-4">
                                            <div>
                                                <h4 class="text-lg font-black text-gray-800 dark:text-gray-100 group-hover:translate-x-1 transition-transform">{{ role.role }}</h4>
                                                <span class="inline-block px-3 py-1 bg-purple-500/10 text-purple-600 dark:text-purple-400 text-[10px] font-black uppercase tracking-widest rounded-full mt-1">
                                                    {{ role.employment_type }}
                                                </span>
                                            </div>
                                            <div class="text-[11px] font-black font-mono text-gray-500 dark:text-gray-400 bg-gray-100/50 dark:bg-black/20 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-white/5">
                                                {{ new Date(role.start_date).toLocaleDateString('pt-PT', { month: 'short', year: 'numeric' }) }} — 
                                                <span v-if="role.is_current" class="text-emerald-500">{{ __('Present') }}</span>
                                                <span v-else>{{ new Date(role.end_date).toLocaleDateString('pt-PT', { month: 'short', year: 'numeric' }) }}</span>
                                            </div>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed whitespace-pre-wrap">{{ role.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Education Section -->
        <section id="education" class="py-24 px-4 scroll-mt-24">
            <div class="max-w-7xl mx-auto">
                <div class="mb-16 text-right">
                    <h2 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                        {{ __('Academic') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-500 to-blue-500">{{ __('Background') }}</span>
                    </h2>
                    <div class="w-24 h-2 bg-cyan-500 mt-4 rounded-full ml-auto"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="edu in educations" :key="edu.id" class="group h-full">
                        <div class="h-full bg-white/40 dark:bg-white/5 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/20 dark:border-white/10 hover:border-cyan-500/40 transition-all duration-500 flex flex-col relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                </svg>
                            </div>

                            <div class="flex items-center gap-2 text-xs font-black text-cyan-500 uppercase tracking-widest mb-4">
                                <span class="px-2 py-1 bg-cyan-500/10 rounded-lg">{{ edu.type }}</span>
                                <span class="w-4 h-px bg-cyan-500/30"></span>
                                <span class="text-gray-500">{{ new Date(edu.start_date || edu.end_date).getFullYear() }}</span>
                            </div>

                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2 leading-tight group-hover:text-cyan-500 transition-colors">{{ edu.degree }}</h3>
                            <p class="text-lg font-bold text-gray-600 dark:text-gray-300 mb-6">{{ edu.institution }}</p>
                            
                            <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed mb-6 flex-1 border-l-2 border-cyan-500/20 pl-4">{{ edu.description }}</p>

                            <!-- Education Skills -->
                            <div v-if="edu.skills && edu.skills.length > 0" class="flex flex-wrap gap-1.5 mb-6">
                                <span v-for="skill in edu.skills" :key="skill.id" class="px-2 py-0.5 bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 text-[9px] font-black uppercase tracking-tight rounded-md border border-cyan-500/10">
                                    {{ skill.name }}
                                </span>
                            </div>

                            <a v-if="edu.url" :href="edu.url" target="_blank" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-cyan-600 dark:text-cyan-400 hover:gap-4 transition-all group/link">
                                {{ __('Official Document') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover/link:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Projects Section -->
        <section id="projects" class="py-24 px-4 scroll-mt-24 bg-gray-50/50 dark:bg-black/20">
            <div class="max-w-7xl mx-auto">
                <div class="mb-16">
                    <h2 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                        {{ __('Featured') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-amber-500">{{ __('Projects') }}</span>
                    </h2>
                    <div class="w-24 h-2 bg-pink-500 mt-4 rounded-full"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="project in projects" :key="project.id" class="group h-full flex flex-col">
                        <div class="flex-1 bg-white/60 dark:bg-white/5 backdrop-blur-xl rounded-[2rem] border border-white/20 dark:border-white/10 hover:border-pink-500/30 transition-all duration-500 overflow-hidden flex flex-col shadow-xl hover:shadow-pink-500/10">
                            <!-- Project Image -->
                            <div class="relative aspect-video overflow-hidden">
                                <img v-if="project.image_url" :src="project.image_url" :alt="project.title" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                                <div v-else class="w-full h-full bg-gradient-to-br from-pink-500/10 to-amber-500/10 flex items-center justify-center text-pink-500/20 text-4xl font-black uppercase">
                                    {{ project.title.substring(0, 2) }}
                                </div>
                                <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-white/90 dark:from-black/80 to-transparent"></div>
                                
                                <div v-if="project.in_progress" class="absolute top-4 right-4 px-3 py-1 bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest rounded-full shadow-lg">
                                    {{ __('In Progress') }}
                                </div>
                            </div>

                            <!-- Project Info -->
                            <div class="p-8 flex-1 flex flex-col relative z-10">
                                <h3 class="text-2xl font-black text-gray-800 dark:text-white mb-3 group-hover:text-pink-500 transition-colors">{{ project.title }}</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed mb-6 line-clamp-3 group-hover:line-clamp-none transition-all duration-300">{{ project.description }}</p>

                                <!-- Skills/Tags -->
                                <div class="flex flex-wrap gap-2 mb-8">
                                    <span v-for="skill in project.skills" :key="skill.id" class="px-3 py-1 bg-pink-500/5 text-pink-600 dark:text-pink-400 text-[10px] font-black uppercase tracking-widest rounded-lg border border-pink-500/10">
                                        {{ skill.name }}
                                    </span>
                                </div>

                                <!-- Links -->
                                <div class="mt-auto flex items-center justify-between border-t border-gray-100 dark:border-white/5 pt-6">
                                    <a v-if="project.project_url" :href="project.project_url" target="_blank" class="px-6 py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-lg hover:shadow-pink-500/20">
                                        {{ __('View Live') }}
                                    </a>
                                    <a v-if="project.github_url" :href="project.github_url" target="_blank" class="flex items-center gap-2 text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors group/gh">
                                        <i class="fab fa-github text-xl"></i>
                                        <span class="text-[10px] font-black uppercase tracking-widest hidden sm:block">GitHub</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Interactive Terminal Section -->
        <section id="terminal" class="py-24 px-4 scroll-mt-24 bg-gray-50/50 dark:bg-gray-950/20">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row gap-12 items-center">
                    <div class="md:w-1/3 space-y-4">
                        <div class="flex items-center gap-2">
                             <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                             <span class="text-[10px] font-black tracking-widest text-emerald-500 uppercase">{{ __('Interactive Override') }}</span>
                        </div>
                        <h2 class="text-4xl lg:text-5xl font-black text-gray-900 dark:text-white tracking-tighter uppercase whitespace-pre-wrap">{{ __('System') }}<br/><span class="text-emerald-500">{{ __('Access') }}</span></h2>
                        <p class="text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                            {{ __("Interact directly with the system core via this terminal. Type 'help' to begin exploration.") }}
                        </p>
                    </div>
                    <div class="md:w-2/3 w-full">
                         <CyberTerminal :hero="hero" :projects="projects" :skills="skills" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="scroll-mt-24">
            <ContactSection :email="contact_email" :phone="contact_phone" :address="contact_address" />
        </section>

        <footer class="py-10 text-center text-gray-500 dark:text-gray-400 text-sm font-medium border-t border-white/10">
            &copy; {{ new Date().getFullYear() }} {{ hero.name }}. <span class="opacity-50" v-if="footer_text">{{ footer_text }}</span>
        </footer>
    </CyberLayout>
</template>
