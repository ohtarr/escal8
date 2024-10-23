<?php

namespace App\Models;

use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Log;
use Aws\Connect\ConnectClient;
use Aws\Exception\AwsException;

class AmazonConnect
{

	public static function Notify($from, $to, $msg)
	{
		$client = new ConnectClient([
			'version'     => 'latest',
			'region'      => env('AMAZON_REGION'),
			'credentials' => [
				'key'    => env('AMAZON_KEY'),
				'secret' => env('AMAZON_SECRET'),
			],
		]);

		$params = [
			'InstanceId'				=> env('AMAZON_INSTANCE_ID'),
			'ContactFlowId'				=> env('AMAZON_CONTACT_FLOW_ID'),	
			'SourcePhoneNumber'			=> "+1" . $from,
			'DestinationPhoneNumber'	=> "+1" . $to,
			'Attributes'				=> ['msg'	=>	$msg],
		];

		$message = 'AMAZONCONNECT: Notifying ' . $to . ' with message "' . $msg . '".' . "\n";
		print $message;
		Log::info($message);
		$result = null;
		try
		{
			$result = $client->startOutboundVoiceContact($params);
		} catch(\Exception $e) {
			//print $e->getAwsErrorMessage();
			$message = "AMAZONCONNECT: Call Failure: " . $e->getAwsErrorMessage() ."\n";
			print $message;
			Log::info($message);
		}
		if($result)
		{
			$message = "AMAZONCONNECT: ContactId " . $result->get('ContactId') . " created successfully.\n";
			print $message;
			Log::info($message);
		}
		return $result;
	}
}
