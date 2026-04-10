<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    user: Object,
    roles: Array,
    permissions: Array,
    selectedRoles: Array,
});
console.log('user', props.user);
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    user_type: props.user.user_type,
    password: null,
    password_confirmation: '',
    roles: props.selectedRoles,
});

const visiblePermissions = ref([]);

watch(
    () => form.roles,
    selectedRoles => {
        const perms = new Set();

        props.roles.forEach(role => {
            if (selectedRoles.includes(role.name) && Array.isArray(role.permissions)) {
                role.permissions.forEach(p => perms.add(p.name));
            }
        });

        visiblePermissions.value = [...perms];
    },
    { immediate: true }
);
</script>

<template>
    <Head title="Edit User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">User</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-2xl font-medium text-black">Edit User</h3>
                        <Link
                            :href="route('admin.manage.user')"
                            class="px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="form.put(route('admin.manage.update', props.user.id))"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Name</label>
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
                                <label class="block text-lg font-medium text-gray-700">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.email"
                                    class="text-sm text-red-600">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">New Password (optional)</label>

                                <input
                                    v-model="form.password"
                                    type="password"
                                    autocomplete="new-password"
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
                                    autocomplete="new-password"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.password_confirmation"
                                    class="text-sm text-red-600">
                                    {{ form.errors.password_confirmation }}
                                </div>
                            </div>

                            <!-- user type -->
                            <div>
                                <label class="block text-lg text-gray-700">User Type</label>
                                <select
                                    v-model="form.user_type"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select User Type</option>
                                    <option value="new">New user</option>
                                    <option value="existing">Existing user</option>
                                </select>
                                <div
                                    v-if="form.errors.user_type"
                                    class="text-sm text-red-600">
                                    {{ form.errors.user_type }}
                                </div>
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

                        <!-- Permissions (Read-Only Display) -->
                        <div>
                            <label class="block mb-2 text-lg font-medium text-gray-700">Assign Permissions</label>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                <div
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        :checked="visiblePermissions.includes(permission.name)"
                                        disabled
                                        class="border-gray-300 rounded shadow-sm text-primary" />
                                    <span class="text-sm text-bluemain">{{ permission.name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                <span v-if="form.processing">Uploading...</span>
                                <span v-else>Update User</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
