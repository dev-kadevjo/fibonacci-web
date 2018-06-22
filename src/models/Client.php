<?php

namespace Kadevjo\Fibonacci\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Kadevjo\Fibonacci\Traits\HasImageTrait;
use Kadevjo\Fibonacci\Models\NotificationDevice;
use Kadevjo\Fibonacci\Traits\Loggable;


class Client extends Authenticatable implements JWTSubject
{
    use Notifiable, HasImageTrait, Loggable;

    protected $table="client";
    protected $images = ["avatar"];

    public function __construct()
    {
        $this->channels = ['appcenter'];
        $this->first_name = 'User';
        $this->last_name = 'User Last Name';
    }

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

    public function notifications()
    {
        return $this->hasMany('Kadevjo\Fibonacci\Models\Notification');
    }

    public function devices()
    {
        return $this->hasMany('Kadevjo\Fibonacci\Models\NotificationDevice');
    }

    public function routeNotificationForOneSignal()
    {
        return \Kadevjo\Fibonacci\Models\NotificationDevice::where('user_id',$this->id)->where('provider','onesignal')->get()->pluck('device_id');
    }

    public function routeNotificationForAppCenter($device)
    {
        return \Kadevjo\Fibonacci\Models\NotificationDevice::where('user_id',$this->id)->where('provider','appcenter')->where('type',$device)->get()->pluck('device_id');
    }

    public function addDevice($provider,$type,$device_id)
    {
        $device =  new \Kadevjo\Fibonacci\Models\NotificationDevice();
        $device->provider = $provider;
        $device->type = $type;
        $device->device_id = $device_id;
        $device->client_id = $this->id;
        $device->save();
    }

    public function setChannelsAttribute($value){
        $this->attributes['channels'] = ($value) ? implode(',', $value):null;
    }

    public function getChannelsAttribute($value){

        return ($value)?explode(',', $value):null;
    }

}
