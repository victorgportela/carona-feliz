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
        Schema::create('ride_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ride_id')->constrained()->onDelete('cascade');
            $table->foreignId('passenger_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();
            
            $table->unique(['ride_id', 'passenger_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_requests');
    }
};
