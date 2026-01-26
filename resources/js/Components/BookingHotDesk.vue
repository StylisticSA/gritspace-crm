<script setup lang="ts">
import { Inertia } from '@inertiajs/inertia';
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import DatePicker from 'primevue/datepicker';
import StatusFeedback from '@/Components/StatusFeedback.vue';

const props = defineProps({
    hotdeskId: Number,
    pricingOptions: Number,
    availablePlans: String,
    selectedDuration: Number,
    buttonName: String,
});

const today = new Date();
const successMessage = ref(null);
const bookingConflict = ref(null);

const form = useForm({
    hotdesk_id: props.hotdeskId,
    plan: props.availablePlans,
    is_half_day: props.availablePlans.toLowerCase() === 'halfday',
    selected_dates: [],
    time_slots: {},
    days_count: 0,
    selected_price: 0,
});

// Helpers
const getDateKey = date => {
    const d = new Date(date);
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
};

const normalizedPlan = computed(() => props.availablePlans.toLowerCase().replace(/\s+/g, '_'));

const isHalfDay = computed(() => normalizedPlan.value === 'half_day');

const isFixedPackage = computed(() => [5, 10, 20].includes(props.selectedDuration));

const weekdaysOnly = computed(() =>
    form.selected_dates.filter(date => {
        const d = new Date(date);
        return d.getDay() !== 0 && d.getDay() !== 6;
    })
);

// Trim to selectedDuration for package plans
watch(
    () => form.selected_dates,
    newDates => {
        if (!isFixedPackage.value || isHalfDay.value) return;

        const weekdays = newDates.filter(date => {
            const d = new Date(date);
            return d.getDay() !== 0 && d.getDay() !== 6;
        });

        if (weekdays.length > props.selectedDuration) {
            form.selected_dates = weekdays.slice(0, props.selectedDuration);
        }
    },
    { deep: true }
);

// Time slot selection for Half Day
const getTimeBlock = date => form.time_slots[getDateKey(date)]?.block || '';
const setTimeBlock = (date, e) => {
    const key = getDateKey(date);
    form.time_slots[key] = { block: e.target.value };
};

const halfDayCount = computed(() => {
    if (!isHalfDay.value) return 0;
    return weekdaysOnly.value.reduce((count, date) => {
        const key = getDateKey(date);
        return form.time_slots[key]?.block ? count + 1 : count;
    }, 0);
});

const totalPrice = computed(() => {
    const base = props.pricingOptions || 0;

    if (isHalfDay.value) {
        return base * halfDayCount.value;
    }

    if (isFixedPackage.value) {
        return base;
    }

    return weekdaysOnly.value.length * base;
});

const currencyFormatter = new Intl.NumberFormat('en-ZA', {
    style: 'currency',
    currency: 'ZAR',
});

const submit = () => {
    form.selected_price = totalPrice.value;
    form.days_count = form.selected_dates.length;

    form.post(route('bookinghotdesk.store'), {
        preserveScroll: true,
        onError: errors => {
            bookingConflict.value = errors.booking_conflict ?? null;
        },
        onSuccess: () => {
            successMessage.value = 'Hot Desk Booking created successfully!';
            bookingConflict.value = null;

            setTimeout(() => {
                successMessage.value = null;
                Inertia.visit(route('bookinghotdesk.show'));
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
        class="pt-5 space-y-6">
        <!-- Plan -->
        <div>
            <label class="block mb-1 font-semibold">Plan</label>
            <input
                type="text"
                class="w-full px-3 py-2 bg-gray-100 border rounded"
                :value="form.plan"
                readonly />
        </div>

        <!-- Calendar -->
        <div>
            <label class="block font-semibold">
                {{ isHalfDay ? 'Select Half-Day Date(s)' : 'Select Booking Date(s)' }}
            </label>
            <DatePicker
                v-model="form.selected_dates"
                selectionMode="multiple"
                :minDate="today"
                :disabledDays="[0, 6]"
                :manualInput="false"
                dateFormat="yy-mm-dd"
                showIcon
                class="w-full [&>input]:w-full" />
            <div
                v-if="form.errors.selected_dates"
                class="mt-1 text-sm text-red-600">
                {{ form.errors.selected_dates }}
            </div>
        </div>

        <!-- Half Day Slot Picker -->
        <div
            v-if="isHalfDay && weekdaysOnly.length"
            class="space-y-4">
            <div
                v-for="date in weekdaysOnly"
                :key="getDateKey(date)"
                class="p-4 border rounded bg-gray-50">
                <p class="mb-2 text-sm font-semibold">
                    {{ new Date(date).toLocaleDateString() }}
                </p>
                <select
                    class="w-full px-2 py-1 border rounded"
                    :value="getTimeBlock(date)"
                    @change="e => setTimeBlock(date, e)"
                    required>
                    <option
                        disabled
                        value="">
                        Select Half Day
                    </option>
                    <option value="morning">Morning (09:00–12:00)</option>
                    <option value="afternoon">Afternoon (13:00–17:00)</option>
                </select>
            </div>
        </div>

        <!-- Summary -->
        <div>
            <label class="block font-semibold">Selected Days</label>
            <p class="px-3 py-2 border rounded bg-gray-50">{{ weekdaysOnly.length }} day(s)</p>

            <input
                type="hidden"
                v-model="form.days_count" />
            <div
                v-if="form.errors.days_count"
                class="mt-1 text-sm text-red-600">
                {{ form.errors.days_count }}
            </div>
        </div>

        <!-- Price -->
        <div>
            <label class="block font-semibold">Total Price</label>
            <input
                type="text"
                :value="currencyFormatter.format(totalPrice)"
                readonly
                tabindex="-1"
                @focus="e => (e.target as HTMLInputElement).blur()"
                class="w-full px-3 py-2 bg-gray-100 border-0 rounded cursor-default select-none" />
            <div
                v-if="form.errors.days_count"
                class="mt-1 text-sm text-red-600">
                {{ form.errors.days_count }}
            </div>

            <input
                type="hidden"
                v-model="form.selected_price" />
        </div>

        <!-- Submit -->

        <button
            :disabled="form.processing"
            type="submit"
            class="px-4 py-2 text-sm text-white rounded bg-primary hover:bg-bluemain">
            Enquire {{ props.buttonName }}
        </button>
    </form>
</template>
