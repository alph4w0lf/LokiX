<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFailureMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failure_messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('scan_id')->unique()->references('id')->on('scans')->constrained()->onDelete('cascade');
            $table->string('reason', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('failure_messages');
    }
}
