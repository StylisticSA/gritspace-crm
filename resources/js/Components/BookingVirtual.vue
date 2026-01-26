<script setup>
import { Inertia } from '@inertiajs/inertia';
import { useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import DatePicker from 'primevue/datepicker';
import { format } from 'date-fns';
import StatusFeedback from '@/Components/StatusFeedback.vue';

const props = defineProps({
    virtualId: Number,
    price: Number,
    availablePlans: Array,
    buttonName: String,
    selectedPlan: String,
    bookedRanges: Array,
});

const today = new Date();
const successMessage = ref(null);
const bookingConflict = ref(null);

const form = useForm({
    virtual_office_id: props.virtualId,
    plan: props.selectedPlan || props.availablePlans[0] || '',
    selected_dates: [],
    start_date: null,
    end_date: null,
    months: 3,
    selected_price: 0,
});

const unitPrice = computed(() => props.price || 0);

const updateBookingDates = () => {
    if (!form.start_date || !form.months) {
        form.end_date = null;
        form.selected_dates = [];
        form.selected_price = 0;
        return;
    }

    const start = new Date(form.start_date);
    const end = new Date(start);
    end.setMonth(start.getMonth() + form.months);
    end.setDate(start.getDate());

    form.end_date = format(end, 'yyyy-MM-dd');
    form.selected_price = unitPrice.value * form.months;

    const current = new Date(start);
    const dates = [];

    while (current <= end) {
        dates.push(format(new Date(current), 'yyyy-MM-dd'));
        current.setDate(current.getDate() + 1);
    }

    form.selected_dates = dates;
};

// Watch for changes in start_date, months, or plan
watch([() => form.start_date, () => form.months, () => form.plan], updateBookingDates, {
    immediate: true,
});

const disabledDates = computed(() => {
    if (!Array.isArray(props.bookedRanges)) return [];

    return props.bookedRanges
        .filter(b => b.plan === form.plan)
        .flatMap(b => b.selected_dates)
        .map(dateStr => new Date(dateStr));
});

watch([() => form.start_date, () => form.end_date, () => form.selected_dates, () => form.plan], () => {
    if (bookingConflict.value) bookingConflict.value = null;
});

const currencyFormatter = new Intl.NumberFormat('en-ZA', {
    style: 'currency',
    currency: 'ZAR',
});

const submit = () => {
    form.post(route('bookingvirtual.store'), {
        preserveScroll: true,
        onError: errors => {
            bookingConflict.value = errors.booking_conflict ?? null;
        },
        onSuccess: () => {
            successMessage.value = 'Virtual Booking created successfully!';
            bookingConflict.value = null;

            setTimeout(() => {
                successMessage.value = null;
                Inertia.visit(route('bookingvirtual.show'));
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
        <div>
            <label class="block font-semibold">Plan</label>
            <select
                v-model="form.plan"
                class="w-full px-3 py-2 border rounded"
                required>
                <option
                    v-for="plan in availablePlans"
                    :key="plan"
                    :value="plan">
                    {{ plan.charAt(0).toUpperCase() + plan.slice(1) }}
                </option>
            </select>
            <div
                v-if="form.errors.plan"
                class="mt-1 text-sm text-red-600">
                {{ form.errors.plan }}
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block font-semibold">Start Date</label>
                <DatePicker
                    :modelValue="form.start_date ? new Date(form.start_date) : null"
                    @update:modelValue="val => (form.start_date = format(val, 'yyyy-MM-dd'))"
                    :disabledDates="disabledDates"
                    :minDate="today"
                    :manualInput="false"
                    :disabledDays="[0, 6]"
                    dateFormat="yy-mm-dd"
                    showIcon
                    class="w-full [&>input]:w-full" />
                <div
                    v-if="form.errors.start_date"
                    class="mt-1 text-sm text-red-600">
                    {{ form.errors.start_date }}
                </div>
            </div>

            <div>
                <label class="block font-semibold">Duration (Months)</label>
                <select
                    v-model="form.months"
                    class="w-full px-3 py-2 border rounded"
                    required>
                    <option
                        v-for="month in 10"
                        :key="month"
                        :value="month + 2">
                        {{ month + 2 }} Months
                    </option>
                </select>
                <div
                    v-if="form.errors.months"
                    class="mt-1 text-sm text-red-600">
                    {{ form.errors.months }}
                </div>
            </div>

            <div>
                <label class="block font-semibold">End Date</label>
                <div class="w-full px-3 py-2 text-gray-700 rounded bg-green-50">
                    {{ form.end_date || 'End date will appear here' }}
                </div>
            </div>
        </div>

        <div>
            <label class="block font-semibold">Total Price</label>
            <input
                type="text"
                :value="currencyFormatter.format(form.selected_price)"
                readonly
                tabindex="-1"
                @focus="e => e.target.blur()"
                class="w-full px-3 py-2 border-0 rounded cursor-default select-none bg-green-50" />
            <input
                type="hidden"
                v-model="form.selected_price" />
            <div
                v-if="form.errors.selected_price"
                class="mt-1 text-sm text-red-600">
                {{ form.errors.selected_price }}
            </div>
        </div>

        <div class="space-x-2">
            <button
                :disabled="form.processing"
                type="submit"
                class="px-4 py-2 text-sm text-white rounded bg-primary hover:bg-bluemain">
                Enquire {{ buttonName }}
            </button>
        </div>
    </form>
</template>
