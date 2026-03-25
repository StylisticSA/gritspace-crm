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
        Schema::create('help_desks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('help_desk_name');
            $table->decimal('price', 8, 2);

            $table->string('duration')->nullable();
            $table->integer('desks')->nullable();
            $table->unsignedInteger('free_boardroom_hours')->nullable();

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
        Schema::dropIfExists('help_desks');
    }
};
