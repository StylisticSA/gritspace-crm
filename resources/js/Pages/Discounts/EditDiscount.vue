<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { watch } from 'vue';
import useStatusMessage from '../../Composables/useStatusMessage';

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const props = defineProps({
    closed: Array,
    dedicated: Array,
    hotdesk: Array,
    virtuals: Array,
    discount: Object, // pass the discount to edit
});

const form = useForm({
    office_id: props.discount.office_id ?? '',
    help_desk_id: props.discount.help_desk_id ?? '',
    virtual_office_id: props.discount.virtual_office_id ?? '',
    discount: props.discount.discount ?? '',
    selectedCategory: props.discount.office_type ?? 'none',
    name: props.discount.name ?? '',
});

const submit = () => {
    form.put(route('admin.discount.update', props.discount.id), {
        onSuccess: () => {
            message.value = 'Discount has been Updated Successfully.';
            status.value = 'success';

            setTimeout(() => {
                router.reload({ preserveScroll: true });
                router.visit(route('admin.discounts.index'));
            }, 2000);
        },
    });
};

watch(
    () => form.selectedCategory,
    () => {
        form.office_id = '';
        form.help_desk_id = '';
        form.virtual_office_id = '';
        form.name = '';
    }
);
</script>

<template>
    <Head title="Create Discount" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Discounts</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Edit Discount</h3>

                        <Link
                            :href="route('admin.discounts.index')"
                            class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <template v-if="showMessage">
                        <div :class="messageClass">
                            {{ messageText }}
                        </div>
                    </template>

                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Radio Buttons -->
                            <label class="block text-lg font-medium text-gray-700">Offices</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        value="none"
                                        v-model="form.selectedCategory" />
                                    <span>None</span>
                                </label>

                                <label class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        value="closed"
                                        v-model="form.selectedCategory" />
                                    <span>Closed Offices</span>
                                </label>

                                <label class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        value="dedicated"
                                        v-model="form.selectedCategory" />
                                    <span>Dedicated Desks</span>
                                </label>

                                <label class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        value="hotdesk"
                                        v-model="form.selectedCategory" />
                                    <span>Hotdesks</span>
                                </label>

                                <label class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        value="virtuals"
                                        v-model="form.selectedCategory" />
                                    <span>Virtual Offices</span>
                                </label>
                            </div>

                            <!-- Office Name Input / Dropdown -->
                            <div>
                                <div
                                    v-if="
                                        form.errors.office_id ||
                                        form.errors.help_desk_id ||
                                        form.errors.virtual_office_id
                                    "
                                    class="text-sm text-red-600 mb-2">
                                    {{
                                        form.errors.office_id ||
                                        form.errors.help_desk_id ||
                                        form.errors.virtual_office_id
                                    }}
                                </div>
                                <label class="block text-lg font-medium text-gray-700">Office Name</label>

                                <!-- None selected -->
                                <input
                                    v-if="form.selectedCategory === 'none'"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />

                                <!-- Closed -->
                                <select
                                    v-else-if="form.selectedCategory === 'closed'"
                                    v-model="form.office_id"
                                    @change="
                                        form.name =
                                            closed.find(o => o.id === form.office_id)?.office_name +
                                            ' (' +
                                            closed.find(o => o.id === form.office_id)?.location?.name +
                                            ')'
                                    "
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Closed Office</option>
                                    <option
                                        v-for="office in closed"
                                        :key="office.id"
                                        :value="office.id">
                                        {{ office.office_name }} ({{ office.location?.name }})
                                    </option>
                                </select>

                                <!-- Dedicated -->
                                <select
                                    v-else-if="form.selectedCategory === 'dedicated'"
                                    v-model="form.office_id"
                                    @change="
                                        form.name =
                                            dedicated.find(o => o.id === form.office_id)?.office_name +
                                            ' (' +
                                            dedicated.find(o => o.id === form.office_id)?.location?.name +
                                            ')'
                                    "
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Dedicated Desk</option>
                                    <option
                                        v-for="office in dedicated"
                                        :key="office.id"
                                        :value="office.id">
                                        {{ office.office_name }} ({{ office.location?.name }})
                                    </option>
                                </select>

                                <!-- Hotdesk -->
                                <select
                                    v-else-if="form.selectedCategory === 'hotdesk'"
                                    v-model="form.help_desk_id"
                                    @change="
                                        form.name =
                                            hotdesk.find(d => d.id === form.help_desk_id)?.help_desk_name +
                                            ' (' +
                                            hotdesk.find(d => d.id === form.help_desk_id)?.location?.name +
                                            ')'
                                    "
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Hotdesk</option>
                                    <option
                                        v-for="desk in hotdesk"
                                        :key="desk.id"
                                        :value="desk.id">
                                        {{ desk.help_desk_name }} ({{ desk.location?.name }})
                                    </option>
                                </select>

                                <!-- Virtual -->
                                <select
                                    v-else-if="form.selectedCategory === 'virtuals'"
                                    v-model="form.virtual_office_id"
                                    @change="
                                        form.name =
                                            virtuals.find(v => v.id === form.virtual_office_id)?.virtualoffice_name +
                                            ' (' +
                                            virtuals.find(v => v.id === form.virtual_office_id)?.location?.name +
                                            ')'
                                    "
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Virtual Office</option>
                                    <option
                                        v-for="virtual in virtuals"
                                        :key="virtual.id"
                                        :value="virtual.id">
                                        {{ virtual.virtualoffice_name }} ({{ virtual.location?.name }})
                                    </option>
                                </select>
                            </div>

                            <!-- Discount -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Discount (%)</label>
                                <input
                                    v-model="form.discount"
                                    type="number"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.discount"
                                    class="text-sm text-red-600">
                                    {{ form.errors.discount }}
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Update Discount
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
