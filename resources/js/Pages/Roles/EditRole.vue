<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    role: Object,
    permissions: Array,
});

const isAllChecked = computed(() => {
    return props.permissions.length > 0 && form.permissions.length === props.permissions.length;
});

function toggleAllPermissions(event) {
    form.permissions = event.target.checked ? props.permissions.map(p => p.name) : [];
}

const form = useForm({
    name: props.role.name,
    permissions: props.role.permissions.map(p => p.name),
});

const submit = () => {
    form.put(route('admin.role.update', props.role.id), {
        onSuccess: () => {
            // router.visit(route('admin.roles'));
            router.visit(route('admin.roles'), { replace: true });
        },
    });
};
</script>

<template>
    <Head title="Edit Roles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Roles</h2>
        </template>

        <div class="p-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Edit Roles</h3>

                        <Link
                            :href="route('admin.roles')"
                            class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-3">
                            <!-- Name -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Role Name</label>
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

                        <!-- Pemissions-->
                        <div>
                            <div class="flex items-center justify-between mb-5">
                                <label class="block mb-2 text-lg font-medium text-gray-700">Assign Permissions</label>
                                <div class="">
                                    <span class="pr-2"> Check All</span>
                                    <input
                                        type="checkbox"
                                        :checked="isAllChecked"
                                        @change="toggleAllPermissions"
                                        class="border-gray-300 rounded shadow-sm text-primary focus:ring-bluemain/60" />
                                </div>
                            </div>

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
                                Update Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
