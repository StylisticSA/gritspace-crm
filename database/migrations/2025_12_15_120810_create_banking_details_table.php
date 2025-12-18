<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banking_details', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name', 150);
            $table->string('account_holder', 150);
            $table->string('account_number', 50);
            $table->string('branch_code', 50)->nullable();
            $table->string('swift_code', 50)->nullable();
            $table->string('iban', 50)->nullable();
            $table->timestamps();


        });

        
    

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banking_details');


    }
};
