// composables/useInactivityMonitor.js
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

export default function useInactivityMonitor({
    timeoutMs = 600000, // 10 minutes
    warnBeforeLogout = true,
    warnMs = 540000, // warn at 9 minutes
} = {}) {
    const showWarning = ref(false);
    let logoutTimer = null;
    let warnTimer = null;

    const resetTimers = () => {
        clearTimeout(logoutTimer);
        clearTimeout(warnTimer);
        showWarning.value = false;

        logoutTimer = setTimeout(() => {
            router.post(route('logout'));
        }, timeoutMs);

        if (warnBeforeLogout) {
            warnTimer = setTimeout(() => {
                showWarning.value = true;
            }, warnMs);
        }
    };

    const bindEvents = () => {
        ['mousemove', 'click', 'scroll', 'keydown'].forEach(event => window.addEventListener(event, resetTimers));
    };

    const unbindEvents = () => {
        ['mousemove', 'click', 'scroll', 'keydown'].forEach(event => window.removeEventListener(event, resetTimers));
        clearTimeout(logoutTimer);
        clearTimeout(warnTimer);
    };

    onMounted(() => {
        resetTimers();
        bindEvents();
    });

    onUnmounted(() => {
        unbindEvents();
    });

    return {
        showWarning,
    };
}
