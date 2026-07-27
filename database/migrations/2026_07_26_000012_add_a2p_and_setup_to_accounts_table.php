<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Voice works instantly; SMS throughput waits on A2P 10DLC. Track it
            // so we can show "SMS pending carrier registration" without blocking
            // onboarding (the big-player approach: register in the background).
            $table->string('a2p_status')->default('not_started')->after('twilio_number_sid');
            // Lets the user dismiss the dashboard setup checklist once done.
            $table->boolean('setup_dismissed')->default(false)->after('is_live');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['a2p_status', 'setup_dismissed']);
        });
    }
};
