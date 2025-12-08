<script setup>
import { useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import StatusFeedback from '@/Components/StatusFeedback.vue';

const props = defineProps({
    locations: Array,
    categories: Array,
});

const successMessage = ref(null);
const bookingConflict = ref(null);

const form = useForm({
    name: '',
    surname: '',
    cell_number: '',
    email_address: '',
    company_name: '',
    company_registration_number: '',
});

const submit = () => {
    form.post(route('clientinfo.store'), {
        preserveScroll: true,
        onError: errors => {
            bookingConflict.value = errors.booking_conflict ?? null;
        },
        onSuccess: () => {
            successMessage.value = 'Closed Office booked successfully!';
            bookingConflict.value = null;

            setTimeout(() => {
                successMessage.value = null;
                Inertia.visit(route('clientinfo.index'));
            }, 1500);
        },
    });
};
</script>

<template>
    <StatusFeedback
        :conflict="bookingConflict"
        :success="successMessage" />

    <form
        @submit.prevent="submit"
        class="pt-5 space-y-4">
        <!-- User Details -->
        <div class="space-y-4">
            <div>
                <label class="block font-semibold">First Name</label>
                <input
                    type="text"
                    v-model="form.name"
                    required
                    class="w-full px-3 py-2 border rounded" />
            </div>

            <div>
                <label class="block font-semibold">Surname</label>
                <input
                    type="text"
                    v-model="form.surname"
                    required
                    class="w-full px-3 py-2 border rounded" />
            </div>

            <div>
                <label class="block font-semibold">Cell Number</label>
                <input
                    type="text"
                    v-model="form.cell_number"
                    required
                    class="w-full px-3 py-2 border rounded" />
            </div>

            <div>
                <label class="block font-semibold">Email Address</label>
                <input
                    type="email"
                    v-model="form.email_address"
                    required
                    class="w-full px-3 py-2 border rounded" />
            </div>

            <div>
                <label class="block font-semibold">Company Name</label>
                <input
                    type="text"
                    v-model="form.company_name"
                    class="w-full px-3 py-2 border rounded" />
            </div>

            <div class="w-full pt-4 md:col-span-2">
                <button
                    type="submit"
                    class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                    :disabled="form.processing">
                    Add Client
                </button>
            </div>
        </div>
    </form>
</template>
