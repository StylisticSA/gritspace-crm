<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import useStatusMessage from '../../../Composables/useStatusMessage';

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const props = defineProps({
    show: Boolean,
    type: Number,
    onClose: Function,
    can: Object,
});

const boardroomsFree = ref([]);
const boardroomsNormal = ref([]);

console.log('tye', props.type);

// Watch for modal open
watch(
    () => props.show,
    async isOpen => {
        if (isOpen) {
            try {
                if (props.type === 2) {
                    const response_free = await axios.get(route('admin.hours.closed'));
                    boardroomsFree.value = response_free.data.boardrooms ?? [];
                } else if (props.type === 3) {
                    const response_normal = await axios.get(route('admin.boardroom_hours.closed'));
                    boardroomsNormal.value = response_normal.data.boardrooms ?? [];
                } else if (props.type === 1) {
                    const response_free = await axios.get(route('admin.hours.closed'));
                    boardroomsFree.value = response_free.data.boardrooms ?? [];

                    const response_normal = await axios.get(route('admin.boardroom_hours.closed'));
                    boardroomsNormal.value = response_normal.data.boardrooms ?? [];
                }
            } catch (error) {
                console.error('Error fetching boardrooms:', error);
                boardroomsFree.value = [];
                boardroomsNormal.value = [];
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

const activeAccordion = ref(null);

const handleClose = () => {
    props.onClose();
    router.reload({ preserveScroll: true });
};

const openMenu = ref(null);

const toggleMenu = id => {
    openMenu.value = openMenu.value === id ? null : id;
};

const viewDetails = row => {
    openMenu.value = null;
};

const closeNormalBoardroom = id => {
    if (!id) return;

    if (confirm('Are you sure you want to Close?')) {
        router.put(route('admin.normal_hours.update', id), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Office status changed to Approved';
                status.value = 'success';

                setTimeout(() => {
                    // router.reload({ preserveScroll: true });
                    router.visit(route('admin.dashboard'));
                }, 3000);
            },
            onError: () => {
                message.value = 'Failed to change to approved.';
                status.value = 'deleted';
            },
        });
    }

    openMenu.value = null;
};

const closeFreeBoardroom = id => {
    if (!id) return;

    if (confirm('Are you sure you want to Close?')) {
        router.put(route('admin.free_hours.update', id), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Office status changed to Approved';
                status.value = 'success';

                setTimeout(() => {
                    // router.reload({ preserveScroll: true });
                    router.visit(route('admin.dashboard'));
                }, 1500);
            },
            onError: () => {
                message.value = 'Failed to change to approved.';
                status.value = 'deleted';
            },
        });
    }

    openMenu.value = null;
};

document.addEventListener('click', event => {
    // If the click is not inside any element with data-popover
    if (!event.target.closest('[data-popover]')) {
        openMenu.value = null;
    }
});

const visitNormalHour = () => {
    router.visit(route('admin.boardroom_hours.index'));
};

const visitFreeHours = () => {
    router.visit(route('admin.hours.index'));
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto bg-black/60">
        <div class="w-full max-w-4xl max-h-[180vh] overflow-y-auto p-4 bg-white rounded shadow-lg sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 gap-3">
                <!-- Title -->
                <h2 class="text-xl sm:text-2xl sm:mb-10">Occupied Boardrooms</h2>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row sm:space-x-3 gap-2 sm:gap-0 w-full sm:w-auto">
                    <button
                        v-if="props.type === 1"
                        @click="visitNormalHour()"
                        class="w-full sm:w-auto px-4 py-2 text-sm text-white rounded bg-primary">
                        Normal Hours
                    </button>
                    <button
                        v-if="props.type === 1"
                        @click="visitFreeHours()"
                        class="w-full sm:w-auto px-4 py-2 text-sm text-white rounded bg-bluemain">
                        Free Hours
                    </button>
                    <button
                        @click="props.onClose"
                        class="w-full sm:w-auto px-4 py-2 text-sm text-white rounded bg-muted">
                        Close
                    </button>
                </div>
            </div>
            <hr class="my-5 border-gray-300" />

            <template v-if="showMessage">
                <div :class="messageClass">
                    {{ messageText }}
                </div>
            </template>

            <div class="space-y-4">
                <!-- Free Boardrooms Accordion -->
                <div
                    class="border rounded"
                    v-if="boardroomsFree.length > 0 && [1, 2].includes(type)">
                    <button
                        @click="activeAccordion = activeAccordion === 'free' ? null : 'free'"
                        class="w-full text-left px-4 py-2 bg-bluemain text-white font-semibold">
                        {{ activeAccordion === 'free' ? 'Hide Free Boardrooms' : 'Show Free Boardrooms' }}
                    </button>

                    <div
                        v-show="activeAccordion === 'free'"
                        class="p-4">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-3 px-4">Username</th>
                                        <th class="text-left py-3 px-4">Boardrooms</th>
                                        <th class="text-left py-3 px-4 hidden sm:block">Start Date</th>
                                        <th class="text-left py-3 px-4">Status</th>
                                        <th class="text-right py-3 px-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="free in boardroomsFree"
                                        :key="free.id"
                                        class="border-b hover:bg-gray-100">
                                        <td class="py-3 px-4">{{ free.user?.name }}</td>
                                        <td class="py-3 px-4">{{ free.boardroom?.boardroom_name }}</td>
                                        <td class="py-3 px-4 hidden sm:block">{{ formatDateTime(free.start_at) }}</td>
                                        <td class="py-3 px-4">
                                            <span
                                                class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                In Progress
                                            </span>
                                        </td>

                                        <td class="py-3 px-4">
                                            <div
                                                class="flex items-center justify-end gap-2 relative"
                                                data-popover>
                                                <!-- Ellipsis button -->
                                                <button
                                                    @click="toggleMenu(free.id)"
                                                    class="p-1 text-center hover:bg-secondary-100 dark:hover:bg-secondary-700 rounded transition-colors"
                                                    type="button">
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="w-4 h-4 text-center text-secondary-600 dark:text-secondary-400"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <circle
                                                            cx="12"
                                                            cy="12"
                                                            r="1"></circle>
                                                        <circle
                                                            cx="12"
                                                            cy="5"
                                                            r="1"></circle>
                                                        <circle
                                                            cx="12"
                                                            cy="19"
                                                            r="1"></circle>
                                                    </svg>
                                                </button>

                                                <!-- Popover menu -->
                                                <div
                                                    v-if="openMenu === free.id"
                                                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-secondary-800 border border-secondary-200 dark:border-secondary-700 rounded-lg shadow-lg z-10">
                                                    <div class="py-1">
                                                        <button
                                                            @click="closeFreeBoardroom(free.id)"
                                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-secondary-50 dark:hover:bg-secondary-700">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Normal Boardrooms Accordion -->
                <div
                    class="border rounded"
                    v-if="boardroomsNormal.length > 0 && [1, 3].includes(type)">
                    <button
                        @click="activeAccordion = activeAccordion === 'normal' ? null : 'normal'"
                        class="w-full text-left px-4 py-2 bg-primary text-white font-semibold">
                        {{ activeAccordion === 'normal' ? 'Hide Normal Boardrooms' : 'Show Normal Boardrooms' }}
                    </button>

                    <div
                        v-show="activeAccordion === 'normal'"
                        class="p-4">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-3 px-4">Username</th>
                                        <th class="text-left py-3 px-4">Boardrooms</th>
                                        <th class="text-left py-3 px-4">Start Date</th>
                                        <th class="text-left py-3 px-4">Status</th>
                                        <th class="text-left py-3 px-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="closed in boardroomsNormal"
                                        :key="closed.id"
                                        class="border-b hover:bg-gray-100">
                                        <td class="py-3 px-4">{{ closed.user?.name }}</td>
                                        <td class="py-3 px-4">{{ closed.boardroom?.boardroom_name }}</td>
                                        <td class="py-3 px-4">{{ formatDateTime(closed.created_at) }}</td>
                                        <td class="py-3 px-4">
                                            <span
                                                class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                In Progress
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div
                                                class="flex items-center justify-end gap-2 relative"
                                                data-popover>
                                                <!-- Ellipsis button -->
                                                <button
                                                    @click="toggleMenu(closed.id)"
                                                    class="p-1 hover:bg-secondary-100 dark:hover:bg-secondary-700 rounded transition-colors"
                                                    type="button">
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="w-4 h-4 text-secondary-600 dark:text-secondary-400"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <circle
                                                            cx="12"
                                                            cy="12"
                                                            r="1"></circle>
                                                        <circle
                                                            cx="12"
                                                            cy="5"
                                                            r="1"></circle>
                                                        <circle
                                                            cx="12"
                                                            cy="19"
                                                            r="1"></circle>
                                                    </svg>
                                                </button>

                                                <!-- Popover menu -->
                                                <div
                                                    v-if="openMenu === closed.id"
                                                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-secondary-800 border border-secondary-200 dark:border-secondary-700 rounded-lg shadow-lg z-10">
                                                    <div class="py-1">
                                                        <button
                                                            @click="closeNormalBoardroom(closed.id)"
                                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-secondary-50 dark:hover:bg-secondary-700">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-5 border-gray-300" />
            <!-- Submit + Cancel -->
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                <div></div>

                <button
                    @click="handleClose"
                    class="w-full px-4 py-2 text-sm text-white rounded sm:w-auto bg-muted">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>
