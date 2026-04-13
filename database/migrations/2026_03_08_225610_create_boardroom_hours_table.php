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
        Schema::create('boardroom_hours', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();      
            $table->foreignId('boardroom_id')->nullable()->constrained('boardrooms')->nullOnDelete();

            $table->string('user_in_office')->nullable();
            $table->string('user_type');
            
            $table->integer('hours_used');
            $table->string('status')->default('in_progress');

            $table->dateTime('start_at')->nullable();
            $table->dateTime('closed_at')->nullable();

               $table->foreignId('user_closed')->nullable()->constrained('users')->nullOnDelete();   

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boardroom_hours');
    }
};
