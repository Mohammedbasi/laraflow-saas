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
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id')->index();

            // report period
            $table->date('week_start')->index();
            $table->date('week_end')->index();

            // status tracking
            $table->string('status')->default('pending'); // pending|stats_ready|pdf_ready|emailed|failed
            $table->json('stats')->nullable();

            // file storage
            $table->string('pdf_disk')->nullable(); // e.g. 'local' or 's3'
            $table->string('pdf_path')->nullable();

            // error/debug
            $table->text('last_error')->nullable();

            $table->timestamps();

            // idempotency: 1 report per tenant per week
            $table->unique(['tenant_id', 'week_start', 'week_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
