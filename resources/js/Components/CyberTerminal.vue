<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const __ = (key) => page.props.language?.[key] || key;

const props = defineProps({
    hero: Object,
    projects: Array,
    skills: Object,
});

const terminalHistory = ref([
    { type: 'system', text: __('SYSTEM BOOT SUCCESSFUL...') },
    { type: 'system', text: __('ESTABLISHING SECURE CONNECTION...') },
    { type: 'system', text: __('WELCOME TO PORTFOLIO_OS V2.0.0') },
    { type: 'info', text: __('Type "help" to see available commands.') },
]);

const currentInput = ref('');
const isProcessing = ref(false);
const terminalBody = ref(null);

const commands = {
    help: () => [
        { type: 'info', text: __('AVAILABLE COMMANDS:') },
        { type: 'cmd', text: '  about      - ' + __('Display system identification (Bio)') },
        { type: 'cmd', text: '  projects   - ' + __('List deployed technological units') },
        { type: 'cmd', text: '  skills     - ' + __('Scan technical capabilities') },
        { type: 'cmd', text: '  clear      - ' + __('Purge terminal buffer') },
        { type: 'cmd', text: '  whoami     - ' + __('Identity check') },
        { type: 'cmd', text: '  matrix     - ' + __('[REDACTED] Execute visual override') },
    ],
    about: () => [
        { type: 'info', text: `${__('IDENT')}: ${props.hero.name?.toUpperCase()}` },
        { type: 'info', text: `${__('TITLE')}: ${props.hero.title?.toUpperCase()}` },
        { type: 'text', text: props.hero.bio },
    ],
    projects: () => {
        const list = props.projects.map(p => ({ 
            type: 'text', 
            text: `> ${p.title.padEnd(25)} | [${p.skills?.[0]?.name || 'N/A'}]` 
        }));
        return [{ type: 'info', text: __('DEPLOYED PROJECTS:') }, ...list];
    },
    skills: () => {
        const result = [{ type: 'info', text: __('TECHNICAL CAPABILITIES SCAN:') }];
        Object.entries(props.skills).forEach(([cat, items]) => {
            result.push({ type: 'cmd', text: `${cat.toUpperCase()}:` });
            result.push({ type: 'text', text: items.map(s => s.name).join(', ') });
        });
        return result;
    },
    whoami: () => [
        { type: 'info', text: __('USER STATUS: AUTHORIZED GUEST') },
        { type: 'text', text: __('You are currently accessing a private data node. Proceed with curiosity.') },
    ],
    matrix: () => [
        { type: 'system', text: __('EXECUTING OVERRIDE...') },
        { type: 'text', text: '01 00 11 01 10 00 01 11' },
        { type: 'text', text: '10 11 00 01 10 11 00 10' },
        { type: 'text', text: '00 01 10 11 01 00 10 11' },
        { type: 'system', text: __('SYSTEM RECOVERED.') },
    ],
};

const scrollToBottom = async () => {
    await nextTick();
    if (terminalBody.value) {
        terminalBody.value.scrollTop = terminalBody.value.scrollHeight;
    }
};

const handleCommand = async () => {
    const input = currentInput.value.trim().toLowerCase();
    if (!input) return;

    terminalHistory.value.push({ type: 'user', text: `guest@portfolio:~$ ${currentInput.value}` });
    
    isProcessing.value = true;
    currentInput.value = '';

    setTimeout(async () => {
        if (input === 'clear') {
            terminalHistory.value = [];
        } else if (commands[input]) {
            const response = commands[input]();
            terminalHistory.value.push(...response);
        } else {
            terminalHistory.value.push({ type: 'error', text: `${__('Command not found')}: ${input}` });
        }
        
        isProcessing.value = false;
        await scrollToBottom();
    }, 300);
};

onMounted(() => {
    scrollToBottom();
});
</script>

<template>
    <div class="bg-gray-900/90 dark:bg-black/80 backdrop-blur-xl border border-white/10 rounded-2xl overflow-hidden shadow-2xl font-mono text-sm h-[500px] flex flex-col group">
        <!-- Terminal Header -->
        <div class="bg-white/5 border-b border-white/10 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="flex gap-1.5">
                    <div class="w-3 h-3 rounded-full bg-rose-500/50"></div>
                    <div class="w-3 h-3 rounded-full bg-amber-500/50"></div>
                    <div class="w-3 h-3 rounded-full bg-emerald-500/50"></div>
                </div>
                <span class="ml-4 text-[10px] font-black tracking-widest text-gray-400 uppercase">{{ __('Interactive Terminal v2.0') }}</span>
            </div>
            <div class="flex items-center gap-2 text-[10px] text-emerald-500">
                <div class="w-2 h-2 rounded-full bg-current animate-pulse"></div>
                LIVE_NODE_CONNECTED
            </div>
        </div>

        <!-- Terminal Body -->
        <div ref="terminalBody" class="flex-1 overflow-y-auto p-6 space-y-2 scrollbar-hide custom-scrollbar">
            <div v-for="(line, index) in terminalHistory" :key="index" :class="{
                'text-emerald-500 font-bold': line.type === 'system',
                'text-purple-400': line.type === 'info',
                'text-cyan-400': line.type === 'cmd',
                'text-gray-300': line.type === 'user',
                'text-rose-500': line.type === 'error',
                'text-gray-400': line.type === 'text'
            }">
                <span v-if="line.type === 'system'">[SYS] </span>
                <span v-if="line.type === 'info'">[INFO] </span>
                <span v-if="line.type === 'error'">[ERR] </span>
                {{ line.text }}
            </div>
            
            <!-- Input Area -->
            <div class="flex items-center gap-2 pt-2">
                <span class="text-emerald-500 font-bold">guest@portfolio:~$</span>
                <input 
                    v-model="currentInput"
                    @keyup.enter="handleCommand"
                    type="text"
                    class="bg-transparent border-none outline-none p-0 flex-1 text-gray-300 placeholder-white/5"
                    spellcheck="false"
                    autocomplete="off"
                    :disabled="isProcessing"
                />
            </div>
        </div>

        <!-- Footer / Status -->
        <div class="bg-white/5 border-t border-white/10 px-4 py-2 flex items-center justify-between text-[10px] text-gray-500">
            <span>{{ __('READY_FOR_INPUT') }}</span>
            <span>UTF-8 // PORT: 443</span>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
