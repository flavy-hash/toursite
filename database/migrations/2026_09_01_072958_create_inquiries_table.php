<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();

            // Slug is kept even if a package is later renamed or retired, so the
            // enquiry still records what was actually being asked about.
            $table->string('tour_slug')->nullable();
            $table->string('tour_name')->nullable();

            $table->date('travel_date')->nullable();
            $table->unsignedTinyInteger('travellers')->default(1);
            $table->text('message')->nullable();

            $table->string('status')->default('new');
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
