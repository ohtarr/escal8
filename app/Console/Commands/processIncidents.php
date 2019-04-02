<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Group;
use Illuminate\Support\Facades\Log;
use App\Escalation;
use App\ServiceNowIncident;

class processIncidents extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'escal8:processIncidents';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Find and Process all incidents';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
	parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->processEscalations();
	}

	public static function logAndPrint($message)
	{
		Log::info($message);
		print $message;
	}
	public function processEscalations()
	{
		$groups = Group::all();
		foreach($groups as $group)
        	{
			$group->processIncidents();


/* 			$message = $group->getServiceNowGroup()->name . ": Processing group...\n" ;
			self::logAndPrint($message);
			$incidents = $group->getOpenUnassignedPriorityIncidents();
			$message = $group->getServiceNowGroup()->name . ": Found " . $incidents->count() . " incidents that require escalation.\n" ;
			self::logAndPrint($message);
			foreach($incidents as $incident)
			{
				//get a fresh copy of the incident for any last minute changes.
				$message = $group->getServiceNowGroup()->name . " " . $incident->number . ": Creating an escalation for incident.\n" ;
				self::logAndPrint($message);
				$escalation = new Escalation($group, $incident->getFresh());
				//$escalation = $incident->createEscalation();
				if($escalation->getCurrentPhoneNumber())
		                {
                		        if($escalation->isCallTime())
		                        {
		                                if($escalation->isCallDelayExpired())
		                                {
		                                        $message = $group->getServiceNowGroup()->name . " " . $incident->number . ": Calling " . $escalation->getCurrentPhoneNumber() . " for group.\n" ;
							self::logAndPrint($message);
		                                        $callstatus = $escalation->callGroup();
							if($callstatus)
							{
								$message = $group->getServiceNowGroup()->name . " " . $incident->number . ": Call to group was SUCCESSFUL!\n";
								self::logAndPrint($message);
							} else {
								$message = $group->getServiceNowGroup()->name . " " . $incident->number . ": Call to group FAILED!\n";
								self::logAndPrint($message);
							}
		                                } else {
		                                        $message = $group->getServiceNowGroup()->name . " " . $incident->number . ": escalation_delay is NOT expired.  Aborting Escalation.\n";
							self::logAndPrint($message);
		                                }
		                        } else {
		                                $message = $group->getServiceNowGroup()->name . " " . $incident->number . ": It is currently outside of the escalation schedule.  Aborting Escalation.\n";
						self::logAndPrint($message);
		                        }
		                } else {
		                        $message = $group->getServiceNowGroup()->name . " " . $incident->number . ": All escalation phone numbers have been exhausted.  Aborting Escalation.\n";
					self::logAndPrint($message);
		                }
			}*/
		}
	}
}
