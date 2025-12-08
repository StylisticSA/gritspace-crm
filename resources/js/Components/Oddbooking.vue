<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, computed, ref } from 'vue';
import Calendar from 'primevue/calendar';

const props = defineProps({
    bookableType: String,
    bookableId: Number,
    pricingOptions: Object,
    availablePlans: Array,
    buttonName: String,
    selectedPlan: String,
});

const dailyPlans = ['daily'];
const today = new Date();

const form = useForm({
    bookable_type: props.bookableType,
    bookable_id: props.bookableId,
    plan: props.selectedPlan || props.availablePlans[0] || '',
    selected_dates: [],
    start_date: '',
    end_date: '',
    months: 1,
    selected_price: 0,
});

const unitPrice = computed(() => props.pricingOptions[form.plan] || 0);

const weekdaysCount = computed(() => {
    return form.selected_dates.filter(date => {
        const day = new Date(date).getDay();
        return day !== 0 && day !== 6;
    }).length;
});

const calculateEndDateFromMonths = (startDateStr, months) => {
    const date = new Date(startDateStr);
    date.setMonth(date.getMonth() + parseInt(months));
    return date.toISOString().split('T')[0];
};

// Plan change reset
watch(
    () => form.plan,
    () => {
        form.selected_dates = [];
        form.start_date = '';
        form.end_date = '';
        form.months = 1;
        form.selected_price = 0;
    }
);

// Recalculate price
watch(
    [() => form.plan, () => form.selected_dates, () => form.start_date, () => form.months],
    () => {
        if (!form.plan) return;

        if (dailyPlans.includes(form.plan)) {
            form.selected_price = unitPrice.value * weekdaysCount.value;
        } else {
            if (!form.start_date) return;
            form.end_date = calculateEndDateFromMonths(form.start_date, form.months);
            form.selected_price = unitPrice.value * form.months;
        }
    },
    { immediate: true }
);

const submit = () => {
    form.post(route('bookings.store'));
};
</script>

<template>
    <form
        @submit.prevent="submit"
        class="space-y-4">
        <!-- Plan -->
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
        </div>

        <!-- DAILY PLAN -->
        <div
            v-if="dailyPlans.includes(form.plan)"
            class="space-y-4">
            <div>
                <label class="block font-semibold">Select Dates</label>
                <Calendar
                    v-model="form.selected_dates"
                    selectionMode="multiple"
                    :minDate="today"
                    dateFormat="yy-mm-dd"
                    showIcon
                    class="w-full" />
            </div>
            <div>
                <label class="block font-semibold">Weekdays Selected</label>
                <div class="px-3 py-2 border rounded bg-gray-50">
                    {{ weekdaysCount }} {{ weekdaysCount === 1 ? 'day' : 'days' }}
                </div>
            </div>
        </div>

        <!-- MONTHLY/OTHERS -->
        <div
            v-else
            class="space-y-4">
            <div>
                <label class="block font-semibold">Start Date</label>
                <Calendar
                    v-model="form.start_date"
                    :minDate="today"
                    dateFormat="yy-mm-dd"
                    showIcon
                    class="w-full" />
            </div>
            <div>
                <label class="block font-semibold">Months</label>
                <input
                    type="number"
                    v-model="form.months"
                    min="1"
                    class="w-full px-3 py-2 border rounded"
                    required />
            </div>
            <div>
                <label class="block font-semibold">End Date</label>
                <input
                    type="date"
                    :value="form.end_date"
                    class="w-full px-3 py-2 bg-gray-100 border rounded"
                    readonly />
            </div>
        </div>

        <!-- PRICE -->
        <div>
            <label class="block font-semibold">Total Price</label>
            <input
                type="number"
                v-model="form.selected_price"
                class="w-full px-3 py-2 bg-gray-100 border rounded"
                readonly />
        </div>

        <!-- SUBMIT -->
        <div>
            <button
                type="submit"
                class="px-4 py-2 text-white bg-pink-600 rounded hover:bg-pink-700">
                Book {{ buttonName }}
            </button>
        </div>
    </form>
</template>
