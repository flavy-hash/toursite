<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();
            $table->string('name');

        
            $table->string('type')->index();

            
            $table->string('level')->index();

            $table->string('location')->nullable();
            $table->string('region')->nullable()->index();

            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('description')->nullable();

            
            
            $table->string('price_impact')->nullable();
            $table->string('board_basis')->nullable();

            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('amenities')->nullable();

            $table->string('website')->nullable();

            $table->boolean('is_published')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
