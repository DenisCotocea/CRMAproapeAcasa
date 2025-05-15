<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('olx_scraped_properties', function (Blueprint $table) {
            $table->boolean('promoted')->default(false);
        });
    }

    public function down()
    {
        Schema::table('olx_scraped_properties', function (Blueprint $table) {
            $table->dropColumn('promoted');
        });
    }
};
