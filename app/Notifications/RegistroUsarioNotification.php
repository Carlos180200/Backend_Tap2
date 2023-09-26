<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistroUsarioNotification extends Notification
{
    use Queueable;

    public $name;
    public $lastname;
    public $email;
    public $job;
    public $phone;
    public $remember_token;
    public function __construct($name, $lastname, $email,$job, $phone, $remember_token)
    {
        $this->name = $name;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->job = $job;
        $this->phone = $phone;
        $this->remember_token = $remember_token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->markdown('Mail.NotificationCreate.NotificationUser',[
                        'name' => $this->name,
                        'lastname' => $this->lastname,
                        'email' => $this->email,
                        'job' => $this->job,
                        'phone' => $this->phone,
                        'remember_token' => $this->remember_token,
                    ])
                    ->subject('Registro')
                    ->from('reyesmartinezcarlos2000@hotmail.com', 'Prueba');
    }

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
