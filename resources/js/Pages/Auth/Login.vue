<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-2 text-sm font-medium text-white bg-green-600">
            {{ status }}
        </div>

        <!-- Full-screen centered form -->
        <div class="flex items-center justify-center min-h-[85vh] px-4">
            <div class="w-full max-w-md p-6 rounded-lg shadow-md bg-gray-50">
                <!-- Logo centered with spacing -->
                <div class="flex justify-center mb-10">
                    <Link :href="route('login')">
                        <ApplicationLogo class="block w-auto h-12 text-gray-800 fill-current" />
                    </Link>
                </div>

                <!-- Form -->
                <form
                    @submit.prevent="submit"
                    class="space-y-4">
                    <!-- Email -->
                    <div>
                        <InputLabel
                            for="email"
                            value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full mt-1"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username" />
                        <InputError
                            class="mt-2"
                            :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <InputLabel
                            for="password"
                            value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            class="block w-full mt-1"
                            v-model="form.password"
                            required
                            autocomplete="current-password" />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password" />
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="flex items-center justify-between pb-3">
                        <label class="flex items-center">
                            <Checkbox
                                name="remember"
                                v-model:checked="form.remember" />
                            <span class="text-sm text-gray-600 ms-2">Remember me</span>
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-bluemain focus:text-bluemain/60">
                            Forgot your password?
                        </Link>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-between">
                        <Link
                            :href="route('register')"
                            class="items-center px-2 py-1 text-sm text-bluemain focus:text-bluemain/60">
                            Create an account
                        </Link>
                        <PrimaryButton
                            class="ms-4"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing">
                            Log In
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
