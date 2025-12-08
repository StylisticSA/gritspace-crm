<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_usages', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');

            $table->string('type');
            $table->unsignedInteger('amount');

            $table->decimal('total_cost', 10, 2)->nullable();

            $table->date('date')->nullable();

            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_usages');
    }
};
