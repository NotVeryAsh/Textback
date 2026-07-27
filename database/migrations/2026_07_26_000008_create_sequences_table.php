<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('kind'); // invoice_reminder | nurture
            $table->string('name');
            $table->string('trigger')->default('manual');
            $table->boolean('is_active')->default(true);
            // Future: paid tiers get editable schedules/wording. This flag lets
            // us mark which sequences a plan is allowed to customize.
            $table->boolean('is_editable')->default(false);
            $table->timestamps();

            $table->index(['account_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
