<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Per-tenant Twilio credentials. Null = use the global env creds.
            // Supports the bridge model where each client runs in their own
            // Twilio account (Direct customer, their own A2P brand/campaign).
            $table->string('twilio_account_sid')->nullable()->after('twilio_number_sid');
            $table->text('twilio_auth_token')->nullable()->after('twilio_account_sid');
            $table->string('twilio_messaging_service_sid')->nullable()->after('twilio_auth_token');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['twilio_account_sid', 'twilio_auth_token', 'twilio_messaging_service_sid']);
        });
    }
};
