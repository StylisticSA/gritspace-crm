<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

</script>

<template>
    <GuestLayout>
        <Head title="Pricing" />
        
        <!-- Header Section -->
        <section class="w-full bg-center bg-cover" style="background-image: url('/files_grits/grit_services.jpg');">
            <div class="py-24 text-center">
                <h1 class="text-4xl font-bold text-white">Offices</h1>
            </div>
        </section>

        <section class="w-full py-12">
             <!-- Headings -->
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-3xl font-bold">The Modern Spaces Converted For Digital Innovations</h2>
                <p class="max-w-2xl mx-auto text-lg text-gray-600">
                Possim sapientem in eos. Cum graeci civibus appareat ne sed omy at usu id illud utamur eleifend. Has sonet tincidunt sit placerat mel nonumy impetus partem deseruise tempor primis singulis
                </p>
            </div>
        
            <div class="container mx-auto">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-3">
                <!-- Standard Plan -->
                <div class="p-6 text-center bg-white rounded-lg shadow-lg">
                    <div class="text-xl font-semibold text-gray-800">Standard</div>
                    <div class="flex items-baseline justify-center my-4">
                    <span class="text-xl font-bold text-gray-900">$</span>
                    <div class="text-5xl font-semibold text-gray-900">29</div>
                    </div>
                    <div class="text-sm text-gray-600">/ Month</div>
                    <a href="#" class="inline-block px-4 py-2 mt-4 text-white bg-blue-500 rounded-lg hover:bg-blue-700">Buy now</a>
                    <div class="mt-6 text-sm text-gray-600">
                    <ul class="space-y-2">
                        <li>Aorem ipsum dolor</li>
                        <li>Tempor incididunts lab dolore</li>
                        <li>Veniam nostrud exercitation</li>
                        <li>Eorem ipsum dolor sit</li>
                    </ul>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="p-6 text-center bg-yellow-100 rounded-lg shadow-lg">
                    <div class="text-xl font-semibold text-gray-800">Premium</div>
                    <div class="flex items-baseline justify-center my-4">
                    <span class="text-xl font-bold text-gray-900">$</span>
                    <div class="text-5xl font-semibold text-gray-900">69</div>
                    </div>
                    <div class="text-sm text-gray-600">/ Month</div>
                    <a href="#" class="inline-block px-4 py-2 mt-4 text-white bg-blue-500 rounded-lg hover:bg-blue-700">Buy now</a>
                    <div class="mt-6 text-sm text-gray-600">
                    <ul class="space-y-2">
                        <li>Aorem ipsum dolor</li>
                        <li>Tempor incididunts lab dolore</li>
                        <li>Veniam nostrud exercitation</li>
                        <li>Eorem ipsum dolor sit</li>
                    </ul>
                    </div>
                </div>

                <!-- Ultimate Plan -->
                <div class="p-6 text-center bg-white rounded-lg shadow-lg">
                    <div class="text-xl font-semibold text-gray-800">Ultimate</div>
                    <div class="flex items-baseline justify-center my-4">
                    <span class="text-xl font-bold text-gray-900">$</span>
                    <div class="text-5xl font-semibold text-gray-900">99</div>
                    </div>
                    <div class="text-sm text-gray-600">/ Month</div>
                    <a href="#" class="inline-block px-4 py-2 mt-4 text-white bg-blue-500 rounded-lg hover:bg-blue-700">Buy now</a>
                    <div class="mt-6 text-sm text-gray-600">
                    <ul class="space-y-2">
                        <li>Aorem ipsum dolor</li>
                        <li>Tempor incididunts lab dolore</li>
                        <li>Veniam nostrud exercitation</li>
                        <li>Eorem ipsum dolor sit</li>
                    </ul>
                    </div>
                </div>
                </div>
            </div>
        </section>

    </GuestLayout>
</template>