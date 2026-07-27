<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('period'); // YYYY-MM
            $table->unsignedInteger('sms_out')->default(0);
            $table->unsignedInteger('sms_in')->default(0);
            $table->unsignedInteger('call_minutes')->default(0);
            $table->unsignedInteger('leads_recovered')->default(0);
            $table->timestamps();

            $table->unique(['account_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_counters');
    }
};
