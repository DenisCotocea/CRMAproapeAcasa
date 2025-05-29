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
        Schema::create('public_scraped_properties', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('property_id')->nullable()->index();
            $table->string('public_url', 255);
            $table->string('title', 255)->nullable();
            $table->string('county', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->decimal('price', 15, 2);
            $table->text('description')->nullable();
            $table->longText('images')->nullable();
            $table->longText('attributes')->nullable();
            $table->boolean('imported')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('type', 255)->nullable();
            $table->string('transaction', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
