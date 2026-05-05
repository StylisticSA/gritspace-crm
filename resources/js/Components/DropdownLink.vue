<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-sm leading-5 transition duration-150 ease-in-out text-start focus:outline-none"
        :class="active ? 'bg-bluemain text-white font-semibold' : 'text-gray-700 hover:bg-gray-100 focus:bg-gray-100'">
        <slot />
    </Link>
</template>
