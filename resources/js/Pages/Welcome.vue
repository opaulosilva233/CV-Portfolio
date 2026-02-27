<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CyberLayout from '@/Layouts/CyberLayout.vue';

defineProps({
    hero: Object,
    projects: Array,
    skills: Object,
    experiences: Array,
    socials: [Object, Array],
    canLogin: Boolean,
});

const scrollTo = (id) => {
    const element = document.getElementById(id);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <CyberLayout>
        <Head title="Portfolio" />
        
        <!-- Navigation (Adjusted for Cyber Layout) -->
        <nav class="fixed top-0 w-full z-40 bg-white/5 dark:bg-black/20 backdrop-blur-md border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-4">
                        <img src="/images/Logotipo.png" alt="Logotipo" class="h-14 w-auto scale-125 transform hover:scale-[1.35] transition-transform duration-300" />
                        <span class="font-bold text-xl tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-cyan-500 to-purple-500 ml-2">{{ hero.name || 'Portfolio' }}</span>
                    </div>
                    <div class="hidden md:flex space-x-8">
                        <button @click="scrollTo('about')" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition font-medium">{{ __('About') }}</button>
                        <button @click="scrollTo('skills')" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition font-medium">{{ __('Skills') }}</button>
                        <button @click="scrollTo('experience')" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition font-medium">{{ __('Experience') }}</button>
                        <button @click="scrollTo('projects')" class="text-gray-600 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition font-medium">{{ __('Projects') }}</button>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- ThemeToggle removed (handled by CyberLayout) -->
                         <Link v-if="canLogin" :href="route('dashboard')" class="text-sm font-bold uppercase tracking-widest text-gray-500 hover:text-purple-600 dark:hover:text-cyan-400 transition">
                            {{ __('Admin') }}
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section id="about" class="pt-32 pb-20 px-4 min-h-screen flex items-center">
            <div class="max-w-7xl mx-auto flex flex-col-reverse lg:flex-row items-center gap-12">
                <div class="lg:w-1/2 space-y-6">
                    <h2 class="text-sm font-bold text-cyan-600 dark:text-cyan-400 tracking-[0.2em] uppercase animate-pulse">
                        {{ hero.title || 'Full Stack Developer' }}
                    </h2>
                    <h1 class="text-5xl lg:text-7xl font-black tracking-tight text-gray-900 dark:text-white">
                        Hi, I'm <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 via-pink-600 to-cyan-600">{{ hero.name }}</span>
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed max-w-lg font-medium">
                        {{ hero.bio }}
                    </p>
                    <div class="pt-4 flex gap-4">
                        <a v-for="(link, platform) in socials" :key="platform" :href="link" target="_blank" class="px-6 py-3 bg-gray-900 dark:bg-white/10 dark:hover:bg-white/20 backdrop-blur-md border border-white/10 text-white dark:text-white rounded-xl font-bold uppercase tracking-wide hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-purple-500/30">
                            {{ platform }}
                        </a>
                    </div>
                </div>
                <div class="lg:w-1/2 flex justify-center">
                    <div class="relative w-72 h-72 lg:w-96 lg:h-96">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-cyan-600 rounded-full blur-2xl opacity-50 animate-pulse"></div>
                        <div class="relative w-full h-full rounded-full overflow-hidden border-4 border-white/20 dark:border-white/10 shadow-2xl">
                            <img 
                                :src="hero.image || 'https://ui-avatars.com/api/?name=' + (hero.name || 'User') + '&background=random'" 
                                alt="Profile" 
                                class="w-full h-full object-cover"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Skills Section -->
        <section id="skills" class="py-20 px-4">
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

        <!-- Experience Section -->
        <section id="experience" class="py-20 px-4">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-black mb-12 text-center text-gray-900 dark:text-white uppercase tracking-widest"><span class="text-cyan-500">#</span> {{ __('Experience') }}</h2>
                <div class="space-y-12">
                    <div v-for="exp in experiences" :key="exp.id" class="relative pl-8 border-l-2 border-purple-200 dark:border-purple-900/50">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-purple-600 ring-4 ring-white dark:ring-black shadow-[0_0_10px_rgba(147,51,234,0.5)]"></div>
                        <div class="mb-1 text-sm text-purple-600 dark:text-purple-400 font-bold uppercase tracking-wide">
                            {{ exp.start_date }} - {{ exp.is_current ? __('Present') : exp.end_date }}
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ exp.role }}</h3>
                        <div class="text-lg font-medium text-gray-500 dark:text-gray-400 mb-2">{{ exp.company }}</div>
                        <p class="text-gray-600 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ exp.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Projects Section -->
        <section id="projects" class="py-20 px-4">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black mb-12 text-center text-gray-900 dark:text-white uppercase tracking-widest"><span class="text-pink-500">#</span> {{ __('Featured Projects') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="project in projects" :key="project.id" class="group bg-white/50 dark:bg-black/40 backdrop-blur-xl rounded-2xl overflow-hidden shadow-lg border border-white/20 dark:border-white/5 hover:border-pink-500/50 hover:shadow-pink-500/20 transition-all duration-300 hover:-translate-y-2">
                        <div class="h-48 overflow-hidden bg-gray-200 dark:bg-gray-800 relative">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent z-10 opacity-60"></div>
                            <img 
                                :src="project.image_url || 'https://via.placeholder.com/400x300?text=' + project.title" 
                                :alt="project.title"
                                class="w-full h-full object-cover transition duration-500 group-hover:scale-110"
                            />
                        </div>
                        <div class="p-6 relative z-20">
                            <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white group-hover:text-pink-500 transition-colors">{{ project.title }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">{{ project.description }}</p>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span v-for="tag in project.tech_stack" :key="tag" class="text-[10px] uppercase font-bold px-2 py-1 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded border border-transparent group-hover:border-pink-500/30 transition-colors">
                                    {{ tag }}
                                </span>
                            </div>
                            <div class="flex gap-4 mt-auto">
                                <a v-if="project.project_url" :href="project.project_url" target="_blank" class="text-pink-600 font-bold hover:underline uppercase text-xs tracking-wider">{{ __('View Live') }}</a>
                                <a v-if="project.github_url" :href="project.github_url" target="_blank" class="text-gray-500 hover:text-gray-900 dark:hover:text-white font-bold uppercase text-xs tracking-wider">GitHub</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="py-10 text-center text-gray-500 dark:text-gray-400 text-sm font-medium border-t border-white/10">
            &copy; {{ new Date().getFullYear() }} {{ hero.name }}. <span class="opacity-50">{{ __('System Online.') }}</span>
        </footer>
    </CyberLayout>
</template>
