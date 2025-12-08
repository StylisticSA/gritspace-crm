<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    roles: Array, // roles with embedded permissions
    permissions: Array, // all available permissions
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
    permissions: [],
});

// Sync permissions automatically when roles are selected
watch(
    () => form.roles,
    selectedRoles => {
        const selectedPermissions = new Set();

        props.roles.forEach(role => {
            if (selectedRoles.includes(role.name)) {
                role.permissions.forEach(perm => {
                    selectedPermissions.add(perm.name);
                });
            }
        });

        form.permissions = [...selectedPermissions];
    }
);
</script>

<template>
    <Head title="Create User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">User</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Add User</h3>

                        <Link
                            :href="route('admin.manage.user')"
                            class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="form.post(route('admin.manage.store'))"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Username -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Username</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Email Address</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.email"
                                    class="text-sm text-red-600">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Password</label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    required
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.password"
                                    class="text-sm text-red-600">
                                    {{ form.errors.password }}
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Confirm Password</label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    required
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.password_confirmation"
                                    class="text-sm text-red-600">
                                    {{ form.errors.password_confirmation }}
                                </div>
                            </div>

                            <!-- Roles -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Assign Roles</label>
                                <div class="flex mt-5 space-x-2">
                                    <div
                                        v-for="role in roles"
                                        :key="role.id"
                                        class="flex items-center space-x-2">
                                        <input
                                            type="checkbox"
                                            :value="role.name"
                                            v-model="form.roles"
                                            class="border-gray-300 rounded shadow-sm text-primary focus:ring-bluemain/60" />
                                        <span class="text-sm text-bluemain">{{ role.name }}</span>
                                    </div>
                                </div>
                                <div
                                    v-if="form.errors.roles"
                                    class="text-sm text-red-600">
                                    {{ form.errors.roles }}
                                </div>
                            </div>
                        </div>
                        <!-- Permissions -->
                        <div>
                            <label class="block mb-2 text-lg font-medium text-gray-700">Assign Permissions</label>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                <div
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        :value="permission.name"
                                        v-model="form.permissions"
                                        disabled
                                        class="border-gray-300 rounded shadow-sm text-primary focus:ring-bluemain/60" />
                                    <span class="text-sm text-bluemain">{{ permission.name }}</span>
                                </div>
                            </div>

                            <div
                                v-if="form.errors.permissions"
                                class="mt-1 text-sm text-red-600">
                                {{ form.errors.permissions }}
                            </div>
                        </div>

                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Add User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
