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
        Schema::create('location_chiefdom', function (Blueprint $table) {
            $table->id();
            $table->string('idemis_code');
            $table->unsignedBigInteger('iddistrict');
            $table->unsignedBigInteger('idcouncil');
            $table->string('chiefdomname');
            $table->foreign('iddistrict')->references('id')->on('location_districts');
            $table->foreign('idcouncil')->references('id')->on('location_councils');         
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
         Schema::dropIfExist('location_chiefdom');
    }
};
