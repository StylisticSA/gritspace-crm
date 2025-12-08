<script setup>
import { onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    initiationUrl: String,
});

onMounted(() => {
    if (props.initiationUrl) {
        window.open(props.initiationUrl, '_blank');
    }
});
</script>

<template>
    <Head title="Redirect Page" />

    <AuthenticatedLayout>
        <div class="p-10 my-10 text-center">
            <h1 class="text-xl font-semibold text-gray-700">Redirecting to payment page…</h1>
            <p class="mt-4 text-sm text-gray-500">
                If you're not redirected automatically,
                <a
                    :href="initiationUrl"
                    target="_blank"
                    class="underline text-primary"
                    >click here</a
                >.
            </p>
            <p class="mt-4 text-sm text-gray-500">
                Or
                <a
                    href="/receive/cart"
                    class="underline text-primary"
                    >Go Back</a
                >.
            </p>
        </div>
    </AuthenticatedLayout>
</template>
