<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class User extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'avatar',
        'email',
        'password',
        'activation_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'activation_code',
        'reset_code',
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activation_code' => 'integer',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function access_token(User $user): string
    {
        $userAccessToken = UserAccessToken::create([
            'user_id' => $user->id,
            'access_token' => Str::random(191)
        ]);
        return $userAccessToken->access_token;
    }

    public static function session_user(): User
    {
        return request()->user;
    }

    public function getAvatarAttribute()
    {
        return asset('storage/' . $this->attributes['avatar']);
    }
}
