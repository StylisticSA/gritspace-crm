<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="flex flex-col items-center justify-center h-[600px] mx-auto overflow-hidden px-4">
            <!-- Message -->
            <div class="text-sm text-gray-600 text-center w-full max-w-lg">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the
                link we just emailed to you? If you didn't receive the email, we will gladly send you another.
            </div>

            <!-- Success message -->
            <div
                class="mt-4 text-sm font-medium text-green-600 text-center w-full max-w-md"
                v-if="verificationLinkSent">
                A new verification link has been sent to the email address you provided during registration.
            </div>

            <!-- Form -->
            <form
                @submit.prevent="submit"
                class="w-full max-w-md mt-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <PrimaryButton
                        class="w-full sm:w-auto"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing">
                        Resend Verification Email
                    </PrimaryButton>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-1 text-sm font-semibold tracking-widest text-white transition duration-150 ease-in-out border border-transparent rounded bg-primary hover:bg-primary/60 focus:bg-bluemain focus:outline-none focus:ring-2 focus:ring-bluemain focus:ring-offset-2 active:bg-bluemain/60">
                        Log Out
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
