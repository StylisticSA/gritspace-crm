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

        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');
            $table->string('transaction_id')->nullable();
            $table->string('plan')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('ZA');
            $table->string('status')->default('pending');
            $table->text('assurance_data')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
