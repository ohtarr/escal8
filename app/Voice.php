<?php

namespace App;

use GuzzleHttp\Client as GuzzleHttpClient;

class Voice
{

	public static function createCall($from, $to)
	{
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

	public static function getCallId($to)
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
		if(!empty($array[0]['id']))
		{
			return $array[0]['id'];
		} else {
			return null;
		}
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

	public static function PlayMessage($callId,$message)
	{
		$params['json'] = [
			"sentence"	=>	$message,
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

	public static function startNewCall($to)
	{
		$call = self::getCall($to);
		if($call)
		{
			return $call;
		} else {
			//for($y = 0; $y <=2; $y++)
			//{
				self::createCall(env("VOICE_CALLERID"),$to);

				for($x = 0; $x <=60; $x++)
				{
					sleep(1);
					$call = self::getCall($to);
					if($call)
					{
						return $call;
					}
				}
			//}
			return null;
		}
	}

	public static function Notify($to, $message)
	{
		$msg = "";
		for($repeats = 1; $repeats < 3; $repeats++)
		{
			$msg .= $message;
			$msg .= ",This message will now repeat,,,"; 
		}
		$msg .= $message;
		$msg .= ",Goodbye!";
		$call = self::startNewCall($to);
		if($call)
		{
			print_r($call);
			self::PlayMessage($call['id'], $msg);
			//Wait for 15 time intervals before disconnecting, unless call is disconnected by called party.
			for ($time = 0; $time <= 15; $time++) {
				$call = self::getCall($to); //Check status of call
				//if call is disconnected by called party, return true and exit function.
				if(!$call)
				{
					return true;
				}
				sleep(3); // Wait 3 seconds before checking call status again.
			}
			//After 15 time intervals (3 seconds * 15) if call is still active, disconnect it.
			if($call)
			{
				self::disconnectCall($call['id']);
				return true;
			}
		} else {
			return false;
		}
	}

	public static function stringToVoice($name)
	{
		return implode(" ", str_split($name));	
	}

}
