<script setup>
import { computed } from 'vue';

const props = defineProps({
    date: {
        type: String,
        required: true,
    },
});

const formattedDate = computed(() => {
    const date = new Date(props.date);

    const day = date.getDate();
    const month = date.toLocaleString('en-US', { month: 'short' });
    const year = date.getFullYear();

    return `${day} ${month} ${year}`;
});
</script>

<template>
    <span>{{ formattedDate }}</span>
</template>
