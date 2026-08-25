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
    sections: Array,
    socials: [Object, Array],
    canLogin: Boolean,
    seo: Object,
    footer_text: String,
    contact_email: String,
    contact_phone: String,
    contact_address: String,
    resume_url: String,
    locale: {
        type: String,
        default: 'en',
    },
});

const supportedLocales = ['en', 'pt', 'nl'];
const defaultLocale = 'en';
const ogLocaleMap = {
    en: 'en_US',
    pt: 'pt_PT',
    nl: 'nl_NL',
};

const pageUrl = computed(() => {
    if (typeof window === 'undefined') {
        return '/';
    }

    return `${window.location.origin}${window.location.pathname}`;
});

const currentLocale = computed(() => {
    return supportedLocales.includes(props.locale) ? props.locale : defaultLocale;
});

const canonicalUrl = computed(() => {
    if (currentLocale.value === defaultLocale) {
        return pageUrl.value;
    }

    return `${pageUrl.value}?lang=${currentLocale.value}`;
});

const alternateLanguageUrls = computed(() => {
    return supportedLocales.map((locale) => ({
        locale,
        url: locale === defaultLocale ? pageUrl.value : `${pageUrl.value}?lang=${locale}`,
    }));
});

const metaTitle = computed(() => {
    if (props.seo?.title) {
        return props.seo.title;
    }

    return [props.hero?.name, props.hero?.title].filter(Boolean).join(' | ') || 'Portfolio';
});

const metaDescription = computed(() => {
    return props.seo?.description || props.hero?.bio || 'Portfolio website with projects, skills and professional experience.';
});

const metaKeywords = computed(() => {
    return props.seo?.keywords || 'portfolio, developer, web developer';
});

const ogImageUrl = computed(() => {
    const imageUrl = props.hero?.image || '/images/Logotipo.png';

    if (typeof window === 'undefined') {
        return imageUrl;
    }

    try {
        return new URL(imageUrl, window.location.origin).toString();
    } catch {
        return imageUrl;
    }
});

const ogLocale = computed(() => {
    return ogLocaleMap[currentLocale.value] || 'en_US';
});

const ogAlternateLocales = computed(() => {
    return supportedLocales
        .filter((locale) => locale !== currentLocale.value)
        .map((locale) => ogLocaleMap[locale] || 'en_US');
});

const twitterHandle = computed(() => {
    const twitterUrl = props.socials?.Twitter;

    if (!twitterUrl) {
        return null;
    }

    const normalized = twitterUrl.match(/(?:twitter\.com|x\.com)\/([A-Za-z0-9_]+)/i);

    return normalized?.[1] ? `@${normalized[1]}` : null;
});

const structuredData = computed(() => {
    return JSON.stringify({
        '@context': 'https://schema.org',
        '@graph': [
            {
                '@type': 'WebSite',
                name: props.hero?.name || metaTitle.value,
                url: pageUrl.value,
                inLanguage: currentLocale.value,
            },
            {
                '@type': 'Person',
                name: props.hero?.name || 'Portfolio Owner',
                jobTitle: props.hero?.title || undefined,
                description: metaDescription.value,
                url: canonicalUrl.value,
                image: ogImageUrl.value,
                sameAs: Object.values(props.socials || {}).filter(Boolean),
            },
            {
                '@type': 'CollectionPage',
                name: metaTitle.value,
                description: metaDescription.value,
                url: canonicalUrl.value,
                inLanguage: currentLocale.value,
            },
        ],
    });
});

const activeSection = ref('about');
const defaultNavList = ['about', 'interests', 'skills', 'timeline', 'terminal', 'contact'];
const validNavItems = ['about', 'interests', 'skills', 'timeline', 'experience', 'education', 'projects', 'terminal', 'contact'];
const allSectionsList = ['about', 'interests', 'skills', 'timeline', 'timeline-mobile', 'experience', 'education', 'projects', 'terminal', 'contact'];

const sortedDbSections = computed(() => {
    if (!props.sections || props.sections.length === 0) {
        return [];
    }
    return [...props.sections].sort((a, b) => a.sort_order - b.sort_order);
});

const isSectionVisible = (sectionName) => {
    if (!props.sections || props.sections.length === 0) return true;
    const mappedSearchName = (name) => {
        if (name === 'about') return ['hero', 'about'];
        if (name === 'timeline' || name === 'timeline-mobile') return ['timeline', 'experience', 'education', 'projects'];
        if (['experience', 'education', 'projects'].includes(name)) return [name, 'timeline'];
        return [name];
    };
    const targetNames = mappedSearchName(sectionName);
    const sec = props.sections.find(s => targetNames.includes(s.name));
    return sec ? sec.is_visible : true;
};

const getSectionSortOrder = (sectionName) => {
    if (!props.sections || props.sections.length === 0) return 0;

    let targetNames = [sectionName];
    if (sectionName === 'about') {
        targetNames = ['about', 'hero'];
    } else if (sectionName === 'timeline' || sectionName === 'timeline-mobile') {
        targetNames = ['timeline', 'experience', 'education', 'projects'];
    } else if (['experience', 'education', 'projects'].includes(sectionName)) {
        targetNames = [sectionName, 'timeline'];
    }

    const index = sortedDbSections.value.findIndex(s => targetNames.includes(s.name));
    return index !== -1 ? index : 99;
};

const sectionsList = computed(() => {
    if (!props.sections || props.sections.length === 0) {
        return defaultNavList;
    }

    const mapName = (name) => (name === 'hero' ? 'about' : name);
    const result = [];

    sortedDbSections.value.forEach(s => {
        if (s.is_visible) {
            const mapped = mapName(s.name);
            if (validNavItems.includes(mapped) && !result.includes(mapped)) {
                result.push(mapped);
            }
        }
    });

    defaultNavList.forEach(item => {
        if (!result.includes(item) && isSectionVisible(item)) {
            result.push(item);
        }
    });

    return result;
});
const selectedProject = ref(null);
const showProjectModal = ref(false);
const activeImageIndex = ref(0);

const openProjectModal = (project) => {
    selectedProject.value = project;
    activeImageIndex.value = 0;
    showProjectModal.value = true;
    document.body.style.overflow = 'hidden';
};

const closeProjectModal = () => {
    showProjectModal.value = false;
    document.body.style.overflow = '';
};

const activeImageUrl = computed(() => {
    if (!selectedProject.value) return null;
    if (selectedProject.value.gallery && selectedProject.value.gallery.length > 0) {
        return selectedProject.value.gallery[activeImageIndex.value].url;
    }
    return selectedProject.value.main_image_url;
});

const nextImage = () => {
    if (selectedProject.value?.gallery?.length > 0) {
        activeImageIndex.value = (activeImageIndex.value + 1) % selectedProject.value.gallery.length;
    }
};

const prevImage = () => {
    if (selectedProject.value?.gallery?.length > 0) {
        activeImageIndex.value = (activeImageIndex.value - 1 + selectedProject.value.gallery.length) % selectedProject.value.gallery.length;
    }
};
const navButtons = ref([]);
const indicatorLeft = ref(0);
const indicatorWidth = ref(0);
const scrollProgress = ref(0);
let observer = null;
let engagementObserver = null;
let engagementTickInterval = null;
let engagementFlushInterval = null;
const sectionVisibility = ref({});
const sectionDurationsMs = ref({});
const lastEngagementTickAt = ref(Date.now());

const isDragging = ref(false);

const activeColorClass = computed(() => {
    switch (activeSection.value) {
        case 'about': return 'from-purple-600 to-indigo-600 shadow-purple-500/50';
        case 'interests': return 'from-pink-500 to-purple-500 shadow-pink-500/50';
        case 'skills': return 'from-cyan-500 to-blue-500 shadow-cyan-500/50';
        case 'timeline': return 'from-pink-500 to-rose-500 shadow-pink-500/50';
        case 'experience': return 'from-purple-500 to-violet-600 shadow-purple-500/50';
        case 'education': return 'from-cyan-500 to-blue-600 shadow-cyan-500/50';
        case 'projects': return 'from-pink-500 to-amber-500 shadow-pink-500/50';
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
        case 'projects': return 'text-pink-600 dark:text-pink-400';
        case 'terminal': return 'text-emerald-600 dark:text-emerald-400';
        case 'contact': return 'text-orange-600 dark:text-orange-400';
        default: return 'text-purple-600 dark:text-purple-400';
    }
});

const timelineItems = computed(() => {
    const items = [];

    // Add projects
    props.projects.forEach(p => {
        const endDate = p.completed_at ? new Date(p.completed_at) : null;
        items.push({
            id: `project-${p.id}`,
            type: 'project',
            date: new Date(p.completed_at || p.created_at),
            displayDate: endDate,
            title: p.title,
            description: p.description,
            image: p.main_image_url,
            tags: p.skills,
            project_url: p.project_url,
            github_url: p.github_url,
            in_progress: p.in_progress,
            is_current: !!p.in_progress
        });
    });

    // Add experiences
    props.experiences.forEach(exp => {
        exp.roles.forEach((role, idx) => {
            const endDate = role.is_current ? null : new Date(role.end_date);
            items.push({
                id: `exp-${exp.id}-${idx}`,
                type: 'experience',
                date: new Date(role.start_date),
                displayDate: endDate,
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
        const endDate = edu.is_current ? null : (edu.end_date ? new Date(edu.end_date) : null);
        items.push({
            id: `edu-${edu.id}`,
            type: 'education',
            date: new Date(edu.end_date || edu.start_date),
            displayDate: endDate,
            title: edu.degree || edu.institution,
            subtitle: edu.degree ? edu.institution : '',
            description: edu.description,
            eduType: edu.type,
            image: edu.image_url,
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
    const activeIndex = sectionsList.value.indexOf(activeSection.value);
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

const normalizeTrackedSection = (id) => id === 'timeline-mobile' ? 'timeline' : id;

const getTopVisibleSection = () => {
    const entries = Object.entries(sectionVisibility.value)
        .filter(([, ratio]) => ratio >= 0.2)
        .sort((a, b) => b[1] - a[1]);

    if (entries.length > 0) {
        return normalizeTrackedSection(entries[0][0]);
    }

    return normalizeTrackedSection(activeSection.value);
};

const registerEngagementSlice = () => {
    const now = Date.now();

    if (document.hidden) {
        lastEngagementTickAt.value = now;
        return;
    }

    const elapsed = Math.max(0, now - lastEngagementTickAt.value);
    const section = getTopVisibleSection();

    if (section && elapsed > 0) {
        sectionDurationsMs.value[section] = (sectionDurationsMs.value[section] || 0) + elapsed;
    }

    lastEngagementTickAt.value = now;
};

const buildEngagementPayload = () => {
    return Object.entries(sectionDurationsMs.value)
        .filter(([, milliseconds]) => milliseconds >= 1000)
        .map(([section, milliseconds]) => ({
            section,
            duration_seconds: Math.round(milliseconds / 1000),
            path: window.location.pathname === '/' ? 'home' : window.location.pathname.replace(/^\//, ''),
        }));
};

const flushSectionEngagement = (useBeacon = false) => {
    const entries = buildEngagementPayload();

    if (entries.length === 0) {
        return;
    }

    sectionDurationsMs.value = {};

    const payload = JSON.stringify({ entries });
    const endpoint = route('analytics.section-engagement.track');

    if (useBeacon && navigator.sendBeacon) {
        const blob = new Blob([payload], { type: 'application/json' });
        navigator.sendBeacon(endpoint, blob);
        return;
    }

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: payload,
        keepalive: true,
    }).catch(() => {
        // Ignore telemetry transport failures.
    });
};

const handleVisibilityChange = () => {
    if (document.hidden) {
        registerEngagementSlice();
        flushSectionEngagement(true);
    } else {
        lastEngagementTickAt.value = Date.now();
    }
};

const handlePageHide = () => {
    registerEngagementSlice();
    flushSectionEngagement(true);
};

onMounted(() => {
    lastEngagementTickAt.value = Date.now();

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
                    if (sectionsList.value.includes(id)) {
                        activeSection.value = id;
                    } else if (['experience', 'education', 'projects'].includes(id) && sectionsList.value.includes('timeline')) {
                        activeSection.value = 'timeline';
                    } else if (id === 'timeline-mobile' && sectionsList.value.includes('timeline')) {
                        activeSection.value = 'timeline';
                    } else {
                        activeSection.value = id;
                    }
                }
            });
        },
        { rootMargin: '-30% 0px -70% 0px' }
    );

    engagementObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                sectionVisibility.value[entry.target.id] = entry.intersectionRatio;
            });
        },
        {
            threshold: [0, 0.2, 0.4, 0.6, 0.8, 1],
        }
    );

    allSectionsList.forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            observer.observe(el);
            engagementObserver.observe(el);
        }
    });

    window.addEventListener('scroll', updateScrollProgress);
    window.addEventListener('scroll', handleTimelineScroll);
    window.addEventListener('resize', updateIndicator);
    window.addEventListener('resize', handleTimelineScroll);
    window.addEventListener('pagehide', handlePageHide);
    window.addEventListener('beforeunload', handlePageHide);
    document.addEventListener('visibilitychange', handleVisibilityChange);

    engagementTickInterval = window.setInterval(() => {
        registerEngagementSlice();
    }, 3000);

    engagementFlushInterval = window.setInterval(() => {
        registerEngagementSlice();
        flushSectionEngagement();
    }, 15000);
    
    setTimeout(() => {
        updateIndicator();
        updateScrollProgress();
        handleTimelineScroll();
    }, 100);
});

onUnmounted(() => {
    if (observer) observer.disconnect();
    if (engagementObserver) engagementObserver.disconnect();

    registerEngagementSlice();
    flushSectionEngagement(true);

    if (engagementTickInterval) {
        clearInterval(engagementTickInterval);
    }

    if (engagementFlushInterval) {
        clearInterval(engagementFlushInterval);
    }

    window.removeEventListener('scroll', updateScrollProgress);
    window.removeEventListener('scroll', handleTimelineScroll);
    window.removeEventListener('resize', updateIndicator);
    window.removeEventListener('resize', handleTimelineScroll);
    window.removeEventListener('pagehide', handlePageHide);
    window.removeEventListener('beforeunload', handlePageHide);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
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

const handleTimelineClick = (item) => {
    if (item.type === 'project') {
        const project = props.projects.find(p => `project-${p.id}` === item.id);
        if (project) openProjectModal(project);
    } else if (item.type === 'experience') {
        scrollTo('experience');
    } else if (item.type === 'education') {
        scrollTo('education');
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
    transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), filter 0.8s ease;
    filter: blur(4px);
}

.timeline-item.is-visible {
    opacity: 1;
    filter: blur(0);
}

/* Timeline Card Base */
.timeline-card {
    background: rgba(8, 12, 28, 0.75);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.07);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.4);
    transition: all 0.45s cubic-bezier(0.23, 1, 0.32, 1);
}

.timeline-card:hover {
    transform: translateY(-8px);
    background: rgba(14, 20, 45, 0.85);
}

/* Experience Card - Purple accent */
.timeline-card--experience:hover {
    box-shadow: 0 20px 50px -10px rgba(147, 51, 234, 0.3), 0 8px 32px 0 rgba(0, 0, 0, 0.5);
    border-color: rgba(147, 51, 234, 0.2);
}

/* Education Card - Cyan accent */
.timeline-card--education:hover {
    box-shadow: 0 20px 50px -10px rgba(6, 182, 212, 0.3), 0 8px 32px 0 rgba(0, 0, 0, 0.5);
    border-color: rgba(6, 182, 212, 0.2);
}

/* Project Card - Pink accent */
.timeline-card--project:hover {
    box-shadow: 0 20px 50px -10px rgba(236, 72, 153, 0.3), 0 8px 32px 0 rgba(0, 0, 0, 0.5);
    border-color: rgba(236, 72, 153, 0.2);
}
</style>

<template>
    <CyberLayout>
        <Head :title="metaTitle">
            <meta name="description" :content="metaDescription" />
            <meta name="keywords" :content="metaKeywords" />
            <meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1" />
            <link rel="canonical" :href="canonicalUrl" />
            <link rel="alternate" hreflang="x-default" :href="pageUrl" />
            <link
                v-for="alternate in alternateLanguageUrls"
                :key="alternate.locale"
                rel="alternate"
                :hreflang="alternate.locale"
                :href="alternate.url"
            />

            <meta property="og:type" content="website" />
            <meta property="og:site_name" :content="hero?.name || 'Portfolio'" />
            <meta property="og:title" :content="metaTitle" />
            <meta property="og:description" :content="metaDescription" />
            <meta property="og:url" :content="canonicalUrl" />
            <meta property="og:image" :content="ogImageUrl" />
            <meta property="og:locale" :content="ogLocale" />
            <meta
                v-for="alternateLocale in ogAlternateLocales"
                :key="alternateLocale"
                property="og:locale:alternate"
                :content="alternateLocale"
            />

            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" :content="metaTitle" />
            <meta name="twitter:description" :content="metaDescription" />
            <meta name="twitter:image" :content="ogImageUrl" />
            <meta name="twitter:creator" :content="twitterHandle" v-if="twitterHandle" />

            <script type="application/ld+json" v-html="structuredData"></script>
        </Head>
        
        <header class="fixed top-0 w-full z-50 bg-white/70 dark:bg-[#030712]/70 backdrop-blur-2xl transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-24 gap-4 xl:gap-8">
                    <div class="flex items-center gap-5 cursor-pointer group" @click="scrollTo('about')">
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
                                    <div :key="activeSection" class="flex items-center gap-3 whitespace-nowrap bg-gray-100/80 dark:bg-white/5 py-1 px-3 rounded-lg border border-gray-200 dark:border-white/10 shadow-sm">
                                        <span class="text-[10px] font-bold tracking-[0.1em] uppercase text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                            <span class="text-[11px] font-black opacity-60">//</span> LOC: 
                                            <span class="text-gray-900 dark:text-white font-black tracking-widest">{{ __(activeSection) }}</span>
                                        </span>
                                        <div class="h-3 w-px bg-gray-300 dark:bg-white/20 hidden xl:block"></div>
                                        <span class="text-[10px] font-mono text-gray-500 dark:text-gray-400 hidden xl:flex items-center gap-1.5">
                                            REG: <span class="text-gray-700 dark:text-gray-300 font-semibold">{{ new Intl.DateTimeFormat().resolvedOptions().timeZone.split('/').pop().replace('_', ' ') }}</span>
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

        <main class="flex flex-col">
        <!-- Hero Section -->
        <section id="about" class="pt-32 pb-20 px-4 min-h-screen flex items-center" :style="{ order: getSectionSortOrder('about') }" v-if="isSectionVisible('about')">
            <div class="max-w-7xl mx-auto flex flex-col-reverse lg:flex-row items-center gap-12">
                <div class="lg:w-1/2 space-y-6">
                    <h2 class="text-sm font-bold text-cyan-600 dark:text-cyan-400 tracking-[0.2em] uppercase animate-pulse">
                        {{ hero.title || __('Full Stack Developer') }}
                    </h2>
                    <h1 class="text-5xl lg:text-7xl font-black tracking-tight text-gray-900 dark:text-white">
                        {{ __('Hi, I\'m') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 via-pink-600 to-cyan-600">{{ hero.name }}</span>
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed max-w-lg font-medium">
                        {{ hero.bio }}
                    </p>
                    <div class="pt-4 flex gap-4">
                        <a v-for="(link, platform) in socials" :key="platform" :href="link" target="_blank" class="px-6 py-3 bg-gray-900 dark:bg-white/10 dark:hover:bg-white/20 backdrop-blur-md border border-white/10 text-white dark:text-white rounded-xl font-bold uppercase tracking-wide hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-purple-500/30">
                            {{ __(platform) }}
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
                                :alt="`Foto de perfil de ${hero.name || 'portfolio owner'}`"
                                class="w-full h-full object-cover"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Interests Section -->
        <section id="interests" class="py-20 px-4" :style="{ order: getSectionSortOrder('interests') }" v-if="isSectionVisible('interests') && interests && Object.keys(interests).length > 0">
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
        <section id="skills" class="py-20 px-4 scroll-mt-24" :style="{ order: getSectionSortOrder('skills') }" v-if="isSectionVisible('skills')">
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
        <section id="timeline" ref="timelineContainer" class="relative hidden md:block" :style="{ height: (timelineItems.length * 45) + 'vh', order: getSectionSortOrder('timeline') }" v-if="isSectionVisible('timeline')">
            <div class="sticky top-0 h-screen flex flex-col overflow-hidden" style="background: linear-gradient(135deg, #060a18 0%, #0a0f22 50%, #06081a 100%)">

                <!-- Subtle grid background -->
                <div class="absolute inset-0 pointer-events-none" style="background-image: linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px); background-size: 60px 60px;"></div>

                <!-- Corner glow accents -->
                <div class="absolute top-0 left-0 w-96 h-96 bg-purple-900/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-cyan-900/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Title block — top portion, centered with significant breathing room -->
                <div class="flex flex-col items-center justify-center pt-40 pb-12 relative z-10 flex-shrink-0">
                    <div class="flex items-center gap-6 mb-4">
                        <div class="w-12 h-[2px] bg-gradient-to-r from-pink-500 to-purple-500"></div>
                        <span class="text-[11px] font-black uppercase tracking-[0.6em] text-pink-500">{{ __('Timeline') }}</span>
                        <div class="w-12 h-[2px] bg-gradient-to-r from-purple-500 to-pink-500"></div>
                    </div>
                    <h2 class="text-7xl md:text-8xl lg:text-[130px] font-black text-white uppercase tracking-tighter leading-none text-center drop-shadow-2xl">
                        <span class="opacity-90">{{ __('My') }}</span> <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-400 to-cyan-400">{{ __('Journey') }}</span>
                    </h2>
                    <p class="text-gray-600 text-[11px] font-bold tracking-[0.4em] uppercase mt-6">{{ __('Scroll to explore the legacy') }} →</p>
                </div>

                <!-- Cards row — flex-1 so it fills remaining space, cards centered vertically -->
                <div class="flex-1 relative flex items-center">
                    <div class="relative flex items-center w-full transition-transform duration-200 ease-out" ref="horizontalTarget" :style="{ transform: `translateX(-${translateX}px)` }">
                    <!-- The Interactive Growing Horizontal Line -->
                    <div class="absolute top-1/2 left-0 w-full h-px bg-white/5">
                        <div class="h-full bg-gradient-to-r from-purple-500 via-pink-500 to-cyan-400 timeline-line-grow"></div>
                    </div>

                    <!-- Timeline Loop -->
                    <div v-for="(item, index) in timelineItems" :key="item.id" 
                         :ref="el => itemRefs[index] = el"
                         :data-id="item.id"
                         class="timeline-item flex-shrink-0 w-[320px] mx-8 relative cursor-pointer transition-[opacity,filter] duration-700"
                         :class="visibleItems.has(item.id) ? 'is-visible' : ''"
                         :style="{ transform: `translateY(${index % 2 === 0 ? '-90px' : '90px'})` }"
                         @click="handleTimelineClick(item)">

                        <!-- Content Card -->
                        <div class="group">
                            <div class="timeline-card rounded-2xl relative overflow-hidden transition-all duration-500 flex"
                                 :class="{
                                     'timeline-card--experience': item.type === 'experience',
                                     'timeline-card--education': item.type === 'education',
                                     'timeline-card--project': item.type === 'project'
                                 }">

                                <!-- Left colored border accent -->
                                <div class="w-1 flex-shrink-0 rounded-l-2xl transition-all duration-500 group-hover:w-1.5"
                                     :class="{
                                         'bg-gradient-to-b from-purple-400 to-violet-700': item.type === 'experience',
                                         'bg-gradient-to-b from-cyan-300 to-blue-600': item.type === 'education',
                                         'bg-gradient-to-b from-pink-400 to-rose-600': item.type === 'project'
                                     }"></div>

                                <!-- Card body -->
                                <div class="flex-1 p-7 relative overflow-hidden">

                                    <!-- Year as large background element -->
                                    <div class="absolute -bottom-3 -right-2 text-[6rem] font-black leading-none font-mono select-none pointer-events-none opacity-[0.06] group-hover:opacity-[0.1] transition-opacity duration-500"
                                         :class="{
                                             'text-purple-400': item.type === 'experience',
                                             'text-cyan-400': item.type === 'education',
                                             'text-pink-400': item.type === 'project'
                                         }">
                                        <span v-if="item.is_current">NOW</span>
                                        <span v-else-if="item.displayDate">{{ item.displayDate.getFullYear() }}</span>
                                        <span v-else>{{ item.date.getFullYear() }}</span>
                                    </div>

                                    <!-- Category badge -->
                                    <div class="flex items-center gap-2 mb-5">
                                        <!-- Icon -->
                                        <div class="w-6 h-6 flex items-center justify-center">
                                            <svg v-if="item.type === 'experience'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <svg v-else-if="item.type === 'education'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path d="M12 14l9-5-9-5-9 5 9 5z" /><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                            </svg>
                                        </div>
                                        <span class="text-[10px] font-black uppercase tracking-[0.25em]"
                                              :class="{
                                                  'text-purple-400': item.type === 'experience',
                                                  'text-cyan-400': item.type === 'education',
                                                  'text-pink-400': item.type === 'project'
                                              }">{{ __(item.type) }}</span>
                                    </div>

                                    <!-- Title — dominant element -->
                                    <h3 class="text-2xl font-black text-white leading-tight tracking-tight mb-4 group-hover:translate-x-1 transition-transform duration-300">
                                        {{ item.title }}
                                    </h3>

                                    <!-- Bottom: logo + subtitle + arrow -->
                                    <div class="flex items-center justify-between relative z-10 mt-2">
                                        <div class="flex items-center gap-2.5">
                                            <div v-if="item.image" class="w-6 h-6 rounded-md overflow-hidden bg-white/5 border border-white/10 p-0.5 flex-shrink-0">
                                                <img :src="item.image" :alt="`Imagem de ${item.title}`" class="w-full h-full object-contain" />
                                            </div>
                                            <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-widest truncate max-w-[180px]">
                                                {{ item.subtitle || item.location || '' }}
                                            </span>
                                        </div>
                                        <!-- Arrow hint -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-0 group-hover:opacity-50 transition-all duration-300 group-hover:translate-x-0.5 flex-shrink-0"
                                             :class="{
                                                 'text-purple-400': item.type === 'experience',
                                                 'text-cyan-400': item.type === 'education',
                                                 'text-pink-400': item.type === 'project'
                                             }"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date Spacer -->
                        <div class="absolute left-1/2 -translate-x-1/2 font-black select-none pointer-events-none transition-all duration-1000 whitespace-nowrap tracking-wider text-sm"
                             :class="[
                                 visibleItems.has(item.id) ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-10 scale-90',
                                 index % 2 === 0 ? 'top-[108%]' : 'bottom-[108%]'
                             ]">
                            <span v-if="item.is_current"
                                  class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"
                                  :class="{
                                      'bg-purple-500/20 text-purple-400 border border-purple-500/30': item.type === 'experience',
                                      'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30': item.type === 'education',
                                      'bg-pink-500/20 text-pink-400 border border-pink-500/30': item.type === 'project'
                                  }">
                                ● {{ __('Ongoing') }}
                            </span>
                            <span v-else class="text-white/20 text-[1.8vw] font-black tracking-tighter">
                                {{ item.displayDate ? item.displayDate.toLocaleDateString('pt-PT', { month: 'short' }).replace('.', '').toUpperCase() + ' ' + item.displayDate.getFullYear() : item.date.toLocaleDateString('pt-PT', { month: 'short' }).replace('.', '').toUpperCase() + ' ' + item.date.getFullYear() }}
                            </span>
                        </div>
                    </div><!-- end timeline-item -->
                    
                    <!-- End Spacer (forces space after last card) -->
                    <div class="flex-shrink-0 min-w-[150px] md:min-w-[200px] h-10"></div>
                    
                    </div><!-- end horizontalTarget inner div -->
                </div><!-- end flex-1 cards row -->
            </div><!-- end sticky container -->
        </section>

        <section id="timeline-mobile" class="py-24 px-4 relative overflow-hidden md:hidden scroll-mt-24" :style="{ order: getSectionSortOrder('timeline') }" v-if="isSectionVisible('timeline')">
            <div class="absolute inset-0 bg-gradient-to-b from-gray-50/50 to-white dark:from-[#030712]/50 dark:to-[#030712] pointer-events-none"></div>
            
            <h2 class="text-5xl font-black mb-20 text-center text-gray-900 dark:text-white uppercase tracking-tighter relative z-10">
                {{ __('My') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-500 to-pink-500">{{ __('Journey') }}</span>
            </h2>

            <div class="relative max-w-lg mx-auto">
                <div class="absolute left-6 top-0 bottom-0 w-[1px] bg-gradient-to-b from-pink-500 via-purple-500 to-cyan-500 opacity-30"></div>
                
                <div v-for="item in timelineItems" :key="item.id" class="pl-14 mb-12 relative group cursor-pointer" @click="handleTimelineClick(item)">
                    <!-- Timeline Node -->
                    <div class="absolute left-[19px] top-2 w-3.5 h-3.5 rounded-full ring-4 ring-white dark:ring-[#030712] transition-all duration-500 z-10"
                         :class="{
                             'bg-purple-600 shadow-[0_0_15px_rgba(147,51,234,0.5)]': item.type === 'experience',
                             'bg-cyan-500 shadow-[0_0_15px_rgba(6,182,212,0.5)]': item.type === 'education',
                             'bg-pink-600 shadow-[0_0_15px_rgba(219,39,119,0.5)]': item.type === 'project'
                         }">
                    </div>

                    <div class="timeline-card p-5 rounded-2xl relative overflow-hidden"
                         :class="{
                             'timeline-card--experience': item.type === 'experience',
                             'timeline-card--education': item.type === 'education',
                             'timeline-card--project': item.type === 'project'
                         }">
                        <!-- Top accent -->
                        <div class="absolute top-0 left-0 right-0 h-[2px]"
                             :class="{
                                 'bg-gradient-to-r from-purple-500 to-violet-600': item.type === 'experience',
                                 'bg-gradient-to-r from-cyan-400 to-blue-500': item.type === 'education',
                                 'bg-gradient-to-r from-pink-500 to-rose-500': item.type === 'project'
                             }"></div>

                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em]"
                                  :class="{
                                      'text-purple-500': item.type === 'experience',
                                      'text-cyan-500': item.type === 'education',
                                      'text-pink-500': item.type === 'project'
                                  }">
                                <template v-if="item.is_current">● {{ __('Ongoing') }}</template>
                                <template v-else>{{ item.displayDate ? item.displayDate.getFullYear() : item.date.getFullYear() }}</template>
                                · {{ __(item.type) }}
                            </span>
                            <div v-if="item.image" class="w-7 h-7 rounded-lg overflow-hidden bg-white/5 border border-white/10 p-0.5">
                                <img :src="item.image" :alt="`Imagem de ${item.title}`" class="w-full h-full object-contain" />
                            </div>
                        </div>

                        <h3 class="text-lg font-black text-gray-900 dark:text-white mb-1.5 leading-tight">{{ item.title }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase tracking-widest">
                            {{ item.subtitle || item.location || '' }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Experience Section -->
        <section id="experience" class="py-24 px-4 scroll-mt-24 bg-gray-50/30 dark:bg-white/[0.02]" :style="{ order: getSectionSortOrder('experience') }" v-if="isSectionVisible('experience')">
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
        <section id="education" class="py-24 px-4 scroll-mt-24" :style="{ order: getSectionSortOrder('education') }" v-if="isSectionVisible('education')">
            <div class="max-w-7xl mx-auto">
                <div class="mb-16 text-right">
                    <h2 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                        {{ __('Academic') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-500 to-blue-500">{{ __('Background') }}</span>
                    </h2>
                    <div class="w-24 h-2 bg-cyan-500 mt-4 rounded-full ml-auto"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="edu in educations" :key="edu.id" class="group relative h-full">
                        <!-- Education Card -->
                        <div class="h-full bg-white/40 dark:bg-white/5 backdrop-blur-3xl p-8 rounded-[2.5rem] border border-white/20 dark:border-white/10 hover:border-cyan-500/40 transition-all duration-700 flex flex-col relative overflow-hidden group-hover:shadow-[0_0_40px_-15px_rgba(6,182,212,0.15)]">
                            <!-- Branding & Header -->
                            <div class="flex items-start gap-6 mb-8">
                                <div class="w-20 h-20 bg-white rounded-2xl p-3 shadow-xl border border-gray-100 dark:border-white/10 flex-shrink-0 flex items-center justify-center transition-all duration-700 group-hover:scale-110 group-hover:rotate-1">
                                    <img v-if="edu.image_url" :src="edu.image_url" :alt="edu.institution" class="w-full h-full object-contain" />
                                    <div v-else class="text-cyan-500/10 text-3xl font-black uppercase select-none">{{ edu.institution.substring(0, 2) }}</div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-cyan-600 dark:text-cyan-400 text-xs font-black uppercase tracking-widest mb-1">{{ edu.type || 'Academic' }}</div>
                                    <h3 class="text-2xl font-black text-gray-950 dark:text-white leading-tight mb-1 truncate group-hover:text-cyan-500 transition-colors">{{ edu.degree }}</h3>
                                    <p class="text-sm font-bold text-gray-600 dark:text-gray-400 opacity-80 truncate">{{ edu.institution }}</p>
                                </div>
                                <div class="text-cyan-600 dark:text-cyan-400 text-2xl font-black tracking-tighter opacity-20 group-hover:opacity-100 transition-all duration-700 whitespace-nowrap">
                                    {{ edu.end_date ? new Date(edu.end_date).getFullYear() : (edu.is_current ? 'Present' : '') }}
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="relative pl-6 border-l-2 border-cyan-500/20 mb-8 flex-1">
                                <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed whitespace-pre-line line-clamp-4">{{ edu.description }}</p>
                            </div>

                            <div class="flex flex-col gap-6">
                                <!-- Education Skills -->
                                <div v-if="edu.skills && edu.skills.length > 0" class="flex flex-wrap gap-1.5">
                                    <span v-for="skill in edu.skills" :key="skill.id" class="px-2.5 py-1 bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 text-[9px] font-black uppercase tracking-widest rounded-lg border border-cyan-500/20">
                                        {{ skill.name }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                        {{ edu.is_current ? 'IN PROGRESS' : 'GRADUATED' }}
                                    </div>
                                    <a v-if="edu.url" :href="edu.url" target="_blank" class="group/btn text-[9px] font-black uppercase tracking-widest text-cyan-600 dark:text-cyan-400 flex items-center gap-2 hover:gap-3 transition-all">
                                        {{ __('Official Document') }}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Decorative background glow -->
                            <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-cyan-500/5 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Projects Section -->
        <section id="projects" class="py-24 px-4 scroll-mt-24 bg-gray-50/50 dark:bg-black/20" :style="{ order: getSectionSortOrder('projects') }" v-if="isSectionVisible('projects')">
            <div class="max-w-7xl mx-auto">
                <div class="mb-16">
                    <h2 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                        {{ __('Featured') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-amber-500">{{ __('Projects') }}</span>
                    </h2>
                    <div class="w-24 h-2 bg-pink-500 mt-4 rounded-full"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="project in projects" :key="project.id" @click="openProjectModal(project)" class="group h-full flex flex-col cursor-pointer">
                        <div class="flex-1 bg-white/60 dark:bg-white/5 backdrop-blur-xl rounded-[2rem] border border-white/20 dark:border-white/10 hover:border-pink-500/30 transition-all duration-500 overflow-hidden flex flex-col shadow-xl hover:shadow-pink-500/10">
                            <!-- Project Image -->
                            <div class="relative aspect-video overflow-hidden">
                                <img v-if="project.main_image_url" :src="project.main_image_url" :alt="project.title" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
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
                                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed mb-6 line-clamp-3 transition-all duration-300">{{ project.description }}</p>

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
        <section id="terminal" class="py-24 px-4 scroll-mt-24 bg-gray-50/50 dark:bg-gray-950/20" :style="{ order: getSectionSortOrder('terminal') }" v-if="isSectionVisible('terminal')">
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
        <section id="contact" class="scroll-mt-24" :style="{ order: getSectionSortOrder('contact') }" v-if="isSectionVisible('contact')">
            <ContactSection :email="contact_email" :phone="contact_phone" :address="contact_address" />
        </section>
        </main>

        <footer class="py-10 text-center text-gray-500 dark:text-gray-400 text-sm font-medium border-t border-white/10">
            &copy; {{ new Date().getFullYear() }} {{ hero.name }}. <span class="opacity-50" v-if="footer_text">{{ footer_text }}</span>
        </footer>

        <!-- Project Modal -->
        <transition name="terminal-fade">
            <div v-if="showProjectModal" class="fixed inset-0 z-[100] flex items-center justify-center p-0 md:p-6 lg:p-10 transition-all duration-700">
                <div class="absolute inset-0 bg-gray-950/98 backdrop-blur-3xl" @click="closeProjectModal"></div>
                
                <div class="relative w-full h-full max-w-[2200px] bg-black md:rounded-[3.5rem] border border-white/10 shadow-[0_0_150px_-30px_rgba(0,0,0,1)] flex flex-col lg:flex-row overflow-hidden project-modal-glow">
                    <!-- Close button -->
                    <button @click="closeProjectModal" class="absolute top-8 right-8 z-[70] w-14 h-14 rounded-full bg-white/5 text-white/50 flex items-center justify-center hover:bg-red-500 hover:text-white hover:rotate-90 transition-all duration-500 border border-white/10 backdrop-blur-xl group/close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Left: Immersive Media Section -->
                    <div class="lg:w-[70%] relative bg-black group/media flex flex-col h-full border-b lg:border-b-0 lg:border-r border-white/10 transition-all duration-500">
                        <div class="absolute inset-0 bg-gradient-to-br from-pink-500/5 to-cyan-500/5 mix-blend-overlay opacity-30 select-none pointer-events-none"></div>
                        
                        <!-- Main Viewport -->
                        <div class="flex-1 relative flex items-center justify-center overflow-hidden h-full">
                            <transition name="fade" mode="out-in">
                                <img :key="activeImageUrl" :src="activeImageUrl" :alt="selectedProject.title" class="w-full h-full object-contain p-6 md:p-12 lg:p-20 transition-all duration-1000" />
                            </transition>

                            <!-- Navigation Controls -->
                            <div v-if="selectedProject.gallery && selectedProject.gallery.length > 1" class="absolute inset-0 flex items-center justify-between px-6 lg:px-12 opacity-0 group-hover/media:opacity-100 transition-opacity duration-300">
                                <button @click="prevImage" class="w-16 h-16 rounded-3xl bg-black/60 hover:bg-pink-500 text-white backdrop-blur-2xl border border-white/10 transition-all hover:scale-110 flex items-center justify-center shadow-2xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                </button>
                                <button @click="nextImage" class="w-16 h-16 rounded-3xl bg-black/60 hover:bg-cyan-500 text-white backdrop-blur-2xl border border-white/10 transition-all hover:scale-110 flex items-center justify-center shadow-2xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Gallery Thumbnails (Floating Overlay) -->
                        <div v-if="selectedProject.gallery && selectedProject.gallery.length > 1" class="absolute bottom-12 left-1/2 -translate-x-1/2 z-20 px-8 py-4 bg-black/40 backdrop-blur-3xl border border-white/10 rounded-[2.5rem] shadow-2xl lg:opacity-0 lg:group-hover/media:opacity-100 transition-all duration-500 lg:translate-y-4 lg:group-hover/media:translate-y-0">
                            <div class="flex items-center gap-4 overflow-x-auto scrollbar-none max-w-[80vw]">
                                <button v-for="(img, idx) in selectedProject.gallery" :key="idx" 
                                    @click="activeImageIndex = idx"
                                    class="w-24 h-16 flex-shrink-0 rounded-2xl overflow-hidden border-2 transition-all duration-500 transform hover:scale-110"
                                    :class="activeImageIndex === idx ? 'border-pink-500 shadow-lg shadow-pink-500/40 opacity-100' : 'border-white/5 opacity-40 hover:opacity-80 hover:border-white/20'">
                                    <img :src="img.url" :alt="`Miniatura ${idx + 1} do projeto ${selectedProject.title}`" class="w-full h-full object-cover" />
                                </button>
                            </div>
                        </div>

                        <!-- Decorative identifiers -->
                        <div class="absolute top-10 left-10 pointer-events-none flex flex-col gap-2">
                            <div class="w-20 h-px bg-pink-500"></div>
                            <div class="w-10 h-px bg-pink-500/50"></div>
                        </div>
                    </div>

                    <!-- Right: Info Panel -->
                    <div class="lg:w-[30%] flex flex-col relative bg-gradient-to-b from-white/[0.04] to-transparent border-t lg:border-t-0 border-white/10 overflow-y-auto">
                        <div class="p-10 lg:p-16 flex flex-col min-h-full">
                            <div class="mb-14">
                                <div class="flex items-center gap-4 mb-8">
                                    <span class="px-5 py-2 bg-pink-500/10 text-pink-500 text-[11px] font-black uppercase tracking-[0.4em] border border-pink-500/20 rounded-xl">
                                        {{ __('Project') }} — {{ selectedProject.id }}
                                    </span>
                                    <div v-if="selectedProject.in_progress" class="px-5 py-2 bg-amber-500/10 text-amber-500 border border-amber-500/20 text-[11px] font-black uppercase tracking-[0.3em] rounded-xl">
                                        DEV_MODE
                                    </div>
                                </div>
                                <h2 class="text-5xl lg:text-7xl font-black text-white leading-[0.9] mb-10 tracking-tighter">{{ selectedProject.title }}</h2>
                                
                                <div class="flex flex-wrap gap-3">
                                    <span v-for="skill in selectedProject.skills" :key="skill.id" class="px-4 py-2 bg-white/5 text-gray-400 text-[11px] font-black uppercase tracking-widest rounded-xl border border-white/10">
                                        #{{ skill.name }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-12 mb-16 flex-1">
                                <div class="text-gray-400 text-lg md:text-xl leading-relaxed font-medium">
                                    <div class="whitespace-pre-line border-l-4 border-pink-500/30 pl-10 leading-loose tracking-wide">{{ selectedProject.description }}</div>
                                </div>
                            </div>

                            <!-- Final Actions -->
                            <div class="flex flex-col gap-6 pt-12 border-t border-white/10 mt-auto">
                                <a v-if="selectedProject.project_url" :href="selectedProject.project_url" target="_blank" class="group/btn relative px-10 py-6 bg-white text-black rounded-[2rem] text-center font-black uppercase tracking-[0.2em] text-xs overflow-hidden transition-all duration-500 hover:shadow-[0_0_60px_rgba(255,255,255,0.3)] hover:-translate-y-1">
                                    <span class="relative z-10 flex items-center justify-center gap-3">
                                        {{ __('Access Live Application') }}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover/btn:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </span>
                                </a>
                                <a v-if="selectedProject.github_url" :href="selectedProject.github_url" target="_blank" class="flex items-center justify-center gap-4 px-10 py-6 bg-white/5 hover:bg-white/10 text-white rounded-[2rem] font-black uppercase tracking-[0.2em] text-xs border border-white/10 transition-all hover:-translate-y-1">
                                    <i class="fab fa-github text-2xl"></i>
                                    View Repository
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </CyberLayout>
</template>

<style scoped>
.project-modal-glow {
    box-shadow: 0 0 100px -30px rgba(236, 72, 153, 0.1), 0 0 120px -40px rgba(6, 182, 212, 0.05);
}
.border-tl-2 {
    border-top: 3px solid;
    border-left: 3px solid;
    border-image: linear-gradient(to right bottom, #ec4899, #06b6d4) 1;
    clip-path: polygon(0 0, 100% 0, 100% 3px, 3px 3px, 3px 100%, 0 100%);
}
.fade-enter-active,
.fade-leave-active {
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    filter: blur(20px);
    transform: scale(0.95);
}
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
