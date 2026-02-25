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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('help_desk_id')->nullable()->constrained('help_desks')->nullOnDelete();
            $table->foreignId('virtual_office_id')->nullable()->constrained('virtual_offices')->nullOnDelete();
            
            $table->string('name')->index();
            $table->string('office_type');
            $table->unsignedInteger('discount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
