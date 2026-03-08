<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    onClose: Function,
    can: Object,
});

const boardrooms = ref([]);

// Watch for modal open
watch(
    () => props.show,
    async isOpen => {
        if (isOpen) {
            try {
                const response = await axios.get(route('admin.hours.closed'));
                boardrooms.value = response.data.boardrooms ?? [];
            } catch (error) {
                console.error('Error fetching boardrooms:', error);
                boardrooms.value = [];
            }
        }
    },
    { immediate: true }
);

const formatDateTime = value => {
    if (!value) return '';
    const date = new Date(value);
    return new Intl.DateTimeFormat('en-GB', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto bg-black/60">
        <div class="w-full max-w-4xl max-h-[80vh] overflow-y-auto p-4 bg-white rounded shadow-lg sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-2xl sm:text-2xl">Occupied Boardrooms</h2>

                <button
                    @click="props.onClose"
                    class="text-sm text-green-800 underline">
                    Close
                </button>
            </div>
            <div class="mt-10 space-y-6">
                <!-- Hours Counter + Info -->
                <div>
                    <div class="grid grid-cols-1 gap-6 mt-5">
                        <hr class="my-2 border-gray-300 md:hidden" />

                        <div v-if="boardrooms.length > 0">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-secondary-200 dark:border-secondary-700">
                                            <th
                                                class="text-left py-3 px-4 text-sm font-semibold text-secondary-700 dark:text-secondary-300">
                                                Username
                                            </th>
                                            <th
                                                class="text-left py-3 px-4 text-sm font-semibold text-secondary-700 dark:text-secondary-300">
                                                Boardrooms
                                            </th>
                                            <th
                                                class="text-left py-3 px-4 text-sm font-semibold text-secondary-700 dark:text-secondary-300">
                                                Start Date
                                            </th>
                                            <th
                                                class="text-left py-3 px-4 text-sm font-semibold text-secondary-700 dark:text-secondary-300">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="closed in boardrooms"
                                            :key="closed.id"
                                            class="border-b border-secondary-100 dark:border-secondary-800 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                            <td class="py-3 px-4">
                                                <div>
                                                    <p class="text-sm font-medium text-secondary-900">
                                                        {{ closed.user?.name }}
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="text-sm text-secondary-700 dark:text-secondary-300">{{
                                                    closed.boardroom?.boardroom_name
                                                }}</span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="text-sm text-secondary-700 dark:text-secondary-300">{{
                                                    formatDateTime(closed.created_at)
                                                }}</span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span
                                                    class="px-2 py-1 rounded text-xs font-semibold capitalize bg-yellow-100 text-yellow-800">
                                                    In Progress
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-5 border-gray-300" />

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                    <div></div>
                    <button
                        type="button"
                        @click="props.onClose"
                        class="w-full px-4 py-2 text-sm text-white rounded sm:w-auto bg-muted">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
