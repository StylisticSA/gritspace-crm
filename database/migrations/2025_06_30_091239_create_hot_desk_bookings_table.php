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

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('helpdesk_id')->nullable()->constrained('help_desks')->nullOnDelete();

            $table->string('plan');
            $table->boolean('is_half_day')->default(false);
            $table->json('selected_dates'); 
            $table->json('time_slots')->nullable(); 
            $table->integer('days_count'); 
            $table->decimal('selected_price', 10, 2); 
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->integer('boardroom_discounted_percent')->nullable()->default(0);

            $table->softDeletes();

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
