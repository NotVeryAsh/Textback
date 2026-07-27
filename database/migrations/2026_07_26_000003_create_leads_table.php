<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('phone');
            $table->string('name')->nullable();
            $table->string('status')->default('texted_back');
            $table->string('source')->default('missed_call');
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'phone']);
            $table->index(['account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
