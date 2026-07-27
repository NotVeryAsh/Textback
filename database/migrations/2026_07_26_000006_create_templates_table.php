<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('kind');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['account_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
