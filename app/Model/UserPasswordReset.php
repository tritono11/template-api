<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class UserPasswordReset extends Model
{
    protected $fillable = ['i_user_id', 't_ip', 'remember_token'];
}
