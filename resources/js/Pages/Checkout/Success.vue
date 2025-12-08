<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    message: String,
    transactionId: String,
    sessionId: String,
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Payment Success" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Payment Result</h2>
        </template>

        <div class="flex items-center justify-center min-h-[600px] px-4 bg-gray-50">
            <div class="w-full max-w-xl p-5 text-center bg-white rounded-lg shadow">
                <h1 class="mb-4 text-xl font-medium text-primary">
                    {{ message }}
                </h1>

                <div class="p-6 bg-gray-100 rounded-lg shadow">
                    <p class="mb-2 text-lg font-semibold">Transaction Details</p>
                    <p><strong>Transaction ID:</strong> {{ transactionId }}</p>
                    <p><strong>Session ID:</strong> {{ sessionId }}</p>
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
