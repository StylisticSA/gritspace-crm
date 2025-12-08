// resources/js/composables/useBodyScrollLock.js
import { watch, onUnmounted } from 'vue';

export function useBodyScrollLock(show) {
    watch(show, isOpen => {
        if (isOpen) {
            document.body.classList.add('body-no-scroll');
        } else {
            document.body.classList.remove('body-no-scroll');
        }
    });

    onUnmounted(() => {
        document.body.classList.remove('body-no-scroll');
    });
}
