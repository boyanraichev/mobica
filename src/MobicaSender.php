<?php

namespace Boyo\Mobica;

use Boyo\Mobica\Exceptions\CouldNotSendMessage;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Bulglish;

class MobicaSender
{
	private $log = true;
	
	private $log_channel = 'stack';
	
	private $send = false;
	
	private $url = 'https://gate.mobica.bg/send.php';	
	
	private $encoding = 'gsm-03-38';
	
	private $priority = '2';
	
	private $user = '';
	
	private $pass = '';	
	
	private $prefix = '';
	
	private $bulglish = true;
	
	private $limitLength = true;
	
	// construct
	public function __construct() {
		
		// settings
		$this->user = config('services.mobica.user');
		$this->pass = config('services.mobica.pass');		
		$this->prefix = config('services.mobica.prefix');
		$this->log = config('services.mobica.log');
		$this->send = config('services.mobica.send');
		$this->log_channel = config('services.mobica.log_channel');
		$this->bulglish = config('services.mobica.bulglish');
		
		if(!empty(config('services.mobica.allow_multiple'))) {
			$this->limitLength = false;
		}
		
		// setup Guzzle client
		$this->client = new Client(['base_uri' => $this->url]);
		
	}
	
	private function processTel($tel) {
		$tel = preg_replace('/^\+/', '0', $tel);
		$tel = preg_replace('/[^0-9]/', '', $tel);
		$tel = preg_replace('/^00/', '0', $tel);
		$tel = preg_replace('/^0359/', '359', $tel);
		$tel = preg_replace('/^0/', '359', $tel);	
		return $tel;
	}
	
	private function cutText($text) {
	        
		if (mb_strlen($text) > 160) {
		
			$text = mb_substr($text, 0, 157);
			$text .= '...';    
			
		}
		
        return $text;
	}
	
	// check limit
	public function checkLimit() {
		
		$url = $this->url . 'index.php?sid='.$this->sid.'&check_limit=1';
		$response = wp_remote_get( $url ); 
		return $response;
		
	}
	
	// send email
	public function send(MobicaMessage $message) {
		
		try {
			
			$message->build();
			
			if (empty($message->to)) { 
	            throw CouldNotSendMessage::telNotProvided();				
			}
			
			if (empty($message->message)) { 
	            throw CouldNotSendMessage::contentNotProvided();				
			}
			
			if ($this->bulglish) {
				$message_processed = Bulglish::toLatin( ( $message->prefix !== false ? $message->prefix : $this->prefix ) . $message->message );
			}
			
			if ($this->limitLength) {
				$message_processed = $this->cutText($message_processed);
			}
			
			$args = [
				'sid' => $this->sid,
				'encoding' => $this->encoding,
				'id' => $message->id.'_'.time(),
				'msisdn' => $this->processTel($message->to),
				'text' => $message_processed,
			];
			
			$query = http_build_query($args);
							
			if($this->log) {
				Log::channel($this->log_channel)->info('Mobica message',$query);
			}
			
			if($this->send) {
			
				$response = $this->client->request('GET', '?'.$query );
				$result = (string) $response->getBody();
				
				if($this->log) {
					Log::channel($this->log_channel)->info('Mobica response: '.$result);
				}
				
	            if (strpos($result, 'SEND_OK') === false) {
	                throw new \Exception($result);
	            }
		
			}
			
		} catch(\Exception $e) {
			
			Log::channel($this->log_channel)->info('Could not send Mobica message ('.$e->getMessage().')');
			
		}
		
	}
	
	
}
