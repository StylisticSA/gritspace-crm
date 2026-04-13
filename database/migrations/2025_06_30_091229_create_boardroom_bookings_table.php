<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('boardroom_bookings', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('boardroom_id')->nullable()->constrained('boardrooms')->nullOnDelete();
      
            $table->string('plan');

            $table->json('selected_dates')->nullable();
            $table->json('selected_times')->nullable();
            $table->integer('months')->default(1);

            $table->decimal('selected_price', 10, 2);
            $table->string('status')->default('pending');

            $table->unsignedTinyInteger('discount_percentage')->nullable()->default(0);
            $table->decimal('discounted_price', 10, 2)->nullable()->default(0); 

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boardroom_bookings');
    }
};
