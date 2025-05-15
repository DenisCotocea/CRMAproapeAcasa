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
        Schema::create('olx_scraped_properties', function (Blueprint $table) {
            $table->id();
            $table->string('olx_id');
            $table->string('olx_url');
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->string('category')->nullable();
            $table->string('tranzaction')->nullable();
            $table->integer('room_numbers')->nullable();
            $table->integer('floor')->nullable();
            $table->integer('total_floors')->nullable();
            $table->decimal('surface', 10, 2)->nullable();
            $table->decimal('usable_area', 10, 2)->nullable();
            $table->decimal('land_area', 10, 2)->nullable();
            $table->decimal('yard_area', 10, 2)->nullable();
            $table->decimal('balcony_area', 10, 2)->nullable();
            $table->integer('construction_year')->nullable();
            $table->string('county')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->decimal('price', 15, 2);
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->string('partitioning')->nullable();
            $table->boolean('furnished')->default(false);
            $table->string('heating')->nullable();
            $table->boolean('balcony')->default(false);
            $table->boolean('garage')->default(false);
            $table->boolean('elevator')->default(false);
            $table->boolean('parking')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olx_scraped_properties');
    }
};
