<script setup>
import { Head, Link } from '@inertiajs/vue3';

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
    <Head title="Portfolio" />
    
    <div class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 font-sans transition-colors duration-300">
        
        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="font-bold text-xl tracking-tight">
                        {{ hero.name || 'Portfolio' }}
                    </div>
                    <div class="hidden md:flex space-x-8">
                        <button @click="scrollTo('about')" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">About</button>
                        <button @click="scrollTo('skills')" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Skills</button>
                        <button @click="scrollTo('experience')" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Experience</button>
                        <button @click="scrollTo('projects')" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Projects</button>
                    </div>
                    <div>
                         <Link v-if="canLogin" :href="route('dashboard')" class="text-sm text-gray-500 hover:text-gray-900 dark:hover:text-gray-100">
                            Admin
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section id="about" class="pt-32 pb-20 px-4">
            <div class="max-w-7xl mx-auto flex flex-col-reverse lg:flex-row items-center gap-12">
                <div class="lg:w-1/2 space-y-6">
                    <h2 class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 tracking-wide uppercase">
                        {{ hero.title || 'Full Stack Developer' }}
                    </h2>
                    <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight">
                        Hi, I'm <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">{{ hero.name }}</span>
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed max-w-lg">
                        {{ hero.bio }}
                    </p>
                    <div class="pt-4 flex gap-4">
                        <a v-for="(link, platform) in socials" :key="platform" :href="link" target="_blank" class="px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg font-medium hover:opacity-90 transition">
                            {{ platform }}
                        </a>
                    </div>
                </div>
                <div class="lg:w-1/2 flex justify-center">
                    <div class="relative w-72 h-72 lg:w-96 lg:h-96 rounded-full overflow-hidden border-4 border-white dark:border-gray-800 shadow-2xl">
                        <img 
                            :src="hero.image || 'https://ui-avatars.com/api/?name=' + (hero.name || 'User') + '&background=random'" 
                            alt="Profile" 
                            class="w-full h-full object-cover"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Skills Section -->
        <section id="skills" class="py-20 bg-gray-100 dark:bg-gray-800/50">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl font-bold mb-12 text-center">Technical Skills</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(categorySkills, category) in skills" :key="category" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 class="text-xl font-semibold mb-6 capitalize border-b pb-2 border-indigo-500 w-max">{{ category }}</h3>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="skill in categorySkills" :key="skill.id" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm font-medium">
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
                <h2 class="text-3xl font-bold mb-12 text-center">Experience</h2>
                <div class="space-y-12">
                    <div v-for="exp in experiences" :key="exp.id" class="relative pl-8 border-l-2 border-indigo-200 dark:border-indigo-900">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-indigo-600 ring-4 ring-white dark:ring-gray-900"></div>
                        <div class="mb-1 text-sm text-indigo-600 dark:text-indigo-400 font-semibold">
                            {{ exp.start_date }} - {{ exp.is_current ? 'Present' : exp.end_date }}
                        </div>
                        <h3 class="text-xl font-bold">{{ exp.role }}</h3>
                        <div class="text-lg font-medium text-gray-500 mb-2">{{ exp.company }}</div>
                        <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ exp.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Projects Section -->
        <section id="projects" class="py-20 bg-gray-100 dark:bg-gray-800/50 px-4">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-bold mb-12 text-center">Featured Projects</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="project in projects" :key="project.id" class="group bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition hover:-translate-y-1">
                        <div class="h-48 overflow-hidden bg-gray-200">
                            <img 
                                :src="project.image_url || 'https://via.placeholder.com/400x300?text=' + project.title" 
                                :alt="project.title"
                                class="w-full h-full object-cover transition duration-500 group-hover:scale-110"
                            />
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2">{{ project.title }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">{{ project.description }}</p>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span v-for="tag in project.tech_stack" :key="tag" class="text-xs px-2 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded">
                                    {{ tag }}
                                </span>
                            </div>
                            <div class="flex gap-4 mt-auto">
                                <a v-if="project.project_url" :href="project.project_url" target="_blank" class="text-indigo-600 font-medium hover:underline">View Live</a>
                                <a v-if="project.github_url" :href="project.github_url" target="_blank" class="text-gray-500 hover:text-gray-900 dark:hover:text-white">GitHub</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="py-10 text-center text-gray-500 text-sm">
            &copy; {{ new Date().getFullYear() }} {{ hero.name }}. Built with Laravel & Vue.
        </footer>
    </div>
</template>
