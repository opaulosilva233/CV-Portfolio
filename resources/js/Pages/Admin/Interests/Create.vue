<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import IconPicker from '@/Components/IconPicker.vue';

const form = useForm({
    name: '',
    description: '',
    category: 'hobby',
    icon: '',
    is_active: true,
});

const submit = () => {
    form.post(route('admin.interests.store'));
};

const categories = [
    { value: 'hobby', label: 'Hobby' },
    { value: 'music', label: 'Music' },
    { value: 'sport', label: 'Sport' },
    { value: 'book', label: 'Book' },
    { value: 'travel', label: 'Travel' },
    { value: 'other', label: 'Other' },
];
</script>

<template>
    <Head :title="__('Add New Interest')" />

    <CyberAdminLayout>
        <template #header>
            <div class="flex items-center justify-between w-full gap-3">
                <h2 class="text-base sm:text-lg md:text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 truncate flex-1">
                    {{ __('Add New Interest') }}
                </h2>
                <Link :href="route('admin.interests.index')" class="text-gray-400 hover:text-white transition-colors text-sm font-medium flex items-center gap-2 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span class="hidden sm:inline">{{ __('Back to List') }}</span>
                    <span class="sm:hidden">{{ __('Back') }}</span>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 shadow-xl sm:rounded-2xl overflow-hidden p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('Interest Name') }}</label>
                            <input v-model="form.name" type="text" required :placeholder="__('e.g. Playing Guitar, Hiking')" 
                                class="w-full bg-gray-900/50 border border-white/10 rounded-xl px-4 py-2.5 text-white focus:border-cyan-500 focus:ring-0 transition-all shadow-inner">
                            <div v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('Category') }}</label>
                            <select v-model="form.category" 
                                class="w-full bg-gray-900/50 border border-white/10 rounded-xl px-4 py-2.5 text-white focus:border-cyan-500 focus:ring-0 transition-all shadow-inner capitalize [&>option]:bg-gray-900">
                                <option v-for="cat in categories" :key="cat.value" :value="cat.value">{{ __(cat.label) }}</option>
                            </select>
                            <div v-if="form.errors.category" class="text-red-400 text-xs mt-1">{{ form.errors.category }}</div>
                        </div>

                        <!-- Icon Picker -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('Icon') }}</label>
                            <IconPicker v-model="form.icon" :category="form.category" />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ __('Description / Note') }}</label>
                            <textarea v-model="form.description" rows="3" :placeholder="__('A brief note about this interest...')" 
                                class="w-full bg-gray-900/50 border border-white/10 rounded-xl px-4 py-2.5 text-white focus:border-cyan-500 focus:ring-0 transition-all shadow-inner"></textarea>
                            <div v-if="form.errors.description" class="text-red-400 text-xs mt-1">{{ form.errors.description }}</div>
                        </div>

                        <!-- Active Switch -->
                        <div class="flex items-center gap-3">
                            <input type="checkbox" v-model="form.is_active" id="is_active" class="w-5 h-5 bg-gray-900/50 border border-white/10 rounded transition-colors text-cyan-600 focus:ring-0">
                            <label for="is_active" class="text-sm font-medium text-gray-300">{{ __('Display on Portfolio') }}</label>
                        </div>

                        <div class="pt-6 border-t border-white/10">
                            <button type="submit" :disabled="form.processing" 
                                class="w-full py-3 bg-gradient-to-r from-purple-600 to-cyan-600 hover:from-purple-500 hover:to-cyan-500 text-white font-bold rounded-xl shadow-[0_0_15px_rgba(6,182,212,0.3)] transition-all duration-300 disabled:opacity-50">
                                {{ form.processing ? __('Applying...') : __('Create Interest') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
