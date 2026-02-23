<script setup>
import CyberAdminLayout from '@/Layouts/CyberAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <CyberAdminLayout>
        <template #header>
            {{ __('Dashboard Overview') }}
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <!-- Dashboard Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Welcome Widget -->
                    <div class="col-span-1 md:col-span-2 bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-cyan-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <h3 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-cyan-400 mb-2">
                                {{ __('Welcome back') }}, {{ $page.props.auth.user.name }}!
                            </h3>
                            <p class="text-gray-400 mb-6">
                                {{ __('This is your command center. Manage your portfolio content, update your skills, and showcase your latest projects.') }}
                            </p>
                            
                            <div class="flex flex-wrap gap-4 mt-auto">
                                <Link :href="route('admin.experiences.create')" class="px-4 py-2 bg-purple-600/20 hover:bg-purple-600/40 border border-purple-500/50 rounded-lg text-sm font-medium text-purple-300 transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    New Experience
                                </Link>
                                <Link :href="route('admin.skills.create')" class="px-4 py-2 bg-cyan-600/20 hover:bg-cyan-600/40 border border-cyan-500/50 rounded-lg text-sm font-medium text-cyan-300 transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    New Skill
                                </Link>
                                <Link :href="route('admin.projects.create')" class="px-4 py-2 bg-blue-600/20 hover:bg-blue-600/40 border border-blue-500/50 rounded-lg text-sm font-medium text-blue-300 transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    New Project
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Widget -->
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                         <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                         <div class="relative z-10 flex flex-col justify-between h-full">
                            <h4 class="text-lg font-semibold text-gray-200">{{ __('System Status') }}</h4>
                            <div class="mt-4 flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-green-400 shadow-[0_0_10px_rgba(74,222,128,0.5)] animate-pulse"></div>
                                <span class="text-green-400 font-mono text-sm">{{ __('All systems operational') }}</span>
                            </div>
                         </div>
                    </div>

                </div>
            </div>
        </div>
    </CyberAdminLayout>
</template>
