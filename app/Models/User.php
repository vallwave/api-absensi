<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id'; // Set primary key to 'user_id'

    public $incrementing = false; // Tell Laravel that primary key is not incrementing

    protected $fillable = [
        'user_id',
        'id_role',
        'id_company',
        'user_nik',
        'user_name',
        'user_email',
        'user_position',
        'user_phone',
        'password',
        'user_trello',
        'user_admin',
        'user_skema',
        'user_status',
        'date_of_birth',
        'date_in_company',
        'photo'
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            // Set id_company to a predefined value
            $user->id_company = '4525a9ec-39e5-4b90-b043-1aec92623500';
        });
    }
}
