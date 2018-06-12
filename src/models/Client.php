<?php

namespace Kadevjo\Fibonacci\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Kadevjo\Fibonacci\Traits\HasImageTrait;

class Client extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable, HasImageTrait;

    protected $table="client";
    protected $images = ["avatar"];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}
