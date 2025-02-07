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
        Schema::table('borrows', function (Blueprint $table) {
            $table->text('log')->nullable();
        });
        Schema::table('borrow_details', function (Blueprint $table) {
            $table->text('log')->nullable();
        });
        Schema::table('borrow_accessories', function (Blueprint $table) {
            $table->text('log')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn('log');
        });
        Schema::table('borrow_details', function (Blueprint $table) {
            $table->dropColumn('log');
        });
        Schema::table('borrow_accessories', function (Blueprint $table) {
            $table->dropColumn('log');
        });
    }
};
