<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            /*
             * Kept separate from `gallery` rather than mixed into it, so the
             * photo field keeps its image editor and thumbnail previews while
             * video gets its own size limit and accepted types. The public
             * page renders both in one grid, videos first.
             */
            $table->json('gallery_videos')->nullable()->after('gallery');
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn('gallery_videos');
        });
    }
};
