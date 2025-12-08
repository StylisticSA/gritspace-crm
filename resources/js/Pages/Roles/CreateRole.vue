<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    permissions: [],
});

const props = defineProps({
    roles: Array,
    permissions: Array,
});
</script>

<template>
    <Head title="Create Roles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Roles</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Add Role</h3>

                        <Link
                            :href="route('admin.roles')"
                            class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="form.post(route('admin.role.store'))"
                        class="space-y-6">
                        <div class="grid grid-cols-1">
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
                        </div>
                        <!-- Premissions-->
                        <div>
                            <label class="block mb-2 text-lg font-medium text-gray-700">Assign Permissions</label>

                            <!-- Grid container for two columns -->
                            <div class="grid items-center grid-cols-2 gap-2 md:grid-cols-4">
                                <div
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        :key="permission.id"
                                        :value="permission.name"
                                        v-model="form.permissions"
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
                                Add Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
