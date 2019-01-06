<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_password_resets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('i_user_id');
            $table->ipAddress('t_ip');
            $table->rememberToken();
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
        Schema::dropIfExists('user_password_resets');
    }
}
