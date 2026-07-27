<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('client_name')->nullable();
            $table->string('phone');
            $table->string('status')->default('queued');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('message_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_requests');
    }
};
