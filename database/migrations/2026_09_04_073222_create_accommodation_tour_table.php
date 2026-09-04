<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodation_tour', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accommodation_id')->constrained()->cascadeOnDelete();

            
            $table->unsignedTinyInteger('nights')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            
            $table->unique(['tour_id', 'accommodation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodation_tour');
    }
};
