<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void
{
    Schema::create('projects', function (Blueprint $table) {
        $table->id();
        $table->foreignId('council_id')
        ->constrained('location_councils')
        ->cascadeOnDelete();
        $table->string('title');
        $table->string('status', 20)->default('ongoing');
        $table->decimal('budget', 18, 2)->nullable();
        $table->timestamps();
    });
}


public function down(): void
{
Schema::dropIfExists('projects');
}
};