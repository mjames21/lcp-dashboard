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
        Schema::create('governance_events', function (Blueprint $table) {
           $table->id();
            $table->foreignId('council_id')->constrained('location_councils')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('type', 50)->index(); // e.g. council_meeting, audit, policy, training, procurement
            $table->string('status', 20)->index(); // e.g. planned, completed, canceled, postponed
            $table->date('occurred_at')->index();
            $table->string('location', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['council_id', 'occurred_at'], 'idx_governance_events_council_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governance_events');
    }
};
