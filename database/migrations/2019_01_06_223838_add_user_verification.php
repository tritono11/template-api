<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserVerification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Flag per segnare il token utilizzato
        Schema::table('user_verifications', function($table) {
            $table->char('b_used',1)->after('remember_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_verifications', function($table) {
            $table->dropColumn('b_used');
        });
    }
}
