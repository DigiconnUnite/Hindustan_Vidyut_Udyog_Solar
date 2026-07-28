<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installation_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('address');
            $table->string('city', 100)->nullable();
            $table->decimal('system_capacity_kw', 6, 2)->nullable();
            $table->enum('current_stage', [
                'lead', 'site_survey', 'quotation', 'agreement', 'installation', 'inspection', 'handover',
            ])->default('lead');
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('current_stage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installation_jobs');
    }
};
