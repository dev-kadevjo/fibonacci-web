<?php

namespace Kadevjo\Fibonacci\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Kadevjo\Fibonacci\Channels\AppCenterChannel;

class ClientPush extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $name;
    public $title;
    public $body;
    public $custom_data;

    
    public function __construct($new_name = null,$new_title = null,$new_body = null ,$new_custom_data=null)
    {
        $this->name = $new_name;
        $this->title = $new_title;
        $this->body = $new_body;
        $this->custom_data = $new_custom_data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $vias = collect([]);
        foreach($notifiable->channels as $provider)
        {
            $vias->push(config('fibonacci.notification-channel')[$provider]);
        }

        return $vias->all();
        //return [AppCenterChannel::class];
    }

    public function toAppCenter($notifiable)
    {
        // ...
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

}
