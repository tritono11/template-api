<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    protected $fillable = ['i_user_id', 't_ip', 'remember_token'];
    
}
