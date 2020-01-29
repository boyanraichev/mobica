<?php

namespace Boyo\Mobica;

use Boyo\Mobica\Exceptions\CouldNotSendMessage;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MobicaSender
{
	private $log = true;
	
	private $log_channel = 'stack';
	
	private $send = false;
	
	private $url = 'https://gate.mobica.bg/send.php';	
	
	private $headers = [
		'Accept' => 'application/json',
		'Content-Type' => 'application/json',
	];
	
	private $user = '';
	
	private $pass = '';
	
	// construct
	public function __construct() {
		
		// settings
		$this->user = config('services.mobica.user');
		$this->pass = config('services.mobica.pass');
		$this->log = config('services.mobica.log');
		$this->send = config('services.mobica.send');
		$this->log_channel = config('services.mobica.log_channel');
				
		// setup Guzzle client
		$this->client = new Client([
			'base_uri' => $this->url,
			'headers' => $this->headers,
		]);
		
	}
	
	// send email
	public function send(MobicaMessage $message) {
		
		try {
			
			$request = $message->getMessage();
			
			$request['user'] = $this->user;
			$request['pass'] = $this->pass;
							
			if($this->log) {
				Log::channel($this->log_channel)->info('Mobica message',$request);
			}
			
			if($this->send) {
			
				$response = $this->client->request('POST', '', [ 'json' => $request ]);
				
				$result = (string) $response->getBody();
				
				if($this->log) {
					Log::channel($this->log_channel)->info('Mobica response: '.$result);
				}
				
/*
	            if (strpos($result, 'SEND_OK') === false) {
	                throw new \Exception($result);
	            }
*/
		
			}
			
		} catch(\Exception $e) {
			
			Log::channel($this->log_channel)->info('Could not send Mobica message ('.$e->getMessage().')');
			
		}
		
	}
	
	
}
