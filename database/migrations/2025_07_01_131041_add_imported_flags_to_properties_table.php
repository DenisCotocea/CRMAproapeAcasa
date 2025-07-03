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
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('imported_olx')->default(false);
            $table->boolean('imported_imobiliare')->default(false);
            $table->boolean('imported_romimo')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('imported_olx');
            $table->dropColumn('imported_imobiliare');
            $table->dropColumn('imported_romimo');
        });
    }
};
