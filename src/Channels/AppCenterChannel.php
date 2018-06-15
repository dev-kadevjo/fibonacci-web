<?php

namespace Kadevjo\Fibonacci\Channels;

//use Illuminate\Notifications\Notification;
use Kadevjo\Fibonacci\Models\Notification;
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
        return AppCenter::sendNotification($notification->name, $notification->title, $notification->body,$notifiable->routeNotificationForAppCenter,$notification->custom_data);
    }
}
