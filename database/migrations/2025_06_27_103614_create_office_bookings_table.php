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
        Schema::create('office_bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('plan');

            $table->json('selected_dates');
            $table->unsignedTinyInteger('weekdays_count')->nullable();

            $table->unsignedTinyInteger('months')->nullable();
            $table->date('start_date');
            $table->date('end_date');

            $table->decimal('total_price', 10, 2)->nullable();
            $table->decimal('parking_price', 10, 2)->nullable()->default(0);
            $table->boolean('parking_availability')->default(false);
            $table->string('status')->default('pending');

            $table->integer('boardroom_discounted_percent')->nullable()->default(0);
   
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_bookings');
    }
};
