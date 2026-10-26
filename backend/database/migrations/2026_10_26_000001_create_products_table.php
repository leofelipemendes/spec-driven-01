<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
            $table->decimal('price', 12, 2);
            $table->string('category', 255);
            $table->unsignedInteger('quantity');
            $table->string('seed_key')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
            $table->index('price');
            $table->index('quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
