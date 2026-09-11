<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_videos', function (Blueprint $table) {
            $table->id();

            /*
             * Just the eleven-character YouTube id. A pasted watch/share/embed
             * URL is reduced to this on save — see HomeVideo::extractId().
             *
             * Nullable because "nobody has chosen a video yet" is a real state:
             * the row exists so the panel has something to edit, and the
             * homepage skips the section until an id is set.
             */
            $table->string('youtube_id', 32)->nullable();

            // The wording around the player, so the section can be re-themed
            // without a deployment.
            $table->string('eyebrow')->nullable();
            $table->string('heading')->nullable();
            $table->string('video_title')->nullable();
            $table->string('caption')->nullable();

            $table->boolean('is_published')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_videos');
    }
};
