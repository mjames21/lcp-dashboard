<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metric_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('council_id')->constrained('location_councils')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->decimal('value', 18, 4)->nullable();
            $table->timestamps();

            $table->unique(['council_id','indicator_id','period_start','period_end'], 'ux_metric_values_natkey');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metric_values');
    }
};
