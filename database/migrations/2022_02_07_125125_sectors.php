<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Sectors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->string('main_sector')->nullable();
            $table->string('sector')->nullable();
            $table->string('mda_name')->nullable();
            $table->string('mda_acronym')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('sectors');
    
    }
}
