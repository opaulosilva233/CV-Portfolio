<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    messages: Object,
    filters: Object,
});

const search = ref(props.filters.search);
const selectedIds = ref([]);

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Messages', active: true },
];

watch(search, debounce((value) => {
    router.get(route('admin.messages.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));

const statusClasses = {
    unread: 'bg-red-500/20 text-red-400 border-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.2)]',
    read: 'bg-blue-500/20 text-blue-400 border-blue-500/30 shadow-[0_0_10px_rgba(59,130,246,0.2)]',
    replied: 'bg-green-500/20 text-green-400 border-green-500/30 shadow-[0_0_10px_rgba(34,197,94,0.2)]',
    archived: 'bg-gray-500/20 text-gray-400 border-gray-500/30 shadow-[0_0_10px_rgba(107,114,128,0.2)]',
};

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedIds.value = props.messages.data.map(m => m.id);
    } else {
        selectedIds.value = [];
    }
};

const isAllSelected = computed(() => {
    return props.messages.data.length > 0 && selectedIds.value.length === props.messages.data.length;
});

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    
    if (confirm(__('Are you sure you want to delete the selected messages?'))) {
        router.post(route('admin.messages.bulk-delete'), {
            ids: selectedIds.value
        }, {
            onSuccess: () => {
                selectedIds.value = [];
            }
        });
    }
};
</script>

<template>
    <Head title="Messages" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex flex-col">
                    <Breadcrumbs :items="breadcrumbs" />
                    <h2 class="text-lg sm:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate mr-4">
                        {{ __('Contact Messages') }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                
                <!-- Search & Filters -->
                <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
                    <div class="relative w-full max-w-md group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            v-model="search"
                            type="text" 
                            :placeholder="__('Search by name, email or subject...')" 
                            class="block w-full pl-10 pr-3 py-2 border border-white/10 rounded-xl leading-5 bg-white/5 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-cyan-500/50 focus:border-cyan-500/50 sm:text-sm transition-all backdrop-blur-sm"
                        >
                    </div>

                    <!-- Bulk Actions -->
                    <div v-if="selectedIds.length > 0" class="flex items-center gap-4 animate-in fade-in slide-in-from-right-4 duration-300">
                        <span class="text-xs font-bold text-cyan-400 uppercase tracking-widest bg-cyan-400/10 px-3 py-1.5 rounded-lg border border-cyan-400/20 shadow-[0_0_10px_rgba(34,211,238,0.1)]">
                            {{ selectedIds.length }} {{ __('selected') }}
                        </span>
                        <button 
                            @click="bulkDelete"
                            class="px-4 py-2 bg-red-500/20 hover:bg-red-500/40 border border-red-500/50 rounded-xl font-bold text-xs text-red-400 uppercase tracking-widest transition-all backdrop-blur-sm"
                        >
                            {{ __('Delete Selected') }}
                        </button>
                    </div>
                </div>

                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden">
                    <div class="p-6">
                        
                        <div class="overflow-x-auto rounded-xl border border-white/10">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-white/5">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left">
                                            <input 
                                                type="checkbox" 
                                                :checked="isAllSelected"
                                                @change="toggleSelectAll"
                                                class="rounded border-white/10 bg-white/5 text-purple-600 focus:ring-purple-500 focus:ring-offset-gray-900"
                                            >
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Sender') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Subject') }}</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Status') }}</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Date') }}</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10 bg-transparent">
                                    <tr v-for="message in messages.data" :key="message.id" class="hover:bg-white/5 transition-colors group" :class="{ 'bg-cyan-500/5': selectedIds.includes(message.id), 'font-bold': message.status === 'unread' }">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input 
                                                type="checkbox" 
                                                v-model="selectedIds" 
                                                :value="message.id"
                                                class="rounded border-white/10 bg-white/5 text-purple-600 focus:ring-purple-500 focus:ring-offset-gray-900"
                                            >
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <div class="text-sm font-medium text-white group-hover:text-cyan-400 transition-colors">{{ message.name }}</div>
                                                <div class="text-xs text-gray-500 font-mono">{{ message.email }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-300 truncate max-w-xs">{{ message.subject }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <span 
                                                class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-lg border transition-all group-hover:scale-105"
                                                :class="statusClasses[message.status]"
                                            >
                                                {{ message.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-xs text-gray-400 font-mono">
                                                {{ new Date(message.created_at).toLocaleDateString() }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <Link :href="route('admin.messages.show', message.id)" class="text-cyan-400 hover:text-cyan-300 transition-colors">{{ __('View') }}</Link>
                                                <Link :href="route('admin.messages.destroy', message.id)" method="delete" as="button" class="text-red-400 hover:text-red-300 transition-colors" onclick="return confirm(__('Are you sure?'))">{{ __('Delete') }}</Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="messages.data.length === 0">
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                            {{ __('No messages found.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination (Simple for now) -->
                        <div v-if="messages.links && messages.links.length > 3" class="mt-6 flex justify-center">
                            <div class="flex gap-2">
                                <template v-for="(link, k) in messages.links" :key="k">
                                    <div v-if="link.url === null"  class="px-3 py-1 text-gray-600 border border-white/5 rounded-lg text-xs" v-html="link.label" />
                                    <Link v-else :href="link.url" class="px-3 py-1 border border-white/10 rounded-lg text-xs transition-all" :class="{ 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30': link.active, 'text-gray-400 hover:bg-white/5': !link.active }" v-html="link.label" />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
