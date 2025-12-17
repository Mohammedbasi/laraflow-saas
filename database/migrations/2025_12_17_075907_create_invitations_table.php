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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            $table->string('email');
            $table->string('role')->default('member'); // member or tenant_admin
            $table->string('token')->unique(); // random token stored hashed
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();

            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
