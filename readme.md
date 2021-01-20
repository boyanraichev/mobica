# Mobica Notification channel

This package adds a notification channel for Mobica.bg services. You can use it to send SMS messages and Viber for Business messages.

## Installation

Install through Composer.

## Config

Add the following to your services config file.

```php
'mobica' => [
	'user' => env('MOBICA_USER',''),
	'pass' => env('MOBICA_PASS',''),
	'prefix' => '',
	'log' => env('MOBICA_LOG',true),
	'log_channel' => env('MOBICA_LOG_CHANNEL','stack'),
	'send' => env('MOBICA_SEND',false),
	'bulglish' => true,
	'allow_multiple' => false,
],
```

- *log* if the messages should be written to a log file
- *log_channel* the log channel to log messages to
- *send* if the messages should be sent (production/dev environment)
- *bulglish* if cyrillic text should be converted to latin letters for SMS messages (cyrillic messages are limited to 67 characters)
- *allow_multiple* if SMS messages above 160 characters should be allowed (billed as multiple messages)

## Send test

To send a test message use the following artisan command:

`php artisan mobica:test phone --message='content' --channel=sms --promo`

- channel can have the following values: sms|viber|viber-sms
- promo will send a promo test message with image and button

## Direct usage

You can instantiate a `Boyo\Mobica\MobicaMessage` object and send it immediately.

```php
use Boyo\Mobica\MobicaMessage;
use Boyo\Mobica\MobicaSender;

class MyClass
{
	public function myFunction()
	{
		$message = (new MobicaMessage())->to('359888888888')->channel('viber-sms')->sms('SMS text')->viber('Viber text');
		
		$client = new MobicaSender();
		$client->send($message);	
	}
}
```

## Usage with notifications

1. Create a message file that extends `Boyo\Mobica\MobicaMessage`. It can take whatever data you need in the construct and should implement a `build()` method that defines the messages text content - a good practice would be to render a view file, so that your message content is in your views. You should only define the methods for the delivery channels that your are going to use. 

```php
use Boyo\Mobica\MobicaMessage;

class MyMessage extends MobicaMessage 
{
	public function __construct($data)
    {
        $this->id = $data->id; // your unique message id, add other parameters if needed
    }
    
	public function build() {
		// set your sms text 
		$this->sms('SMS text');
	
		// set your viber text
		$this->viber('Viber text');
		
		return $this;
	}	
}
```

2. In your Notification class you can now include the Mobica channel in the `$via` array returned by the `via()` method.

```php
use Boyo\Mobica\MobicaChannel;

via($notifiable) 
{
	
	// ...
	
	$via[] = MobicaChannel::class;
	
	return $via 
	
}
```

Within the same Notification class you should also define a method `toSms()`:

```php
public function toSms($notifiable)
{
	return (new MyMessage($unique_id))->to($notifiable->phone)->channel('viber-sms');
}
```

The channel method is where you define the delivery channel you wish to use. 

- **sms** delivery by sms only (this is the default value, if you omit the channel method)
- **viber** delivery by viber only
- **viber-sms** delivery attempted by viber, if not successful delivery by sms

