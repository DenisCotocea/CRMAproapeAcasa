<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('promoted')->default(false);
            $table->string('type');
            $table->string('category');
            $table->string('tranzaction');
            $table->integer('room_numbers')->nullable();
            $table->string('level')->nullable();
            $table->integer('floor')->nullable();
            $table->integer('total_floors')->nullable();
            $table->double('surface')->nullable();
            $table->integer('construction_year')->nullable();
            $table->string('county');
            $table->string('city');
            $table->string('address');
            $table->decimal('price', 15, 2);
            $table->text('description')->nullable();
            $table->text('details')->nullable();
            $table->string('partitioning')->nullable();
            $table->string('comfort')->nullable();
            $table->string('furnished')->nullable();
            $table->string('heating')->nullable();
            $table->boolean('balcony')->default(false);
            $table->boolean('garage')->default(false);
            $table->boolean('elevator')->default(false);
            $table->boolean('parking')->default(false);
            $table->string('availability_status')->default('Disponibil');
            $table->date('available_from')->nullable();
            $table->foreignId('locked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->string('name')->nullable();
            $table->decimal('usable_area', 8, 2)->nullable();

            $table->decimal('land_area', 8, 2)->nullable();
            $table->decimal('yard_area', 8, 2)->nullable();
            $table->decimal('balcony_area', 8, 2)->nullable();
            $table->string('interior_condition')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
