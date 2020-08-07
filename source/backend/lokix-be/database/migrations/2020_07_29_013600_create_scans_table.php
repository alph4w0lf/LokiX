<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scans', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('endpoint_id')->unique()->references('id')->on('endpoints')->constrained()->onDelete('cascade');
            $table->string('state', 9);
            $table->timestamp('scan_start')->nullable();
            $table->timestamp('scan_end')->nullable();
            $table->timestamp('heartbeat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scans');
    }
}
