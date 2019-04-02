<?php

namespace App;

use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Log;

class Voice
{

	public static function createCall($from, $to)
	{
		$message = "VOICE: Creating call to " . $to . " from " . $from . ".\n";
		Log::info($message);
		print $message;
		$body = [
			"from"		=>	$from,
			"to"		=>	$to,
		];
		$params['auth'] = [
			env("VOICE_TOKEN"),
			env("VOICE_SECRET"),
		];
		$params['body'] = json_encode($body);
		$params['headers'] = [
			'Content-Type'	=> 'application/json',
		];
		$client = new GuzzleHttpClient([
			'base_uri' => env("VOICE_BASE_URL"),
		]);
		try
		{
			$response = $client->request("POST", "calls", $params);
		} catch(\Exception $e) {
			print $e->getMessage();
		}
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
	}

	public static function getCall($to)
	{
		$params['auth'] = [
			env("VOICE_TOKEN"),
			env("VOICE_SECRET"),
		];
		$params['headers'] = [
			'Content-Type'	=> 'application/json',
		];
		$params['query'] = [
			'to'	=> $to,
			'state'	=>	'active',
		];
		$client = new GuzzleHttpClient([
			'base_uri' => env("VOICE_BASE_URL"),
		]);
		try
		{
			$response = $client->request("GET", "calls", $params);
		} catch(\Exception $e) {
			print $e->getMessage();
		}
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
		print_r($array);
		if(!empty($array[0]))
		{
			return $array[0];
		} else {
			return null;
		}
	}

	public static function PlayMessage($callId,$voicemessage)
	{
		$message = "VOICE: Playing message for call ID " . $callId . ".\n";
		Log::info($message);
		print $message;
		$params['json'] = [
			"sentence"	=>	$voicemessage,
			"locale"	=>	"en_US",
			"gender"	=>	"female",
			"voice"		=>	"Kate",
		];
		$params['auth'] = [
			env("VOICE_TOKEN"),
			env("VOICE_SECRET"),
		];
		//$params['body'] = json_encode($body);
		$params['headers'] = [
			'Content-Type'	=> 'application/json',
		];
		$client = new GuzzleHttpClient([
			'base_uri' => env("VOICE_BASE_URL"),
		]);
		try
		{
			$response = $client->request("POST", "calls/" . $callId . "/audio", $params);
		} catch(\Exception $e) {
			print $e->getMessage();
		}
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
	}

	public static function disconnectCall($callId)
	{
		$message = "VOICE: Disconnecting call ID " . $callId . ".\n";
		Log::info($message);
		print $message;
		$body = [
			"state"	=>	"completed",
		];
		$params['auth'] = [
			env("VOICE_TOKEN"),
			env("VOICE_SECRET"),
		];
		$params['body'] = json_encode($body);
		$params['headers'] = [
			'Content-Type'	=> 'application/json',
		];
		$client = new GuzzleHttpClient([
			'base_uri' => env("VOICE_BASE_URL"),
		]);
		try
		{
			$response = $client->request("POST", "calls/" . $callId, $params);
		} catch(\Exception $e) {
			print $e->getMessage();
		}
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
	}

	public static function startNewCall($from,$to)
	{
		$call = self::getCall($to);
		if($call)
		{
			$message = "VOICE: Found existing call ID " . $call['id'] . " to " . $to . ".\n";
			Log::info($message);
			print $message;
			return $call;
		} else {
			//for($y = 0; $y <=2; $y++)
			//{
				self::createCall($from,$to);

				for($x = 0; $x <=60; $x++)
				{
					sleep(1);
					$call = self::getCall($to);
					if($call)
					{
						$message = "VOICE: Found call ID " . $call['id'] . " to " . $to . ".\n";
						Log::info($message);
						print $message;
						return $call;
					}
				}
			//}
			$message = "VOICE: Unable to create a call to " . $to . ".\n";
			Log::info($message);
			print $message;
			return null;
		}
	}

	public static function NotifyVoice($from, $to, $voicemessage)
	{
		$message = "VOICE: Notifying " . $to . " with message " . $voicemessage . ".\n";
		Log::info($message);
		print $message;
		$msg = "";
		for($repeats = 1; $repeats < 3; $repeats++)
		{
			$msg .= $voicemessage;
			$msg .= "This message will now repeat"; 
		}
		$msg .= $voicemessage;
		$msg .= "Goodbye";
		$call = self::startNewCall($from,$to);
		if($call)
		{
			print_r($call);
			self::PlayMessage($call['id'], $msg);
			//Wait for 15 time intervals before disconnecting, unless call is disconnected by called party.
			for ($time = 0; $time <= 15; $time++) {
				$callcheck = self::getCall($to); //Check status of call
				//if call is disconnected by called party, return true and exit function.
				if(!$callcheck)
				{
					$message = "VOICE: Call Hangup detected on call ID " . $call['id'] . "...\n";
					Log::info($message);
					print $message;
					return true;
				}
				sleep(3); // Wait 3 seconds before checking call status again.
			}
			//After 15 time intervals (3 seconds * 15) if call is still active, disconnect it.
			if($callcheck)
			{
				self::disconnectCall($call['id']);
				return true;
			}
		} else {
			$message = "VOICE: Notifying " . $to . " FAILED!\n";
			Log::info($message);
			print $message;
			return false;
		}
	}

	public static function NotifySms($from, $to, $message)
	{
		$body = [
			"from"		=>	$from,
			"to"		=>	$to,
			"text"		=>	$message,
		];
		$params['auth'] = [
			env("VOICE_TOKEN"),
			env("VOICE_SECRET"),
		];
		$params['body'] = json_encode($body);
		$params['headers'] = [
			'Content-Type'	=> 'application/json',
		];
		$client = new GuzzleHttpClient([
			'base_uri' => env("VOICE_BASE_URL"),
		]);
		try
		{
			$response = $client->request("POST", "messages", $params);
		} catch(\Exception $e) {
			print $e->getMessage();
		}
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
	}

	public static function stringToVoice($name)
	{
		return implode(" ", str_split($name));	
	}

}
