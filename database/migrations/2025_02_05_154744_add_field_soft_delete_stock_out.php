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
        Schema::table('stock_out_details', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('stock_out_products', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_out_details', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('stock_out_products', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
