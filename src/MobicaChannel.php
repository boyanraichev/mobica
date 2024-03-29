<?php

namespace Boyo\Mobica;

use Illuminate\Notifications\Notification;
use Boyo\Mobica\MobicaSender;
use Boyo\Mobica\MobicaMessage;

class MobicaChannel
{
	
    protected $client;
    
    public function __construct()
    {

    }
    
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification) : void
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