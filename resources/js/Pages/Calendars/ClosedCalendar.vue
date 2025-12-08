<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
});

const allBookings = ref(props.events);
</script>

<template>
    <Head title="Closed Calendar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Closed Offices Calendar</h2>
        </template>

        <div class="py-12">
            <div class="px-4 mx-auto max-w-7xl lg:px-8">
                <vue-cal
                    class="vuecal--blue-theme vuecal"
                    style="height: 800px"
                    default-view="month"
                    :disable-views="['years', 'week']"
                    hide-weekends
                    :events="allBookings" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.vuecal__event-time {
    display: none;
}
</style>
