<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            /*
             * A question asked by a visitor arrives with no answer — that is
             * the whole point of the queue — so the column can no longer be
             * required. The admin form still insists on one before publishing.
             */
            $table->text('answer')->nullable()->change();

            // Who asked, when they are willing to say. Both optional: the
            // question is useful even from someone who would rather not.
            $table->string('asked_by_name')->nullable()->after('answer');
            $table->string('asked_by_email')->nullable()->after('asked_by_name');

            // Set only on visitor submissions, so staff can tell a real
            // question apart from one they wrote themselves.
            $table->timestamp('asked_at')->nullable()->after('asked_by_email');
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn(['asked_by_name', 'asked_by_email', 'asked_at']);
            $table->text('answer')->nullable(false)->change();
        });
    }
};
