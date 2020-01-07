<?php

namespace Boyo\Mobica;

use Illuminate\Notifications\Notification;
use Boyo\Mobica\Exceptions\CouldNotSendMessage;

class MobicaMessage
{
	/**
     * The phone number to send the message to
     *
     * @var string
     */
    public $to = '';
    
    /**
     * The delivery channel - default is sms
     *
     * @var string
     */
    public $channel = 'sms';
    
    /**
     * The delivery channels possible - 'sms', 'viber' or 'viber-sms'. Default is 'sms'
     *
     * @var string
     */
    private $allowedChannels = ['sms','viber','viber-sms'];
    
    /**
     * The message content for SMS.
     *
     * @var string
     */
    public $messageSMS = '';
    
    /**
     * The message content for Viber.
     *
     * @var string
     */
    public $messageViber = '';
    
    /**
     * The message id
     *
     * @var string
     */
    public $id = '';
    
    /**
     * The prefix - overwrites global
     *
     * @var string
     */
    public $prefix = false;
    
    /**
     * @param  string $message
     * @param  string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    /**
     * Use this method to build the needed messages
     *
     *
     * @return $this
     */
    public function build() {
	    
	    switch($this->channel) {
		    case 'sms':
		    	$this->buildSMS();
				break;
			case 'viber':
				$this->buildViber();
				break;
			case 'viber-sms':
				$this->buildViber()->buildSMS();
				break;
	    }
	    
	    return $this;
	    
    }
    
    /**
     * Use this method to set custom content in SMS messages
     *
     *
     * @return $this
     */
    public function buildSMS($text = '') {
	    
	    $this->messageSMS = $text;
	    
	    return $this;
	    
    }
    
    /**
     * Use this method to set custom content in Viber messages
     *
     *
     * @return $this
     */
    public function buildViber($text = '') {
	    
	    $this->messageViber = $text;
	    
	    return $this;
	    
    }
    
    /**
     * Set the phone number of the recipient
     *
     * @param  string $to
     *
     * @return $this
     */
    public function to(string $to) {
	    
	    $this->to = $to;
	    
	    return $this;
	    
    }
    
    /**
     * Set the delivery channel 
     *
     * @param  string $channel
     *
     * @return $this
     */
    public function channel(string $channel) {
    	
    	if (!in_array($channel, $this->allowedChannels)) {
		    throw CouldNotSendMessage::unknownChannel();
	    }
	    
        $this->channel = $channel;
        
        return $this;
        
    }
}