<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_stage_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('installation_jobs')->cascadeOnDelete();
            $table->enum('stage', [
                'lead', 'site_survey', 'quotation', 'agreement', 'installation', 'inspection', 'handover',
            ]);
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('changed_at')->useCurrent();

            $table->index(['job_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_stage_history');
    }
};
