<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRadioProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('radio_programs', function (Blueprint $table) {
            $table->string('station_id');
            $table->string('title');
            $table->string('cast')->nullable();
            $table->string('start');
            $table->string('end');
            $table->text('info')->nullable();
            $table->text('url')->nullable();
            $table->text('image')->nullable();
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
        Schema::dropIfExists('radio_programs');
    }
}