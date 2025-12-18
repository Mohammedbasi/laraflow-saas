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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->foreignId('impersonated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('tokenable_id');

            $table->timestamp('impersonation_started_at')->nullable()->after('impersonated_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('impersonated_by_user_id');
            $table->dropColumn('impersonation_started_at');
        });
    }
};
