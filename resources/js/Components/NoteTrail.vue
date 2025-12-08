<script setup>
import { format } from 'date-fns';
import { ref } from 'vue';

const expandedNoteId = ref(null);

const props = defineProps({
    notes: {
        type: Array,
        required: true,
    },
    can: Object,
});

const formatDate = dateStr => {
    return dateStr ? format(new Date(dateStr), 'dd MMM yyyy') : '';
};

const truncateText = html => {
    const div = document.createElement('div');
    div.innerHTML = html;
    const text = div.textContent || div.innerText || '';
    const words = text.split(' ').slice(0, 10).join(' ');
    return words + (text.split(' ').length > 10 ? ' ...' : '');
};
</script>

<style scoped>
.border-l {
    border-color: #d1d5db;
}
</style>

<template>
    <div class="relative ml-4 border-l border-gray-300">
        <div
            v-for="note in notes"
            :key="note.id"
            class="relative pl-6 mb-6">
            <div class="absolute w-3 h-3 border border-white rounded-full bg-primary -left-2 top-2"></div>

            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="flex justify-between text-sm text-gray-700">
                    <div v-if="can['manage settings']"><strong>For:</strong> {{ note.user?.name || '—' }}</div>
                    <div><strong>Office:</strong> {{ note.office_name || '—' }}</div>
                </div>

                <!-- Render Quill HTML safely -->
                <div
                    class="my-2 text-gray-800 cursor-pointer"
                    @click="expandedNoteId = expandedNoteId === note.id ? null : note.id">
                    <div
                        v-if="expandedNoteId === note.id"
                        v-html="note.content"></div>
                    <div v-else>{{ truncateText(note.content) }}</div>
                </div>

                <div class="flex justify-between mt-3 text-xs text-gray-500">
                    <div><strong>Created by:</strong> {{ note.created_by }}</div>
                    <div>{{ formatDate(note.created_at) }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
