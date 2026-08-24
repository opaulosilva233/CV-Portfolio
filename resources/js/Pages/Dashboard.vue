<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

defineProps({
    stats: Object,
    recentActivity: Array,
});

const defaultAnalyticsData = () => ({
    total_views: 0,
    unique_visitors: 0,
    avg_session_duration_seconds: 0,
    most_engaged_section: null,
});

const analyticsData = ref(defaultAnalyticsData());
const loadingAnalytics = ref(true);

const fetchAnalytics = async () => {
    try {
        const response = await axios.get(route('admin.analytics.stats'));
        analyticsData.value = {
            ...defaultAnalyticsData(),
            ...response.data,
        };
    } catch (error) {
        console.error('Failed to fetch analytics:', error);
        analyticsData.value = defaultAnalyticsData();
    } finally {
        loadingAnalytics.value = false;
    }
};

onMounted(() => {
    fetchAnalytics();
});

const formatDuration = (seconds) => {
    const value = Number(seconds || 0);

    if (value < 60) {
        return `${value}s`;
    }

    const minutes = Math.floor(value / 60);
    const remainder = value % 60;

    if (minutes < 60) {
        return remainder > 0 ? `${minutes}m ${remainder}s` : `${minutes}m`;
    }

    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-PT', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getIcon = (type) => {
    switch (type) {
        case 'Project': return 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z';
        case 'Experience': return 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z';
        case 'Skill': return 'M13 10V3L4 14h7v7l9-11h-7z';
        case 'Education': return 'M12 14l9-5-9-5-9 5 9 5z';
        default: return 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z';
    }
};

const analyticsSummaryCards = [
    {
        key: 'total_views',
        label: 'Total Views',
        color: 'text-cyan-400',
        iconBg: 'bg-cyan-500/20',
        border: 'border-cyan-500/30',
        cardGlow: 'from-cyan-500/5 to-transparent',
        icon: 'M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM8 10h8M8 14h5',
        format: 'number',
    },
    {
        key: 'unique_visitors',
        label: 'Unique Visitors',
        color: 'text-purple-400',
        iconBg: 'bg-purple-500/20',
        border: 'border-purple-500/30',
        cardGlow: 'from-purple-500/5 to-transparent',
        icon: 'M12 12a4 4 0 100-8 4 4 0 000 8zm-7 8a7 7 0 1114 0H5z',
        format: 'number',
    },
    {
        key: 'avg_session_duration_seconds',
        label: 'Avg Session Time',
        color: 'text-emerald-400',
        iconBg: 'bg-emerald-500/20',
        border: 'border-emerald-500/30',
        cardGlow: 'from-emerald-500/5 to-transparent',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        format: 'duration',
    },
    {
        key: 'most_engaged_section',
        label: 'Most Engaged Section',
        color: 'text-pink-400',
        iconBg: 'bg-pink-500/20',
        border: 'border-pink-500/30',
        cardGlow: 'from-pink-500/5 to-transparent',
        icon: 'M12 8v8m-4-4h8',
        format: 'section',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <CyberAdminLayout>
        <template #header>
            {{ __('Dashboard Overview') }}
        </template>

        <div class="py-6 space-y-8">
            <div class="mx-auto max-w-7xl">
                
                <!-- Welcome Widget -->
                <div class="mb-8 bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-8 shadow-xl relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-cyan-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <h3 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 mb-3">
                            {{ __('Welcome back') }}, {{ $page.props.auth.user.name }}!
                        </h3>
                        <p class="text-gray-400 text-lg mb-8 max-w-2xl">
                            {{ __('This is your command center. Manage your portfolio content, update your skills, and showcase your latest projects.') }}
                        </p>
                        
                        <div class="flex flex-wrap gap-4 mt-auto">
                            <Link :href="route('admin.experiences.create')" class="px-5 py-2.5 bg-purple-600/20 hover:bg-purple-600/40 border border-purple-500/50 rounded-xl text-sm font-semibold text-purple-300 transition-all flex items-center gap-2 shadow-[0_0_15px_rgba(168,85,247,0.1)] hover:shadow-[0_0_20px_rgba(168,85,247,0.2)]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                {{ __('New Experience') }}
                            </Link>
                            <Link :href="route('admin.skills.create')" class="px-5 py-2.5 bg-cyan-600/20 hover:bg-cyan-600/40 border border-cyan-500/50 rounded-xl text-sm font-semibold text-cyan-300 transition-all flex items-center gap-2 shadow-[0_0_15px_rgba(6,182,212,0.1)] hover:shadow-[0_0_20px_rgba(6,182,212,0.2)]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                {{ __('New Skill') }}
                            </Link>
                            <Link :href="route('admin.projects.create')" class="px-5 py-2.5 bg-blue-600/20 hover:bg-blue-600/40 border border-blue-500/50 rounded-xl text-sm font-semibold text-blue-300 transition-all flex items-center gap-2 shadow-[0_0_15px_rgba(59,130,246,0.1)] hover:shadow-[0_0_20px_rgba(59,130,246,0.2)]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                {{ __('New Project') }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Analytics Snapshot -->
                <div class="mb-8">
                    <div class="flex items-end justify-between mb-4">
                        <div>
                            <h4 class="text-lg font-bold text-white">{{ __('Analytics Snapshot') }}</h4>
                            <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">{{ __('Top level metrics on the dashboard home') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="card in analyticsSummaryCards" :key="card.key" class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                            <div class="absolute inset-0 bg-gradient-to-br" :class="card.cardGlow"></div>
                            <div class="relative z-10 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __(card.label) }}</p>
                                    <template v-if="loadingAnalytics">
                                        <div class="h-8 w-20 mt-2 bg-white/5 animate-pulse rounded"></div>
                                    </template>
                                    <template v-else>
                                        <h4 v-if="card.format === 'number'" class="text-3xl font-bold text-white mt-1">{{ analyticsData[card.key] }}</h4>
                                        <h4 v-else-if="card.format === 'duration'" class="text-3xl font-bold text-white mt-1">{{ formatDuration(analyticsData[card.key]) }}</h4>
                                        <h4 v-else class="text-2xl font-bold text-white mt-2 truncate">{{ analyticsData.most_engaged_section?.label || __('N/A') }}</h4>
                                    </template>
                                </div>
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center border" :class="[card.iconBg, card.border, card.color]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Projects Stat -->
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent"></div>
                        <div class="relative z-10 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('Projects') }}</p>
                                <h4 class="text-3xl font-bold text-white mt-1">{{ stats.projects }}</h4>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-400 border border-blue-500/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Skills Stat -->
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 to-transparent"></div>
                        <div class="relative z-10 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('Skills') }}</p>
                                <h4 class="text-3xl font-bold text-white mt-1">{{ stats.skills }}</h4>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-cyan-500/20 flex items-center justify-center text-cyan-400 border border-cyan-500/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Experiences Stat -->
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent"></div>
                        <div class="relative z-10 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('Experiences') }}</p>
                                <h4 class="text-3xl font-bold text-white mt-1">{{ stats.experiences }}</h4>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-400 border border-purple-500/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Education Stat -->
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-pink-500/5 to-transparent"></div>
                        <div class="relative z-10 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">{{ __('Education') }}</p>
                                <h4 class="text-3xl font-bold text-white mt-1">{{ stats.education }}</h4>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-pink-500/20 flex items-center justify-center text-pink-400 border border-pink-500/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Recent Activity -->
                    <div class="lg:col-span-2 bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl overflow-hidden">
                        <div class="p-6 border-b border-white/10 bg-white/5 flex items-center justify-between">
                            <h4 class="text-lg font-bold text-white">{{ __('Recent Activity') }}</h4>
                            <Link :href="route('admin.projects.index')" class="text-[10px] text-cyan-400 hover:underline uppercase tracking-widest font-bold">
                                {{ __('View All') }}
                            </Link>
                        </div>
                        <div class="divide-y divide-white/10">
                            <div v-for="activity in recentActivity" :key="activity.id + activity.type" class="p-4 hover:bg-white/5 transition-colors group cursor-default">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 group-hover:text-purple-400 group-hover:border-purple-500/30 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(activity.type)"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-white truncate">{{ activity.title }}</p>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white/10 text-gray-300 uppercase select-none">{{ activity.type }}</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ formatDate(activity.updated_at) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="recentActivity.length === 0" class="p-8 text-center text-gray-500 italic">
                                {{ __('No recent activity found.') }}
                            </div>
                        </div>
                    </div>

                    <!-- System Status & Quick Info -->
                    <div class="space-y-6">
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                             <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-transparent"></div>
                             <div class="relative z-10 h-full">
                                <h4 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-400 shadow-[0_0_10px_rgba(74,222,128,0.5)] animate-pulse"></span>
                                    {{ __('System Status') }}
                                </h4>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-400">{{ __('Environment') }}</span>
                                        <span class="text-sm font-mono text-cyan-400 px-2 py-0.5 bg-cyan-400/10 rounded border border-cyan-400/20">Production</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-400">{{ __('Laravel Version') }}</span>
                                        <span class="text-sm font-mono text-gray-300">v11.x</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-400">{{ __('PHP Version') }}</span>
                                        <span class="text-sm font-mono text-gray-300">v8.2</span>
                                    </div>
                                    <div class="border-t border-white/10 pt-4 mt-4 text-center">
                                        <Link :href="route('admin.settings.edit')" class="text-xs font-bold text-purple-400 hover:text-purple-300 uppercase tracking-widest transition-colors">
                                            {{ __('Site Settings') }} →
                                        </Link>
                                    </div>
                                </div>
                             </div>
                        </div>

                        <!-- Info Widget -->
                        <div class="bg-gradient-to-br from-purple-600/20 to-cyan-600/20 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl flex flex-col items-center justify-center text-center">
                            <p class="text-[10px] text-gray-500 max-w-[200px] italic">
                                "The best way to predict the future is to invent it."
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
