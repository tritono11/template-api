<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogAuthUserEvent extends Model
{
    protected $fillable = ['i_user_id', 't_ip'];
}
