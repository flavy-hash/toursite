<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            /*
             * Records that the guest has been told their booking is confirmed.
             *
             * Kept as a timestamp rather than a boolean so staff can see when
             * it went, and so a confirmation is never sent twice if an enquiry
             * is reopened and confirmed again.
             */
            $table->timestamp('confirmation_sent_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn('confirmation_sent_at');
        });
    }
};
