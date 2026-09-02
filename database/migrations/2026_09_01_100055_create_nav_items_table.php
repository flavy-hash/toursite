<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();

            // "header" is the main bar and its mega panels; "bottom" is the
            // phone tab bar. One table, because both are the same idea:
            // an ordered list of labelled links.
            $table->string('location')->default('header')->index();

            $table->string('label');
            $table->string('path');
            $table->string('icon')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();

            // Mega panel. Left empty for a plain link such as Contact.
            $table->string('panel_heading')->nullable();
            $table->text('panel_copy')->nullable();
            $table->string('panel_cta_label')->nullable();
            $table->string('panel_cta_path')->nullable();
            $table->string('panel_image')->nullable();

            // The left-hand rail: [{name, path}, …]
            $table->json('rail')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_items');
    }
};
