<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    message: Object,
});

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

const breadcrumbs = [
    { label: 'Contact Messages', active: true },
];

const form = useForm({
    status: props.message.status,
});

const updateStatus = () => {
    form.put(route('admin.messages.update', props.message.id), {
        preserveScroll: true,
    });
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'unread': return 'bg-red-500/20 text-red-400 border-red-500/30';
        case 'read': return 'bg-blue-500/20 text-blue-400 border-blue-500/30';
        case 'replied': return 'bg-green-500/20 text-green-400 border-green-500/30';
        default: return 'bg-gray-500/20 text-gray-400 border-gray-500/30';
    }
};
</script>

<template>
    <Head title="View Message" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex flex-col min-w-0 w-full">
                <Breadcrumbs :items="breadcrumbs" />
                <h2 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate">
                    {{ __('Message from') }} {{ message.name }}
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Message Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-8 shadow-xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4">
                                <span :class="getStatusBadge(message.status)" class="px-3 py-1 text-[10px] font-bold uppercase rounded-full border">
                                    {{ __(message.status) }}
                                </span>
                            </div>
                            
                            <div class="flex items-start gap-4 mb-8">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-cyan-500 flex items-center justify-center text-white font-bold text-xl shadow-[0_0_15px_rgba(168,85,247,0.3)]">
                                    {{ message.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-xl font-bold text-white">{{ message.name }}</h3>
                                    <p class="text-sm text-cyan-400 font-mono">{{ message.email }}</p>
                                    <p class="text-[10px] text-gray-500 font-mono mt-1 uppercase tracking-tighter">{{ formatDate(message.created_at) }}</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block mb-1">{{ __('Subject') }}</label>
                                    <p class="text-lg font-semibold text-gray-200">{{ message.subject || __('(No Subject)') }}</p>
                                </div>
                                
                                <div class="pt-6 border-t border-white/5">
                                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block mb-4">{{ __('Message') }}</label>
                                    <div class="text-gray-300 leading-relaxed whitespace-pre-wrap bg-white/5 p-6 rounded-xl border border-white/5 italic">
                                        "{{ message.message }}"
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 pt-8 border-t border-white/5 flex gap-4">
                                <a 
                                    :href="`mailto:${message.email}?subject=RE: ${message.subject || 'Portfolio Inquiry'}`"
                                    class="px-6 py-2.5 bg-purple-600/80 hover:bg-purple-500 border border-purple-500/50 rounded-xl font-bold text-xs text-white uppercase tracking-widest shadow-[0_0_15px_rgba(168,85,247,0.4)] transition-all flex items-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    {{ __('Reply via Email') }}
                                </a>
                                <Link 
                                    :href="route('admin.messages.destroy', message.id)" 
                                    method="delete" 
                                    as="button" 
                                    class="px-6 py-2.5 bg-red-500/20 hover:bg-red-500/40 border border-red-500/50 rounded-xl font-bold text-xs text-red-400 uppercase tracking-widest transition-all"
                                    onclick="return confirm('Are you sure you want to delete this message?')"
                                >
                                    {{ __('Delete Message') }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Stats/Metadata -->
                    <div class="space-y-6">
                        <form @change="updateStatus" class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl space-y-4">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block">{{ __('Update Status') }}</label>
                            <select 
                                v-model="form.status"
                                class="w-full bg-white/5 border border-white/10 rounded-xl text-sm text-gray-300 focus:ring-cyan-500 focus:border-cyan-500"
                            >
                                <option value="unread" class="bg-gray-900">{{ __('Unread') }}</option>
                                <option value="read" class="bg-gray-900">{{ __('Read') }}</option>
                                <option value="replied" class="bg-gray-900">{{ __('Replied') }}</option>
                            </select>
                            <p class="text-[10px] text-gray-500 italic">{{ __('Changing status will automatically update the record.') }}</p>
                        </form>

                        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block mb-4">{{ __('Metadata') }}</label>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-400">{{ __('Received At') }}</span>
                                    <span class="text-xs font-mono text-gray-200">{{ formatDate(message.created_at) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-400">{{ __('Read At') }}</span>
                                    <span class="text-xs font-mono text-gray-200">{{ message.read_at ? formatDate(message.read_at) : __('Never') }}</span>
                                </div>
                            </div>
                        </div>

                        <Link 
                            :href="route('admin.messages.index')" 
                            class="flex items-center justify-center gap-2 px-4 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-bold text-gray-300 uppercase tracking-widest transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            {{ __('Back to Messages') }}
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </CyberAdminLayout>
</template>
