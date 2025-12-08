// composables/useStatusMessage.js
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

export default function useStatusMessage(timeout = 3000) {
    const page = usePage();
    const message = ref(null);
    const status = ref('success');

    const flashMessage = computed(() => page.props?.flash?.success || null);

    const showMessage = computed(() => {
        return !!(message.value?.trim?.() || flashMessage.value?.trim?.());
    });

    const messageText = computed(() => {
        return message.value || flashMessage.value || '';
    });

    const messageClass = computed(() => {
        const base = 'p-3 mb-4 rounded';
        const map = {
            success: 'bg-green-100 text-green-800',
            pending: 'bg-yellow-100 text-yellow-800',
            rejected: 'bg-primary text-white',
            cancelled: 'bg-gray-100 text-gray-700',
            deleted: 'bg-red-100 text-red-800',
        };
        return `${base} ${map[status.value] || map.success}`;
    });

    watch(showMessage, msg => {
        if (msg) {
            setTimeout(() => {
                message.value = null;
            }, timeout);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    return {
        message,
        status,
        showMessage,
        messageText,
        messageClass,
    };
}
