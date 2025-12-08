<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

</script>

<template>
  <GuestLayout>
    <Head title="Home" />
   
    <section class="w-full px-10 py-24">
      <div class="container mx-auto">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
          <!-- Image Section -->
          <div class="flex items-center justify-center">
            <img src="/files_grits/grit_services.jpg" />
          </div>

          <!-- Text Section -->
          <div class="space-y-6">
            <!-- Icon Section -->
            <div class="flex justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="38" height="35" class="text-red-600">
                <path d="M0 .005C7.711 0 15.422.004 23.133.003c1.451.018 2.917-.083 4.353.19 3.245.556 6.222 2.466 8.132 5.161a12.757 12.757 0 0 1 2.332 6.333c.117 1.714.04 3.46-.454 5.113-.922 3.231-3.175 6.042-6.103 7.656a12.59 12.59 0 0 1-5.984 1.576c-1.863.01-3.725.002-5.587.004-.429.005-.884-.041-1.276.172-.407.206-.653.631-.758 1.065-.616 2.306-1.228 4.616-1.845 6.924-.089.287-.182.618-.458.781-.312.098-.566-.179-.777-.367-3.614-3.64-7.237-7.274-10.845-10.92-.506-.461-.779-1.165-.575-1.84.858-3.322 1.762-6.631 2.637-9.949.12-.454.301-.955.741-1.187.43-.21.925-.186 1.39-.195l17.148.001a2.469 2.469 0 0 1 1.809.796c.605.636.828 1.608.554 2.445-.302 1.015-1.306 1.769-2.363 1.747l-13.123-.001c-.455.01-.926-.044-1.365.106-.42.174-.564.652-.679 1.056-.431 1.6-.844 3.208-1.287 4.806 1.145 1.168 2.306 2.321 3.455 3.486.274.275.492.608.808.841.183-.191.277-.44.342-.694.275-1.002.533-2.008.806-3.011.161-.628.799-1.061 1.433-1.049 2.822-.003 5.645 0 8.467-.002.864-.004 1.738.032 2.592-.126a7.765 7.765 0 0 0 4.755-2.897 7.792 7.792 0 0 0 1.628-4.987 7.79 7.79 0 0 0-1.196-4.411c-1.362-2.192-3.859-3.622-6.435-3.634-6.81.002-13.621-.006-20.432.002C3.307 3.339 1.667 1.66 0 .005z" fill="#DE4313" fill-rule="evenodd"></path>
              </svg>
            </div>

            <!-- Main Heading -->
            <div class="text-center">
              <h2 class="text-3xl font-semibold text-gray-800">The Modern Spaces Converted For Digital Innovations!</h2>
            </div>

            <!-- List Section -->
            <div class="text-center">
              <ul class="space-y-2 text-lg text-gray-600 list-disc list-inside">
                <li>Graeci civibus appareat omy impetus</li>
                <li>Eud utamur eleifend sonet tincid</li>
                <li>Kil sit placerat nonumy partem desruisy</li>
              </ul>
            </div>

            <!-- Subheading Section -->
            <div class="hidden text-center md:block">
              <h3 class="text-xl font-medium text-gray-700">We help freelancers & companies find their perfect desk or space</h3>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="w-full py-12">
      <div class="container mx-auto">
        <!-- Header Section -->
        <div class="mb-12 space-y-6 text-center">
          <h2 class="text-3xl font-semibold text-gray-800">What Picozo Offers</h2>
          <h3 class="text-xl font-medium text-gray-700">Offering Amenities In Our CoWorking Spaces</h3>
        </div>

        <!-- Icon List Section -->
        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
          <div class="flex items-start space-x-4">
            <i class="text-2xl text-orange-500 fi0e65- fi-0e65-icon1"></i>
            <div>
              <div class="font-semibold">Fast 5G Internet</div>
              <p class="text-sm text-gray-600">Nostro putant laboramusy per facilis eurpidiad prompta vocibus molestiae est labitur equidem has eum.</p>
            </div>
          </div>

          <div class="flex items-start space-x-4">
            <i class="text-2xl text-orange-500 fi0e65- fi-0e65-icon2"></i>
            <div>
              <div class="font-semibold">Office Equipments</div>
              <p class="text-sm text-gray-600">Nostro putant laboramusy per facilis eurpidiad prompta vocibus molestiae est labitur equidem has eum.</p>
            </div>
          </div>

          <div class="flex items-start space-x-4">
            <i class="text-2xl text-orange-500 fi0e65- fi-0e65-icon3"></i>
            <div>
              <div class="font-semibold">Relaxation Spaces</div>
              <p class="text-sm text-gray-600">Nostro putant laboramusy per facilis eurpidiad prompta vocibus molestiae est labitur equidem has eum.</p>
            </div>
          </div>

          <div class="flex items-start space-x-4">
            <i class="text-2xl text-orange-500 fi0e65- fi-0e65-icon4"></i>
            <div>
              <div class="font-semibold">Meeting Rooms</div>
              <p class="text-sm text-gray-600">Nostro putant laboramusy per facilis eurpidiad prompta vocibus molestiae est labitur equidem has eum.</p>
            </div>
          </div>

          <div class="flex items-start space-x-4">
            <i class="text-2xl text-orange-500 fi0e65- fi-0e65-icon5"></i>
            <div>
              <div class="font-semibold">Free Car Parking</div>
              <p class="text-sm text-gray-600">Nostro putant laboramusy per facilis eurpidiad prompta vocibus molestiae est labitur equidem has eum.</p>
            </div>
          </div>

          <div class="flex items-start space-x-4">
            <i class="text-2xl text-orange-500 fi0e65- fi-0e65-icon6"></i>
            <div>
              <div class="font-semibold">Kitchen And Bar</div>
              <p class="text-sm text-gray-600">Nostro putant laboramusy per facilis eurpidiad prompta vocibus molestiae est labitur equidem has eum.</p>
            </div>
          </div>
        </div>
      </div>
    </section>




    
  </GuestLayout>
</template>


