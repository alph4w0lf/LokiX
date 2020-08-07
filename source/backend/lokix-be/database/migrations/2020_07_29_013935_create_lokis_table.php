<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLokisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lokis', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->boolean('ready');
            $table->boolean('updating');
            $table->boolean('appended');
            $table->timestamp('last_update')->nullable();
            $table->string('error', 191)->nullable();
            $table->string('token', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lokis');
    }
}
