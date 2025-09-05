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
        Schema::table('key_issues', function (Blueprint $table) {
            //
            /*
                $table->foreignId('council_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('location_councils')
                    ->nullOnDelete()
                    ->index();
                    */
                    //$table->text('severity')->nullable();
                   // $table->text('status')->nullable();
                    $table->date('resolved_at')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('key_issues', function (Blueprint $table) {
            //
            if (Schema::hasColumn('key_issues', 'council_id')) {
                $table->dropConstrainedForeignId('council_id');
            }
        });
    }
};
