<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Timestamp of SMS opt-in (null = did not opt in). Consent is optional
            // at signup per A2P 10DLC rule 30923; this records proof when given.
            $table->timestamp('sms_opt_in_at')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sms_opt_in_at');
        });
    }
};
