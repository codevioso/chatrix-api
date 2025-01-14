<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccessToken extends Model
{
    protected $fillable = [
        'user_id',
        'access_token'
    ];

    protected $hidden = [
        'ip_address',
        'created_at',
        'updated_at'
    ];
}
