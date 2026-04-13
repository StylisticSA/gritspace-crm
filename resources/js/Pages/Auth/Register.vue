<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { ref } from 'vue';

const form = useForm({
    user_type: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(true);
const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="flex items-center justify-center min-h-[85vh] px-4">
            <div class="w-full max-w-md p-6 rounded-lg shadow-md bg-gray-50">
                <!-- Logo centered -->
                <div class="flex justify-center mb-10">
                    <Link :href="route('register')">
                        <ApplicationLogo class="block w-auto h-12 text-gray-800 fill-current" />
                    </Link>
                </div>

                <!-- Form starts -->
                <form
                    @submit.prevent="submit"
                    class="space-y-4">
                    <hr />

                    <!-- User Type -->
                    <div>
                        <InputLabel
                            for="user_type"
                            value="User Type" />
                        <div class="flex space-x-6 mt-1">
                            <label class="flex items-center w-full border rounded px-3 py-2 cursor-pointer">
                                <input
                                    type="radio"
                                    name="user_type"
                                    value="new"
                                    v-model="form.user_type"
                                    class="form-radio text-primary"
                                    required />
                                <span class="ml-2">New User</span>
                            </label>
                            <label class="flex items-center w-full border rounded px-3 py-2 cursor-pointer">
                                <input
                                    type="radio"
                                    name="user_type"
                                    value="existing"
                                    v-model="form.user_type"
                                    class="form-radio text-primary" />
                                <span class="ml-2">Existing User</span>
                            </label>
                        </div>
                        <InputError
                            class="mt-2"
                            :message="form.errors.user_type" />
                    </div>
                    <hr />
                    <!-- Name -->
                    <div>
                        <InputLabel
                            for="name"
                            value="Name" />
                        <TextInput
                            id="name"
                            type="text"
                            class="block w-full mt-1"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name" />
                        <InputError
                            class="mt-2"
                            :message="form.errors.name" />
                    </div>

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
                            autocomplete="new-password" />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <InputLabel
                            for="password_confirmation"
                            value="Confirm Password" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="block w-full mt-1"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password" />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Bottom links + submit -->
                    <div class="flex items-center justify-between pt-2">
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center px-2 py-1 text-xs font-normal tracking-widest transition duration-150 ease-in-out border border-transparent text-bluemain round focus:text-bluemain/60">
                            Already registered?
                        </Link>

                        <PrimaryButton
                            class="ms-4"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing">
                            Register
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
