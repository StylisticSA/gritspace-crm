<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    error: String,
    transactionId: String,
    sessionId: String,
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Payment Failed" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Payment Result</h2>
        </template>

        <div class="flex items-center justify-center min-h-[calc(100vh-80px)] bg-gray-50 px-4">
            <div class="w-full max-w-xl p-8 text-center bg-white rounded-lg shadow">
                <h1 class="mb-4 text-3xl font-bold text-red-600">Payment Failed</h1>

                <div class="p-6 bg-gray-100 rounded-lg shadow">
                    <p class="mb-2 text-lg font-semibold">Error Details</p>
                    <p>{{ error }}</p>
                    <p v-if="transactionId"><strong>Transaction ID:</strong> {{ transactionId }}</p>
                    <p v-if="sessionId"><strong>Session ID:</strong> {{ sessionId }}</p>
                </div>

                <div class="mt-6">
                    <Link
                        :href="route('booking.offices')"
                        class="text-bluemain hover:underline">
                        Back to Home
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
