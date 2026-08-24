<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Line, Bar, Doughnut } from 'vue-chartjs';

const props = defineProps({ analytics: Object, period: Object });
const selectedPeriod = ref(props.period?.days || 30);

const formatDuration = (seconds) => {
  const value = Number(seconds || 0);
  if (value < 60) return `${value}s`;
  const minutes = Math.floor(value / 60);
  if (minutes < 60) return `${minutes}m ${value % 60}s`;
  const hours = Math.floor(minutes / 60);
  return `${hours}h ${minutes % 60}m`;
};

const trafficChartData = computed(() => ({
  labels: props.analytics?.traffic_timeline?.map(d => d.date) || [],
  datasets: [
    { label: 'Page Views', data: props.analytics?.traffic_timeline?.map(d => d.views) || [], borderColor: '#22d3ee', backgroundColor: 'rgba(34, 211, 238, 0.1)', fill: true, tension: 0.4 },
    { label: 'Unique Visitors', data: props.analytics?.traffic_timeline?.map(d => d.unique_visitors) || [], borderColor: '#a78bfa', backgroundColor: 'rgba(167, 139, 250, 0.1)', fill: true, tension: 0.4 }
  ]
}));

const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: '#9ca3af' } } }, scales: { y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af' } }, x: { ticks: { color: '#9ca3af' } } } };
</script>

<template>
  <Head title="Analytics" />
  <CyberAdminLayout>
    <template #header>{{ __('Site Analytics') }}</template>
    <div class="py-6 space-y-8 mx-auto max-w-7xl">
      <div class="mb-6 flex items-center justify-between">
        <div><h4 class="text-lg font-bold text-white">{{ __('Analytics Dashboard') }}</h4><p class="text-xs text-gray-500 mt-1">{{ __('Visitor insights and engagement metrics') }}</p></div>
        <div class="flex gap-2"><button v-for="p in period?.available_periods" :key="p" @click="$inertia.get(route('admin.analytics.index'), { days: p })" :class="['px-4 py-2 rounded-lg text-sm border', selectedPeriod === p ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-400' : 'bg-white/5 border-white/10 text-gray-400']">{{ p }}d</button></div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div v-for="card in [{key:'total_views',label:'Total Views',glow:'from-cyan-500/5'},{key:'unique_visitors',label:'Unique Visitors',glow:'from-purple-500/5'},{key:'recruiter_visits',label:'Recruiter Visits',glow:'from-pink-500/5'},{key:'countries_count',label:'Countries',glow:'from-emerald-500/5'}]" :key="card.key" class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br" :class="card.glow"></div>
          <div class="relative z-10"><p class="text-sm text-gray-400 uppercase">{{ __(card.label) }}</p><h4 class="text-3xl font-bold text-white mt-2">{{ analytics?.overview?.[card.key] || 0 }}</h4></div>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <div class="lg:col-span-2 bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6"><h4 class="text-lg font-bold text-white mb-4">{{ __('Traffic Timeline') }}</h4><div class="h-[300px]"><Line v-if="analytics?.traffic_timeline" :data="trafficChartData" :options="chartOptions" /></div></div>
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6"><h4 class="text-lg font-bold text-white mb-4">{{ __('Visitor Types') }}</h4><div class="h-[300px]"><Doughnut v-if="analytics?.visitor_types?.by_type" :data="{labels:analytics.visitor_types.by_type.map(v=>v.label),datasets:[{data:analytics.visitor_types.by_type.map(v=>v.unique_visitors),backgroundColor:['#a78bfa','#22d3ee','#34d399','#f472b6','#fbbf24'],borderWidth:0}]}" :options="{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{color:'#9ca3af'}}}}" /></div></div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6"><h4 class="text-lg font-bold text-white mb-4">{{ __('Top Countries') }}</h4><div class="space-y-2"><div v-for="c in analytics?.geographic_data?.by_country?.slice(0,10)" :key="c.country" class="flex justify-between items-center p-3 bg-white/5 rounded-lg"><span class="text-white">{{ c.flag }} {{ c.country }}</span><span class="text-cyan-400 font-bold">{{ c.unique_visitors }}</span></div></div></div>
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6"><h4 class="text-lg font-bold text-white mb-4">{{ __('Top Companies') }}</h4><div class="space-y-2 max-h-[300px] overflow-y-auto"><div v-for="(co,i) in analytics?.company_data?.slice(0,15)" :key="i" class="flex justify-between items-center p-3 bg-white/5 rounded-lg"><div class="min-w-0"><p class="text-white text-sm truncate">{{ co.company }}</p><p class="text-xs text-gray-500">{{ co.country }} • {{ co.is_recruiter ? '🎯 Recruiter' : 'General' }}</p></div><span class="text-cyan-400 font-bold">{{ co.visits }}</span></div></div></div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6"><h4 class="text-lg font-bold text-white mb-4">{{ __('Section Engagement') }}</h4><div class="space-y-2 max-h-[400px] overflow-y-auto"><div v-for="s in analytics?.section_engagement" :key="s.section" class="p-4 bg-white/5 rounded-xl"><div class="flex justify-between mb-2"><span class="text-white font-semibold">{{ s.label }}</span><span class="text-cyan-400 font-bold">{{ formatDuration(s.total_seconds) }}</span></div><div class="grid grid-cols-3 gap-2 text-xs text-gray-400"><span>Avg: {{ formatDuration(s.average_seconds) }}</span><span>Visitors: {{ s.unique_visitors }}</span><span>Interactions: {{ s.interactions }}</span></div></div></div></div>
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6"><h4 class="text-lg font-bold text-white mb-4">{{ __('Top Cities') }}</h4><div class="space-y-2 max-h-[400px] overflow-y-auto"><div v-for="(city,i) in analytics?.geographic_data?.by_city?.slice(0,20)" :key="i" class="flex justify-between items-center p-3 bg-white/5 rounded-lg"><div><p class="text-white text-sm">{{ city.city }}</p><p class="text-xs text-gray-500">{{ city.country }}</p></div><span class="text-purple-400 font-bold">{{ city.visits }}</span></div></div></div>
      </div>
    </div>
  </CyberAdminLayout>
</template>
