<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogAuthUserEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_auth_user_events', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('i_user_id');
            $table->ipAddress('t_ip');
            $table->timestamps();
            $table->foreign('i_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_auth_user_events');
    }
}
