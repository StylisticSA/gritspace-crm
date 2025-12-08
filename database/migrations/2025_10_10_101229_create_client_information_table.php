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
        Schema::create('client_information', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('surname');
            $table->string('cell_number');
            $table->string('email_address');

            $table->string('company_name')->nullable();
            $table->string('company_registration_number')->nullable();

            $table->string('company_reg_path')->nullable();
            $table->string('identity_path')->nullable();
            $table->string('residency_path')->nullable();

            $table->boolean('agreement')->default(false);


            $table->boolean('approved')->default(false);


            $table->timestamps();

            $table->softDeletes();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_information');
    }
};
