<?php

namespace App;

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
			'region'      => 'us-east-1',
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

		$message = "AMAZONCONNECT: Notifying " . $to . " with message " . $msg . ".\n";
		Log::info($message);

		$result = $client->startOutboundVoiceContact($params);
		if($result)
		{
			$message = "AMAZONCONNECT: ContactId " . $result->get('ContactId') . " created successfully.\n";
			Log::info($message);
		}
		return $result;
	}
}
