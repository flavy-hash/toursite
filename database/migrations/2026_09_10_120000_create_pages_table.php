<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            /*
             * The slug names a route rather than generating one: /about and
             * /about/team are declared in routes/web.php, and a page record
             * supplies their content. Staff edit pages; they do not invent
             * new URLs from the panel, which would 404.
             */
            $table->string('slug')->unique();

            $table->string('title');

            // Header
            $table->string('eyebrow')->nullable();
            $table->string('heading')->nullable();
            $table->text('intro')->nullable();
            $table->string('hero_image')->nullable();

            // Repeating body sections: heading, copy, optional photo.
            $table->json('sections')->nullable();

            // Overrides for the <title> and meta description; both fall back
            // to the page title and intro when left empty.
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
