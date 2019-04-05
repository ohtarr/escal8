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
			'Accept'		=> 'application/json',
		];
		$client = new GuzzleHttpClient([
			'base_uri' => env("VOICE_BASE_URL"),
			//'http_errors' => false,
		]);
		try
		{
			$response = $client->request("POST", "calls", $params);
		} catch(\Exception $e) {
			print $e->getMessage();
		}
		//Regex to match the call ID that comes back in the LOCATION url field of the header 
		if(!$response)
		{
			$message = "VOICE: Creating call FAILED to " . $to . " from " . $from . ".\n";
			Log::info($message);
			print $message;
			return null;
		}
		$reg = "/\/calls\/(\S+)/";
		//get the Location field in the header
		$callurl = $response->getHeader('Location')[0]; 
		//perform the pregmatch
		if(preg_match($reg,$callurl,$hits))
		{
			$callid = $hits[1];
		}
		if($callid)
		{
			$message = "VOICE: Call ID " . $callid . " has been created.\n";
			Log::info($message);
			print $message;
			//return the call ID that is created.
			return $callid;
		}
		$message = "VOICE: Creating call FAILED to " . $to . " from " . $from . ".\n";
		Log::info($message);
		print $message;
	}

	public static function getCallById($callid)
	{
		$params['auth'] = [
			env("VOICE_TOKEN"),
			env("VOICE_SECRET"),
		];
		$params['headers'] = [
			'Content-Type'	=> 'application/json',
		];
		$client = new GuzzleHttpClient([
			'base_uri' => env("VOICE_BASE_URL"),
		]);
		try
		{
			$response = $client->request("GET", "calls/" . $callid, $params);
		} catch(\Exception $e) {
			print $e->getMessage();
			return null;
		}
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
		return $array;
	}

	public static function getCallEventsById($callid)
	{
		$params['auth'] = [
			env("VOICE_TOKEN"),
			env("VOICE_SECRET"),
		];
		$params['headers'] = [
			'Content-Type'	=> 'application/json',
		];
		$client = new GuzzleHttpClient([
			'base_uri' => env("VOICE_BASE_URL"),
		]);
		try
		{
			$response = $client->request("GET", "calls/" . $callid . "/events", $params);
		} catch(\Exception $e) {
			print $e->getMessage();
		}
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
		if(!empty($array))
		{
			return $array;
		} else {
			return null;
		}
	}

	public static function getActiveCallByTo($to)
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
			return null;
		}
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
		if(isset($array[0]))
		{
			return $array[0];
		}
	}

	public static function getStartedCallByTo($to)
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
			'state'	=>	'started',
		];
		$client = new GuzzleHttpClient([
			'base_uri' => env("VOICE_BASE_URL"),
		]);
		try
		{
			$response = $client->request("GET", "calls", $params);
		} catch(\Exception $e) {
			print $e->getMessage();
			return null;
		}
		//get the body contents and decode json into an array.
		$array = json_decode($response->getBody()->getContents(), true);
		if(isset($array[0]))
		{
			return $array[0];
		}
	}

	public static function getCallByTo($to)
	{
		if($active = self::getActiveCallByTo($to))
		{
			return $active;
		}
		if($started = self::getStartedCallByTo($to))
		{
			return $started;
		}
	}

	public static function playMessage($callId,$voicemessage)
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
			return null;
		}
		//get the body contents and decode json into an array.
		return json_decode($response->getBody()->getContents(), true);
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
			return null;
		}
		//get the body contents and decode json into an array.
		return true;
	}

	public static function startNewCall($from,$to)
	{
		$call = self::getCallByTo($to);
		if($call)
		{
			$message = "VOICE: Found existing call ID " . $call['id'] . " to " . $to . ".  Cancelling new call!\n";
			Log::info($message);
			print $message;
			return $call;
		} else {
			//for($y = 0; $y <=2; $y++)
			//{
				$callid = self::createCall($from,$to);

				for($x = 0; $x <=60; $x++)
				{
					sleep(1);
					$call = self::getCallById($callid);
					if($call['state'] == 'active')
					{
						$message = "VOICE: Found ACTIVE call ID " . $call['id'] . " to " . $to . ".\n";
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

	public static function CompileMessage($message)
	{
		$msg = "";
		for($repeats = 1; $repeats < 3; $repeats++)
		{
			$msg .= $message;
			$msg .= ",This message will now repeat,"; 
		}
		$msg .= $message;
		$msg .= ",Goodbye";
		return $msg;
	}

	public static function NotifyVoice($from, $to, $voicemessage)
	{
		$message = "VOICE: Notifying " . $to . " with message " . $voicemessage . ".\n";
		Log::info($message);
		print $message;
		$msg = self::CompileMessage($voicemessage);
		$call = self::startNewCall($from,$to);
		if($call)
		{
			//print_r($call);
			self::playMessage($call['id'], $msg);
			//Wait for 15 time intervals before disconnecting, unless call is disconnected by called party.
			for ($time = 0; $time <= 15; $time++) {
				$callcheck = self::getCallById($call['id']);
				//$callcheck = self::getCall($to); //Check status of call
				//if call is disconnected by called party, return true and exit function.
				if($callcheck['state'] == "completed")
				{
					$message = "VOICE: Call Hangup detected on call ID " . $call['id'] . "...\n";
					Log::info($message);
					print $message;
					return true;
				}
				sleep(3); // Wait 3 seconds before checking call status again.
			}
			//After 15 time intervals (3 seconds * 15) if call is still active, disconnect it.
			if($callcheck['state'] == "active")
			{
				$message = "VOICE: Call notification timer expired on call ID " . $call['id'] . "... disconnected call!\n";
				Log::info($message);
				print $message;
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
