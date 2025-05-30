<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('borrow_accessories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_id')->constrained()->onDelete('cascade');
            $table->foreignId('model_id')->constrained('product_models')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->boolean('borrowed')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_accessories');
    }
};
