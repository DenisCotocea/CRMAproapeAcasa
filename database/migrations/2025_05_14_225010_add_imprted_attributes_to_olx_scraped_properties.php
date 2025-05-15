<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('olx_scraped_properties', function (Blueprint $table) {
            $table->json('attributes')->nullable()->after('images');
            $table->boolean('imported')->default(false)->after('attributes');
        });
    }

    public function down()
    {
        Schema::table('olx_scraped_properties', function (Blueprint $table) {
            $table->dropColumn(['attributes', 'imported']);
        });
    }
};
