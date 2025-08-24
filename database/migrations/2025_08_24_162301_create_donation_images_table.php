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
        Schema::create('donation_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_item_id')->constrained()->onDelete('cascade');
            $table->string('path');
            $table->timestamps();
            
            // Evita avaliações duplicadas
            $table->unique(['donation_item_id', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_images');
    }
};
