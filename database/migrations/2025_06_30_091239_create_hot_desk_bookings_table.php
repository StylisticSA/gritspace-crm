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
        Schema::create('hot_desk_bookings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id'); 
            $table->unsignedBigInteger('helpdesk_id'); 
            $table->string('plan');
            $table->boolean('is_half_day')->default(false);
            $table->json('selected_dates'); 
            $table->json('time_slots')->nullable(); 
            $table->integer('days_count'); 
            $table->decimal('selected_price', 10, 2); 
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'paid'])->default('pending');
            $table->timestamps();

            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('helpdesk_id')->references('id')->on('help_desks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hot_desk_bookings');
    }
};
