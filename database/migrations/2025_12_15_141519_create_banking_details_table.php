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
            $table->string('currency', 10)->default('ZAR');
            $table->timestamps();

        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('banking_detail_id')
                  ->nullable()
                  ->constrained('banking_details')
                  ->onDelete('set null');
        });
    

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('banking_details');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['banking_detail_id']);
            $table->dropColumn('banking_detail_id');
        });

        Schema::dropIfExists('banking_details');

    }
};
