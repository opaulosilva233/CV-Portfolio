import { useForm as useInertiaForm } from '@inertiajs/vue3';
import { watch } from 'vue';

export function useCachedForm(cacheKey, initialData) {
    let cachedData = null;
    try {
        const item = localStorage.getItem(`form_cache_${cacheKey}`);
        if (item) {
            cachedData = JSON.parse(item);
        }
    } catch (e) {
        // Ignored
    }

    const dataToUse = cachedData ? { ...initialData, ...cachedData } : initialData;

    const form = useInertiaForm(dataToUse);

    // Watch for deep changes to automatically backup state to local storage
    watch(() => form.data(), (newData) => {
        const cleanedData = { ...newData };
        
        // Remove File objects as they cannot be serialized
        Object.keys(cleanedData).forEach(key => {
            if (cleanedData[key] instanceof File || cleanedData[key] instanceof Blob || cleanedData[key] instanceof FileList) {
                cleanedData[key] = null;
            }
        });

        localStorage.setItem(`form_cache_${cacheKey}`, JSON.stringify(cleanedData));
    }, { deep: true });

    // Method to manually clear the cache
    form.clearCache = () => {
        localStorage.removeItem(`form_cache_${cacheKey}`);
    };

    // Override the submission methods to clear our cache on success
    const methodsToPatch = ['post', 'put', 'patch', 'delete', 'submit'];
    methodsToPatch.forEach(method => {
        const originalMethod = form[method].bind(form);
        form[method] = (...args) => {
            // Options is usually the last object in arguments, or second argument depending on method,
            // e.g., post(url, options) or submit(method, url, options)
            let optionsIndex = args.findIndex(arg => typeof arg === 'object' && arg !== null && 'onSuccess' in arg === false && 'preserveState' in arg === false ? false : true);
            
            // Actually, in inertia, options are the last argument. So let's safely patch it:
            const urlOrMethod = args[0];
            let options = null;

            if (method === 'submit') {
                // submit(method, url, options)
                options = args[2] || {};
                optionsIndex = 2;
            } else {
                // post(url, options), put(url, options), etc
                options = args[1] || {};
                optionsIndex = 1;
            }

            const originalOnSuccess = options.onSuccess;
            options.onSuccess = (page) => {
                form.clearCache();
                if (originalOnSuccess) {
                    return originalOnSuccess(page);
                }
            };

            args[optionsIndex] = options;
            originalMethod(...args);
        };
    });

    return form;
}
