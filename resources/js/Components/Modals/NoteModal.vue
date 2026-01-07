<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
    users: Array,
    show: Boolean,
    onClose: Function,
});

const form = useForm({
    user_id: null,
    office_name: null,
    content: '',
    is_visible_to_user: false,
});

const successMessage = ref(null);

const offices = [
    { id: 1, name: 'Closed Offices' },
    { id: 2, name: 'Dedicated Desk' },
    { id: 3, name: 'Virtual Office' },
    { id: 4, name: 'Boardroom' },
    { id: 5, name: 'Hot Desk' },
];

const submit = () => {
    form.post(route('admin.notes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Note created successfully!';
            // form.reset();

            setTimeout(() => {
                props.onClose();
                successMessage.value = null;
            }, 2000);
        },
    });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto bg-black/60">
        <div class="w-full max-w-2xl p-4 bg-white rounded shadow-lg sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-2xl sm:text-2xl">Add Note</h2>

                <button
                    @click="props.onClose"
                    class="text-sm text-green-800 underline">
                    Close
                </button>
            </div>
            <hr class="my-2 border-gray-300" />

            <div
                v-if="successMessage"
                class="px-4 py-2 mb-4 text-green-700 bg-green-100 border border-green-300 rounded">
                {{ successMessage }}
            </div>

            <form
                @submit.prevent="submit"
                class="mt-10 space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium">User</label>
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
                            v-if="form.errors.user_id"
                            class="text-sm text-red-600">
                            {{ form.errors.user_id }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Office</label>
                        <select
                            v-model="form.office_name"
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

                <div>
                    <input
                        type="checkbox"
                        v-model="form.is_visible_to_user"
                        id="visibility" />
                    <label
                        for="visibility"
                        class="pl-2 text-sm font-medium"
                        >Visible to user</label
                    >
                </div>

                <hr class="my-2 border-gray-300" />

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                    <button
                        type="submit"
                        class="w-full px-4 py-2 text-sm text-white bg-green-700 rounded sm:w-auto hover:bg-bluemain/60"
                        :disabled="form.processing">
                        Save Note
                    </button>
                    <div class="flex flex-col items-center space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2">
                        <Link
                            :href="route('admin.dashboard')"
                            class="w-full px-4 py-2 text-sm text-center text-white rounded sm:w-auto bg-slate hover:bg-bluemain/60">
                            Dashboard
                        </Link>

                        <Link
                            :href="route('admin.notes.index')"
                            class="w-full px-4 py-2 text-sm text-center text-white rounded sm:w-auto bg-primary hover:bg-bluemain/60">
                            Go to Notes
                        </Link>
                    </div>

                    <button
                        type="button"
                        @click="props.onClose"
                        class="w-full px-4 py-2 text-sm text-white rounded sm:w-auto bg-muted">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
