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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->boolean('has_company')->default(false);
            $table->string('company_name')->nullable();
            $table->string('company_email')->nullable();
            $table->string('cui')->nullable();
            $table->string('company_address')->nullable();
            $table->string('cnp')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('county');
            $table->string('city');
            $table->string('source')->nullable();
            $table->enum('priority', ['High', 'Medium', 'Low'])->default('Medium');
            $table->enum('status', ['New', 'In Progress', 'Closed', 'Lost'])->default('New');
            $table->date('last_contact')->nullable();
            $table->text('notes')->nullable();
            $table->string('doc_attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
