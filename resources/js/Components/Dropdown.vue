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

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.removeEventListener('click', handleClickOutside);
});

const widthClass = computed(() => ({ 48: 'w-48' })[props.width.toString()]);

const alignmentClasses = computed(() => {
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
        <div @click.stop="open = !open">
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
                class="absolute z-50 mt-2 rounded-md shadow-lg"
                :class="[widthClass, alignmentClasses]"
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
