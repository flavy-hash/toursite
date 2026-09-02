<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();
            $table->string('name');
            $table->string('tagline')->nullable();

            // Filters the public listing reads.
            $table->string('category')->index();
            $table->string('region')->nullable()->index();
            $table->string('tier')->nullable()->index();
            $table->string('difficulty')->default('Easy');

            $table->string('image')->nullable();
            $table->json('gallery')->nullable();

            $table->string('days');
            $table->string('nights')->nullable();
            $table->string('group')->nullable();
            $table->string('location')->nullable();
            $table->string('best_time')->nullable();
            $table->string('start')->nullable();
            $table->string('end')->nullable();

            $table->decimal('rating', 2, 1)->default(5.0);
            $table->unsignedInteger('reviews')->default(0);

            // Kept as text, not decimal — the site prints "$2,450" verbatim and
            // some packages are quoted "on request".
            $table->string('price');
            $table->string('price_note')->nullable();
            $table->string('highlight')->nullable();

            $table->json('summary')->nullable();
            $table->json('highlights')->nullable();
            $table->json('itinerary')->nullable();
            $table->json('included')->nullable();
            $table->json('excluded')->nullable();

            $table->boolean('is_published')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
