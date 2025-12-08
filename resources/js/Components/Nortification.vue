<template>
    <div
        v-if="visible && message"
        :class="classes">
        {{ message }}
    </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue';

interface Props {
    message: string | null;
    type: 'success' | 'error';
    duration?: number;
}

const props = defineProps<Props>();
const visible = ref(false);

const classes = computed(() => {
    return props.type === 'success'
        ? 'px-4 py-3 mb-4 text-sm text-green-800 bg-green-100 border border-green-300 rounded'
        : 'px-4 py-3 mb-4 text-sm text-red-700 bg-red-100 border border-red-300 rounded';
});

onMounted(() => {
    if (props.message) {
        visible.value = true;
        setTimeout(() => {
            visible.value = false;
        }, props.duration ?? 4000);
    }
});

watch(
    () => props.message,
    msg => {
        if (msg) {
            visible.value = true;
            setTimeout(() => {
                visible.value = false;
            }, props.duration ?? 4000);
        }
    }
);
</script>
