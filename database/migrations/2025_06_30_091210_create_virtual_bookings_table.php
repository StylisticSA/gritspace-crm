<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('virtual_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('virtual_office_id')->constrained()->cascadeOnDelete(); 
            $table->string('plan');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedTinyInteger('months')->default(3);
            $table->decimal('selected_price', 10, 2);
            $table->json('selected_dates')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'paid'])->default('pending');
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_bookings');
    }
};
