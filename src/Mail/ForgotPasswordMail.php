<?php

namespace Kadevjo\Fibonacci\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $client;
    public $password;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($client, $pass)
    {
        $this->client = $client;
        $this->password = $pass;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        return $this->view('fibonacci::mails.forgotPassword');
    }
}