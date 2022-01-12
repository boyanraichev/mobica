<?php

namespace Boyo\Mobica\Channels;

use Illuminate\Notifications\Notification;
use Boyo\Mobica\MobicaSender;
use Boyo\Mobica\MobicaMessage;

class MobicaSmsChannel
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
	    
        // force SMS sending on this channel 
        $message->channel('sms');
        
        $client = new MobicaSender();
        
        $client->send($message);
        
    }
    
    
}