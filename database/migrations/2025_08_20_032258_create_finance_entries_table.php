<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('finance_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('council_id')->constrained('location_councils')->cascadeOnDelete();
            $table->string('category', 50);
            $table->string('sub_category', 50)->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 18, 2)->default(0);
            $table->timestamps();

            // Prevent duplicates for same council/period/category
            $table->unique(['council_id','category','sub_category','period_start','period_end'], 'ux_finance_entries_natkey');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_entries');
    }
};
