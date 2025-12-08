<script setup>
import { router } from '@inertiajs/vue3';

const goToBookings = () => {
    router.visit(route('bookingoffices.show'));
};
</script>
<template>
    <button
        @click="goToBookings"
        class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-700">
        View My Bookings
    </button>
</template>
