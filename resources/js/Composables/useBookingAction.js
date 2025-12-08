import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

export const useBookingAction = () => {
    const isProcessing = ref(false);
    const messageType = ref('');

    const processBooking = async ({
        id,
        routeName,
        successMsg = 'Action completed successfully.',
        type = 'info',
        onClose = () => {},
        setMessage = () => {},
    }) => {
        if (!id || !routeName) return;

        isProcessing.value = true;

        await router.put(
            route(routeName, { booking: id }),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setMessage(successMsg);
                    messageType.value = type;
                    onClose();
                },
                onError: () => {
                    setMessage('Something went wrong.');
                    messageType.value = 'error';
                },
                onFinish: () => {
                    isProcessing.value = false;
                },
            }
        );
    };

    return {
        processBooking,
        isProcessing,
        messageType,
    };
};
