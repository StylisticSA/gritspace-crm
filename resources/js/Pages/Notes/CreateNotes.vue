<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';
import { useForm, Head, usePage, Link } from '@inertiajs/vue3';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
    users: Array,
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success || null);

const form = useForm({
    user_id: null,
    office_name: null,
    content: '',
    is_visible_to_user: false,
});

const offices = [
    { id: 1, name: 'Closed Office' },
    { id: 2, name: 'Dedicated Desk' },
    { id: 3, name: 'Virtual Office' },
    { id: 4, name: 'Boardroom' },
    { id: 5, name: 'Hot Desk' },
];

const submit = () => {
    form.post(route('admin.notes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Notes Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold leading-tight text-gray-800">Notes</h2>
            </div>
        </template>

        <div class="py-2">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-xl font-medium text-black">Add Note</h3>

                        <Link
                            :href="route('admin.notes.index')"
                            class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <div
                        v-if="successMessage"
                        class="p-4 text-green-700 bg-green-100 border border-green-300 rounded">
                        {{ successMessage }}
                    </div>

                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-lg font-medium text-gray-700">User</label>
                                <select
                                    v-model="form.user_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select User</option>
                                    <option
                                        v-for="user in users"
                                        :key="user.id"
                                        :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <div
                                    v-on:focus="form.clearErrors('user_id')"
                                    v-if="form.errors.user_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.user_id }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg font-medium text-gray-700">Office</label>
                                <select
                                    v-model="form.office_name"
                                    v-on:focus="form.clearErrors('office_name')"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Office</option>
                                    <option
                                        v-for="office in offices"
                                        :key="office.id"
                                        :value="office.name">
                                        {{ office.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.office_name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.office_name }}
                                </div>
                            </div>
                        </div>
                        <!-- Content -->

                        <div>
                            <label class="block text-lg font-medium text-gray-700">Note</label>
                            <QuillEditor
                                v-model:content="form.content"
                                content-type="html"
                                @focus="form.clearErrors('content')"
                                class="bg-white border rounded"
                                style="min-height: 150px" />
                            <div
                                v-if="form.errors.content"
                                class="text-sm text-red-600">
                                {{ form.errors.content }}
                            </div>
                        </div>

                        <!-- Visibility -->
                        <div>
                            <input
                                type="checkbox"
                                v-model="form.is_visible_to_user"
                                id="visibility" />
                            <label
                                for="visibility"
                                class="pl-3 space-x-3 text-lg font-medium text-gray-700"
                                >Visible to user</label
                            >
                        </div>

                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Add Note
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
