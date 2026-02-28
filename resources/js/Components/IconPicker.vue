<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    category: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const localIcon = ref(props.modelValue);

watch(() => props.modelValue, (newVal) => {
    localIcon.value = newVal;
});

const updateIcon = (val) => {
    localIcon.value = val;
    emit('update:modelValue', val);
};

// Tech / Hard Skills Icons
const techIcons = [
    { name: 'Code', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>' },
    { name: 'Database', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>' },
    { name: 'Server', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>' },
    { name: 'Web', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>' },
    { name: 'Terminal', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"></polyline><line x1="12" y1="19" x2="20" y2="19"></line></svg>' },
    { name: 'Layout', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>' },
    { name: 'Mobile', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>' },
    { name: 'Shield', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>' },
    { name: 'Cloud', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path></svg>' },
    { name: 'CPU', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>' },
    { name: 'Link', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>' },
    { name: 'Settings', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>' },
];

// Soft Skills Icons
const softIcons = [
    { name: 'Teamwork', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>' },
    { name: 'Communication', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>' },
    { name: 'Leadership', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>' },
    { name: 'Creativity', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a7 7 0 0 0-7 7c0 2.38 1.19 4.47 3 5.74V17a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-2.26c1.81-1.27 3-3.36 3-5.74a7 7 0 0 0-7-7z"></path><line x1="9" y1="21" x2="15" y2="21"></line></svg>' },
    { name: 'Problem Solving', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line><line x1="11" y1="8" x2="11" y2="14"></line></svg>' },
    { name: 'Time Management', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>' },
    { name: 'Adaptability', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"></path><path d="M20.49 15a9 9 0 0 1-14.85 3.36L1 14"></path></svg>' },
    { name: 'Presentation', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h20"></path><rect x="2" y="3" width="20" height="14" rx="2"></rect><line x1="12" y1="17" x2="12" y2="21"></line><line x1="8" y1="21" x2="16" y2="21"></line></svg>' },
    { name: 'Empathy', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>' },
    { name: 'Critical Thinking', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a9 9 0 0 1 9 9c0 3.07-1.53 5.64-3.54 7.23C15.78 19.74 14 21 12 22c-2 -1-3.78-2.26-5.46-3.77C4.53 16.64 3 14.07 3 11a9 9 0 0 1 9-9z"></path><path d="M12 6v4"></path><path d="M9.5 8.5L12 6l2.5 2.5"></path><circle cx="12" cy="14" r="1"></circle></svg>' },
    { name: 'Collaboration', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>' },
    { name: 'Innovation', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>' },
    { name: 'Target', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>' },
    { name: 'Handshake', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 17l-1.5 1.5a2.12 2.12 0 0 1-3 0 2.12 2.12 0 0 1 0-3L8 14"></path><path d="M16 7l-1.5-1.5a2.12 2.12 0 0 0-3 0L7 10l5 5 4.5-4.5a2.12 2.12 0 0 0 0-3z"></path><path d="M2 12l5-5"></path><path d="M17 17l5-5"></path></svg>' },
];

// Show icons based on category
const quickIcons = computed(() => {
    if (props.category === 'soft') return softIcons;
    if (['frontend', 'backend', 'database', 'tools'].includes(props.category)) return techIcons;
    return [...techIcons, ...softIcons];
});

const categoryLabel = computed(() => {
    const labels = {
        frontend: 'Frontend',
        backend: 'Backend',
        database: 'Database',
        tools: 'Tools & DevOps',
        soft: 'Soft Skills',
        other: 'Other',
    };
    return labels[props.category] || 'All Icons';
});
</script>

<template>
    <div class="space-y-4">
        <!-- Preview + Label -->
        <div class="flex items-center gap-4">
            <div 
                :class="[
                    'w-16 h-16 rounded-xl bg-gray-900/80 shadow-inner flex items-center justify-center overflow-hidden shrink-0 transition-colors',
                    localIcon ? 'border border-cyan-500/30 text-cyan-400' : 'border border-white/10'
                ]"
            >
                <div 
                    v-if="localIcon" 
                    v-html="localIcon" 
                    class="w-8 h-8 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full drop-shadow-[0_0_8px_rgba(6,182,212,0.8)]"
                ></div>
                <div v-else class="text-gray-500 text-xs text-center leading-tight">No<br>Icon</div>
            </div>
            <div v-if="localIcon" class="text-base text-gray-300 font-medium">
                {{ quickIcons.find(i => i.svg === localIcon)?.name || 'Custom Icon' }}
            </div>
        </div>

        <!-- Quick Select Grid -->
        <div class="bg-black/20 p-4 rounded-xl border border-white/5 w-full">
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ categoryLabel }}</p>
                <button 
                    v-if="localIcon" 
                    type="button" 
                    @click="updateIcon('')" 
                    class="text-xs text-red-400 hover:text-red-300 transition-colors font-medium border border-red-500/30 rounded px-2 py-0.5 hover:bg-red-500/10"
                >
                    Clear Icon
                </button>
            </div>
            <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 gap-3">
                <button 
                    v-for="(icon, idx) in quickIcons" 
                    :key="idx"
                    type="button"
                    @click="updateIcon(icon.svg)"
                    :class="[
                        'aspect-square rounded-xl border flex items-center justify-center transition-all group relative w-full',
                        localIcon === icon.svg
                            ? 'border-cyan-500/60 bg-cyan-600/20 ring-1 ring-white/20 scale-105 shadow-[0_0_10px_rgba(6,182,212,0.3)]'
                            : 'bg-gray-800/50 hover:bg-gray-700/80 border-white/5 hover:border-cyan-500/50 hover:scale-105'
                    ]"
                    :title="icon.name"
                >
                    <div 
                        v-html="icon.svg" 
                        :class="[
                            'w-6 h-6 transition-colors [&>svg]:w-full [&>svg]:h-full',
                            localIcon === icon.svg ? 'text-cyan-400' : 'text-gray-400 group-hover:text-cyan-400'
                        ]"
                    ></div>
                </button>
            </div>
        </div>
    </div>
</template>
