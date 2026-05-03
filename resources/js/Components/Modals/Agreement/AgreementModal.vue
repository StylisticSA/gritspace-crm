<script setup>
const props = defineProps({
    show: Boolean,
    onClose: Function,
});

const agreementPath = '/files_grits/gritspace_agreement_file.pdf';

function openPDF() {
    window.open(agreementPath, '_blank');
}
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="bg-white w-full max-w-2xl rounded shadow-lg p-6 text-center">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold">Agreement File</h2>
                <button
                    @click="onClose"
                    class="text-gray-500 hover:text-black">
                    ✕
                </button>
            </div>

            <p class="mb-6 text-gray-600">Click the button below to download the agreement file.</p>

            <!-- Trigger the function here -->
            <button
                @click="openPDF"
                class="bg-bluemain text-white px-6 py-2 rounded font-medium hover:bg-bluemain/60">
                Download Agreement File
            </button>
        </div>
    </div>
</template>
