<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('location_districts',function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('idregion');
            $table->string('districtname');
            $table->foreign('idregion')->references('id')->on('location_regions');

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
        Schema::dropIfExists('location_districts');
    }
};
