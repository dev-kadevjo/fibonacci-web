<?php

namespace Kadevjo\Fibonacci\Channels;

use NotificationChannels\AppCenter\Exceptions\CouldNotSendNotification;
use NotificationChannels\AppCenter\Events\MessageWasSent;
use NotificationChannels\AppCenter\Events\SendingMessage;
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

        Log::debug($notifiable);

        //$response = [a call to the api of your notification send]

//        if ($response->error) { // replace this by the code need to check for errors
//            throw CouldNotSendNotification::serviceRespondedWithAnError($response);
//        }
    }
}
