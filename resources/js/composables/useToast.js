import { ref } from 'vue';

// Singleton — shared across all components
const _toast = ref(null);

export function useToast() {
    function setInstance(instance) {
        _toast.value = instance;
    }

    function success(message, title = '') {
        _toast.value?.success(message, title);
    }

    function error(message, title = '') {
        _toast.value?.error(message, title);
    }

    function warning(message, title = '') {
        _toast.value?.warning(message, title);
    }

    function info(message, title = '') {
        _toast.value?.info(message, title);
    }

    return { setInstance, success, error, warning, info };
}
