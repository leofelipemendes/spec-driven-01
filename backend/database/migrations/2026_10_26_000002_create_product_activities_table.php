<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('activity_type', 16);
            $table->timestamp('created_at');
            $table->index(['product_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_activities');
    }
};
