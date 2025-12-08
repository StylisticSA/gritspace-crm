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

        Schema::create('client_rates', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->unsignedBigInteger('client_information_id')
                ->nullable();

            $table->string('type')->nullable();
            $table->string('office_name')->nullable();
            $table->unsignedBigInteger('space_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('client_information_id')->references('id')->on('client_information')->onDelete('cascade');

        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_rates');
    }
};
