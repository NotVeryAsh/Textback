<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequence_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position'); // 0-based order
            // Offset from the enrollment's base time (due date for invoices,
            // enrollment time for nurture). Stored in minutes so future tiers
            // can set arbitrary schedules without code changes.
            $table->unsignedInteger('delay_minutes');
            $table->text('body');
            // Future MMS / PDF support: channel + media are data-driven so a
            // higher tier can attach a PDF invoice without a rebuild.
            $table->string('channel')->default('sms');
            $table->string('media_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sequence_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequence_steps');
    }
};
