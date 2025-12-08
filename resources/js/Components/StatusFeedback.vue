<template>
    <div
        v-if="conflict || success"
        class="space-y-10">
        <div
            v-if="success"
            class="px-4 py-3 text-sm text-green-800 bg-green-100 border border-green-300 rounded">
            {{ success }}
        </div>

        <div
            v-if="conflict"
            class="px-4 py-3 text-sm text-red-700 bg-red-100 border border-red-300 rounded">
            {{ conflict }}
        </div>

        <div
            v-if="errors"
            class="px-4 py-3 text-sm text-red-700 bg-red-100 border border-red-300 rounded">
            {{ errors.client_rate }}
        </div>
    </div>
</template>

<script setup>
defineProps({
    conflict: String,
    success: String,
    errors: String,
});
</script>
