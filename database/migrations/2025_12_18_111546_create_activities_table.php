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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();

            /**
             * Tenant context
             * - NULL for platform-level actions (rare)
             * - set for tenant actions (projects, tasks, impersonation)
             */
            $table->foreignId('tenant_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            /**
             * User who performed the action in the system
             * (during impersonation: this is the impersonated user)
             */
            $table->foreignId('causer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /**
             * Super-admin who initiated impersonation
             * NULL when action is not impersonated
             */
            $table->foreignId('impersonated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /**
             * Polymorphic subject (Project, Task, Invitation, etc.)
             */
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            /**
             * Human-readable description
             * e.g. "Project created", "Impersonation started"
             */
            $table->string('description');

            /**
             * Extra structured context
             * e.g. changed fields, IP, token id, request metadata
             */
            $table->json('meta')->nullable();

            $table->timestamps();

            /*
             |--------------------------------------------------------------------------
             | Indexes (VERY important for audit queries)
             |--------------------------------------------------------------------------
             */
            $table->index(['tenant_id', 'created_at']);
            $table->index(['causer_id', 'created_at']);
            $table->index(['impersonated_by_user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
