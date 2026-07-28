<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->enum('roof_type', ['rcc', 'tin_shed', 'tiled', 'other'])->nullable();
            $table->decimal('monthly_bill_estimate', 10, 2)->nullable();
            $table->enum('source', ['website', 'phone', 'walk_in', 'referral', 'other'])->default('website');
            $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'lost'])->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            // converted_job_id -> installation_jobs FK added later (see 2026_07_28_099999) to avoid a circular create-time dependency.
            $table->unsignedBigInteger('converted_job_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
