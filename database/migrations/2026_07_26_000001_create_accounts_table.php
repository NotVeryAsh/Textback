<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('business_name');
            $table->string('vertical')->default('realtor');

            // The operator's real phone that calls forward to.
            $table->string('operator_cell')->nullable();
            $table->timestamp('operator_cell_verified_at')->nullable();

            // The provisioned Twilio number clients actually dial.
            $table->string('twilio_number')->nullable();
            $table->string('twilio_number_sid')->nullable();

            $table->string('google_review_link')->nullable();

            $table->string('timezone')->default('America/New_York');
            $table->string('quiet_hours_start')->default('21:00');
            $table->string('quiet_hours_end')->default('08:00');

            $table->string('caller_id_mode')->default('lead');

            $table->unsignedInteger('leads_recovered_count')->default(0);

            $table->string('onboarding_step')->default('business');
            $table->boolean('is_live')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
