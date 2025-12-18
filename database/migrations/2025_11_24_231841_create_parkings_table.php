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
        Schema::create('parkings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->string('code');

            $table->string('name');

            $table->decimal('price', 10, 2);

            $table->boolean('is_available')->nullable(false);
            $table->date('available_dates')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parkings');
    }
};
