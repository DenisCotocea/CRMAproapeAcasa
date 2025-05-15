<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('olx_scraped_properties', function (Blueprint $table) {
            // Add unsigned big integer for the foreign key (adjust type if your properties table uses something else)
            $table->unsignedBigInteger('property_id')->nullable()->after('id');

            // Add foreign key constraint assuming your properties table is 'properties' with primary key 'id'
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('olx_scraped_properties', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropColumn('property_id');
        });
    }

};
