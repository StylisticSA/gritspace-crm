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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->string('office_name');
            $table->unsignedBigInteger('category_id');
            $table->integer('seats')->nullable();
            $table->decimal('monthly_rate', 8, 2)->nullable();
            $table->decimal('daily_rate', 8, 2)->nullable();
            $table->decimal('price_premium', 8, 2)->nullable();
            $table->decimal('price_standard', 8, 2)->nullable();

            $table->boolean('is_available')->nullable(false);
            $table->date('available_dates')->nullable();

            $table->timestamps();

            // Foreign key relationships
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
