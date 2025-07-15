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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_type');
            $table->foreignId('agent_id')->constrained('users');
            $table->foreignId('leads')->nullable()->constrained('leads');
            $table->boolean('signed')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->text('signature_agent')->nullable();
            $table->timestamp('agent_signed_at')->nullable();
            $table->text('signature_client')->nullable();
            $table->timestamp('client_signed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
