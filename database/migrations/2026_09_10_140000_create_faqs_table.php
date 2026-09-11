<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();

            $table->string('question');
            $table->text('answer');

            /*
             * Free text rather than a fixed list: the questions worth grouping
             * change as the business does, and a lookup table would be one
             * more screen to manage for something that is only a heading.
             * Questions with no category fall under "General".
             */
            $table->string('category')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);

            $table->timestamps();

            /*
             * Deliberately excludes `category`. A VARCHAR(255) under utf8mb4
             * is 1020 bytes on its own, which overruns the 1000-byte key limit
             * some MySQL builds still impose. It buys nothing anyway: the FAQ
             * page reads every published row and groups them in PHP.
             */
            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
