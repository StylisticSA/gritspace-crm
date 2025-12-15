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
        Schema::create('boardrooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->string('boardroom_name');
            $table->integer('seats');
            $table->decimal('hourly_price', 8, 2);
            $table->decimal('daily_price', 8, 2);

            $table->boolean('is_available')->nullable(false);

            $table->date('available_dates')->nullable();


            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boardrooms');
    }
};
