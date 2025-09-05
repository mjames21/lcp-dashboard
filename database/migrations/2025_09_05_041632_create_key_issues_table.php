<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('key_issues', function (Blueprint $table) {
            $table->id();

            $table->string('title')->unique();
            $table->text('description')->nullable();

            $table->string('owner')->nullable(); // e.g. "MoF (FDD)", "Local Councils"
            $table->enum('priority', ['low','medium','high'])->default('medium')->index();
            $table->enum('status', ['open','in_progress','blocked','resolved','closed'])->default('open')->index();

            $table->timestamp('opened_at')->nullable()->index();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable()->index();

            $table->string('source')->nullable(); // e.g. "IMC Key Issues"
            $table->string('tags')->nullable();   // e.g. "finance;devolution"

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('key_issues');
    }
};
