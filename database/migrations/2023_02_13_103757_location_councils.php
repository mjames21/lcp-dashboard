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
        Schema::create('location_councils',function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('iddistrict');
            $table->string('councilname');
            $table->foreign('iddistrict')->references('id')->on('location_districts');

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
        Schema::dropIfExists('location_councils');
    }
};
