<?php

namespace Boyo\Mobica;

use Illuminate\Notifications\Notification;
use Boyo\Mobica\Exceptions\CouldNotSendMessage;
use Bulglish;

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
     * The message unique id
     *
     * @var string
     */
    public $id = '';
    
    /**
     * The prefix - overwrites global setting
     *
     * @var string
     */
    public $prefix = false;
    
    /**
     * The viber message validity
     *
     * @var int
     */
    public $validity_period_sec = 600;
    
    /**
     * Is viber promotional message
     *
     * @var boolean
     */
	public $is_promotional = 0;
    
    /**
     * @param  string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    /**
     * Use this method to build the json request
     *
     *
     * @return $this
     */
    public function getMessage() {
	    
	    $this->bulglish = !empty(config('services.mobica.bulglish'));
	    
		$this->limitLength = !empty(config('services.mobica.allow_multiple'));
		
		if (!$this->prefix) {
			$this->prefix = config('services.mobica.prefix');
		}
	    	    
		if (empty($this->to)) { 
            throw CouldNotSendMessage::telNotProvided();				
		}
		
		$json = [
			'phone' => [ $this->to ],
		];

	    switch($this->channel) {
		    case 'sms':
				
				$sms_message_processed = $this->getSmsText();
				
		    	$json['sms'] = [
			    	'idd' => $this->id,
			    	'message' => $sms_message_processed,
			    	//'from' => '',
		    	];
		    	
				break;
			case 'viber':
			case 'viber-sms':			
				
				if (empty($this->messageViber)) { 
				    throw CouldNotSendMessage::contentNotProvided();				
				}
				
				$json['viber'] = [
			    	'idd' => $this->id,
			    	'text' => $this->messageViber,
			    	'validity_period_sec' => $this->validity_period_sec,
			    	'is_promotional' => $this->is_promotional,
		    	];
		    	
			    if ($this->channel == 'viber-sms') {
				    
				    $sms_message_processed = $this->getSmsText();
				    
				    $json['viber']['sms_text'] = $sms_message_processed;
				    
			    }
/*
				"image_url":"< valid image url >",
				"button_url":"https://mobica.bg",
				"button_text":"click me",
*/
				
				break;
	    }
	    
	    
	    return $json;
	    
    }
    
    /**
     * Use this method to set custom content in SMS messages
     *
     *
     * @return $this
     */
    public function sms($text = '') {
	    
	    $this->messageSMS = $text;
	    
	    return $this;
	    
    }
    
    /**
     * Use this method to set custom content in Viber messages
     *
     *
     * @return $this
     */
    public function viber($text = '') {
	    
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
    
    /**
     * Get the processed SMS text
     *
     *
     * @return string $text
     */
    public function getSmsText() {
	    	    		    
    	if (empty($this->messageSMS)) { 
		    throw CouldNotSendMessage::contentNotProvided();				
		}
	    
	    $sms_message_processed = ( $this->prefix ?: '' ) . $this->messageSMS;
		    	
    	if ($this->bulglish) {
			$sms_message_processed = Bulglish::toLatin( $sms_message_processed );
		}
		
		if ($this->limitLength) {
			$sms_message_processed = $this->cutText($sms_message_processed);
		}

		return $sms_message_processed;
		
    }
    
    /**
     * Cut text to limit of 160 characters 
     *
     * @param  string $text
     *
     * @return $text
     */
	private function cutText($text) {
	        
		if (mb_strlen($text) > 160) {
		
			$text = mb_substr($text, 0, 157);
			$text .= '...';    
			
		}
		
        return $text;
	}
}