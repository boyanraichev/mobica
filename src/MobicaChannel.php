<?php

namespace Boyo\Mobica;

use Illuminate\Notifications\Notification;
use Boyo\Mobica\MobicaSender;
use Boyo\Mobica\MobicaMessage;

class MobicaChannel
{
	
    protected $client;
    
    /**
     * @param ClickatellClient $clickatell
     */
    public function __construct()
    {

    }
    
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        
        $message = $notification->toSms($notifiable);
        
        if (!$message instanceof MobicaMessage) {
	        throw new \Exception('No message provided');
	    }
	    
	    // run the build functions
	    $message->build();
	    
        $client = new MobicaSender();
        
        $client->send($message);
        
    }
    
    
}