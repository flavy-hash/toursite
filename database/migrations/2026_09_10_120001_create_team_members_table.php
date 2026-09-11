<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('role')->nullable();
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();

            // Optional, and only shown when filled — not everyone on the team
            // wants a public address.
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);

            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
