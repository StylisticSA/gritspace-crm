<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-4 py-2 bg-primary rounded text-white text-sm font-semibold transition duration-150 ease-in-out focus:outline-none'
        : 'inline-flex items-center px-4 py-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md transition duration-150 ease-in-out focus:outline-none'
);
</script>

<template>
    <Link
        :href="href"
        :class="classes">
        <slot />
    </Link>
</template>
