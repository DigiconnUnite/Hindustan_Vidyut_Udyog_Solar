<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('installation_jobs')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('file_path');
            $table->enum('document_type', ['site_photo', 'agreement', 'subsidy_paper', 'completion_photo', 'other']);
            $table->string('description')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();

            $table->index(['job_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_documents');
    }
};
