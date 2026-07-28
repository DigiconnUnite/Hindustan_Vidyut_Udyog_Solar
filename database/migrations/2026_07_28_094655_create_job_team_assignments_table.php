<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_team_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('installation_jobs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role_on_job', ['lead_technician', 'technician', 'supervising_staff']);
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('removed_at')->nullable();

            $table->index(['job_id', 'user_id']);
            $table->index(['job_id', 'removed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_team_assignments');
    }
};
