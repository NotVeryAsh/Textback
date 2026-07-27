<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequence_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sequence_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('phone');
            // Per-enrollment merge values (invoice amount, pay link, due date).
            $table->json('context')->nullable();
            // Time the schedule offsets are measured from.
            $table->timestamp('base_at');
            $table->unsignedInteger('current_step')->default(0);
            $table->string('status')->default('active');
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_run_at']);
            $table->index(['account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequence_enrollments');
    }
};
