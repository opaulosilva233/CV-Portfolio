<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

// Local state to bind to the textarea/input
const localIcon = ref(props.modelValue);

watch(() => props.modelValue, (newVal) => {
    localIcon.value = newVal;
});

const updateIcon = (val) => {
    localIcon.value = val;
    emit('update:modelValue', val);
};

// Generic Quick Icons (Feather-style SVG strings)
const quickIcons = [
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
</script>

<template>
    <div class="space-y-4">
        <!-- Preview & Custom Input -->
        <div class="flex gap-4 items-start">
            <!-- Icon Preview -->
            <div 
                class="w-16 h-16 rounded-xl bg-gray-900/80 border border-white/10 shadow-inner flex items-center justify-center overflow-hidden shrink-0 text-cyan-400"
            >
                <div v-if="localIcon" v-html="localIcon" class="w-8 h-8 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full drop-shadow-[0_0_8px_rgba(6,182,212,0.8)]"></div>
                <div v-else class="text-gray-500 text-xs text-center leading-tight">No<br>Icon</div>
            </div>

            <!-- Input area -->
            <div class="flex-1">
                <textarea 
                    v-model="localIcon"
                    @input="updateIcon($event.target.value)"
                    rows="2"
                    class="block w-full rounded-xl bg-gray-900/50 border border-white/10 shadow-inner text-white focus:border-cyan-500 focus:ring-cyan-500 transition-colors font-mono text-sm placeholder-gray-500 scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent"
                    placeholder="Paste SVG code or enter an icon class..."
                ></textarea>
            </div>
        </div>

        <!-- Quick Select -->
        <div class="bg-black/20 p-3 rounded-xl border border-white/5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Quick Select</p>
                <button 
                    v-if="localIcon" 
                    type="button" 
                    @click="updateIcon('')" 
                    class="text-xs text-red-400 hover:text-red-300 transition-colors font-medium border border-red-500/30 rounded px-2 py-0.5 hover:bg-red-500/10"
                >
                    Clear Icon
                </button>
            </div>
            <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-12 gap-2">
                <button 
                    v-for="(icon, idx) in quickIcons" 
                    :key="idx"
                    type="button"
                    @click="updateIcon(icon.svg)"
                    class="aspect-square rounded-lg bg-gray-800/50 hover:bg-gray-700/80 border border-white/5 hover:border-cyan-500/50 flex items-center justify-center transition-all group relative"
                    :title="icon.name"
                >
                    <div v-html="icon.svg" class="w-5 h-5 text-gray-400 group-hover:text-cyan-400 transition-colors [&>svg]:w-full [&>svg]:h-full"></div>
                </button>
            </div>
        </div>
    </div>
</template>
