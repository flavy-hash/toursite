<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            // Never shown publicly — kept so a review can be verified or queried.
            $table->string('email')->nullable();
            $table->string('location')->nullable();
            $table->string('photo')->nullable();

            // Which trip they took. The name is denormalised so the review
            // still reads correctly if the package is later renamed or removed.
            $table->string('tour_slug')->nullable()->index();
            $table->string('tour_name')->nullable();

            $table->string('title')->nullable();
            $table->text('body');

            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('rating_guiding')->nullable();
            $table->unsignedTinyInteger('rating_value')->nullable();

            $table->date('travelled_on')->nullable();
            $table->string('source')->default('website');

            /*
             * Off by default. Anything arriving from the public form is held
             * for moderation — publishing unread submissions on a live site
             * invites spam and abuse.
             */
            $table->boolean('is_published')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();

            $table->timestamps();

            $table->index(['is_published', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
