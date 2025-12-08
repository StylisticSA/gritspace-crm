<script setup>
import { computed, defineProps, defineEmits } from "vue";

const currentDate = new Date();

const currentYear = currentDate.getFullYear();
const currentMonth = String(currentDate.getMonth() + 1).padStart(2, "0");

const minDate = computed(() => `${currentYear}-${currentMonth}-01`);

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
    <input
        v-bind="$attrs"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        type="date"
        class="w-full px-3 py-2 border rounded"
        :min="minDate" />
</template>
