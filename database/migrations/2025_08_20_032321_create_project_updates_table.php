<?php
// File: database/migrations/2025_08_21_120400_create_project_updates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('status', 20)->index(); // planned|ongoing|completed|stalled (validated in app)
            $table->unsignedTinyInteger('progress_percent')->default(0); // 0..100
            $table->decimal('amount_spent', 18, 2)->nullable();
            $table->date('reported_at')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'reported_at'], 'idx_project_updates_project_reported');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_updates');
    }
};

