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
        Schema::create('office_pricings', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->string('pricing_type')->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_pricings');
    }
};
