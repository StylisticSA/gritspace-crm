<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    align: { type: String, default: 'right' },
    width: { type: String, default: '48' },
    contentClasses: { type: String, default: 'py-1 bg-white' },
    nested: { type: Boolean, default: false },
});

const open = ref(false);
const dropdownRef = ref(null);

const closeOnEscape = e => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

const handleClickOutside = e => {
    if (open.value && dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        open.value = false;
    }
};

const toggle = () => {
    if (!open.value) {
        if (props.nested) {
            window.dispatchEvent(new CustomEvent('close-nested-dropdowns'));
        }
        open.value = true;
    } else {
        open.value = false;
    }
};

const handleCloseNested = () => {
    if (props.nested) {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('close-nested-dropdowns', handleCloseNested);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('close-nested-dropdowns', handleCloseNested);
});

const widthClass = computed(() => ({ 48: 'w-48' })[props.width.toString()]);

const alignmentClasses = computed(() => {
    if (props.nested) {
        return 'sm:absolute sm:top-0 sm:right-full sm:mr-1 sm:mt-0';
    }

    switch (props.align) {
        case 'left':
            return 'start-0';
        case 'right':
            return 'end-0';
        case 'center':
            return 'left-1/2 -translate-x-1/2';
        default:
            return 'origin-top';
    }
});
</script>

<template>
    <div
        class="relative"
        ref="dropdownRef">
        <div @click.stop="toggle">
            <slot name="trigger" />
        </div>

        <div
            v-if="!nested"
            v-show="open"
            class="fixed inset-0 z-40"
            @click.self="open = false" />

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="scale-95 opacity-0"
            enter-to-class="scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="scale-100 opacity-100"
            leave-to-class="scale-95 opacity-0">
            <div
                v-show="open"
                class="absolute z-50 rounded-md shadow-lg"
                :class="[widthClass, alignmentClasses, nested ? '' : 'mt-1']"
                @click.stop>
                <div
                    class="rounded-md ring-1 ring-black ring-opacity-5"
                    :class="contentClasses">
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>
