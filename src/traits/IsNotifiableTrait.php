<?php

namespace Kadevjo\Fibonacci\Traits;

use Illuminate\Notifications\Notifiable;
use Kadevjo\Fibonacci\Models\NotificationDevice;

trait IsNotifiableTrait
{
    use Notifiable;

    public function notifications()
    {
        return $this->hasMany('Kadevjo\Fibonacci\Models\Notification');
    }

    public function devices()
    {
        return $this->hasMany('Kadevjo\Fibonacci\Models\NotificationDevice');
    }

    public function routeNotificationForOneSignal($model)
    {
        return NotificationDevice::where('client_id',$this->id)->where('provider','onesignal')->where('model',$model)->get()->pluck('device_id');
    }

    public function routeNotificationForAppCenter($device,$model)
    {
        return NotificationDevice::where('client_id',$this->id)->where('provider','appcenter')->where('model',$model)->where('type',$device)->get()->pluck('device_id');
    }

    public function addDevice($provider,$type,$device_id, $model)
    {
        $device = NotificationDevice::where('client_id',$this->id)->where('device_id',$device_id)->where('type',$type)->where('model',$model)->first();
        if($device)
            return $device;

        $device =  new NotificationDevice();
        $device->provider = $provider;
        $device->type = $type;
        $device->device_id = $device_id;
        $device->client_id = $this->id;
        $device->model =  $model;
        $device->save();
        return $device;
    }

    public function setChannelsAttribute($value){
        $this->attributes['channels'] = ($value) ? implode(',', $value):null;
    }

    public function getChannelsAttribute($value){

        return ($value)?explode(',', $value):null;
    }
}
