<?php

namespace Kadevjo\Fibonacci\Channels;


use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Kadevjo\Fibonacci\Helpers\AppCenter;

class AppCenterChannel
{
    public function __construct()
    {
        // Initialisation code here
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \NotificationChannels\:channel_namespace\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        //send notification
        $reflection = new \ReflectionClass(get_class($notifiable));
        return AppCenter::sendNotification($notification->name, $notification->title, $notification->body,$notifiable->routeNotificationForAppCenter('iOS'),$notifiable->routeNotificationForAppCenter('Android'),$reflection->getShortName(),$notification->custom_data);
    }
}
