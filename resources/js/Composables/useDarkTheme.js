import { onMounted, ref } from 'vue';

export function useDarkTheme() {
    const isDark = ref(false);

    const toggleTheme = () => {
        isDark.value = !isDark.value;
        if (isDark.value) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    };

    onMounted(() => {
        const savedTheme = localStorage.getItem('theme');
        const updatedTheme = savedTheme === 'dark' || (!savedTheme && true); // Default to dark
        isDark.value = updatedTheme;
        if (updatedTheme) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    });

    return {
        isDark,
        toggleTheme,
    };
}
