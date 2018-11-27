<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Group;
use Illuminate\Support\Facades\Log;
use App\Escalation;

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

	public function processEscalations()
	{
		$groups = Group::all();
		foreach($groups as $group)
        	{
			$message = "Processing group " . $group->getServiceNowGroup()->name . " : \n" ;
			Log::info($message);
			print $message;
			$incidents = $group->getOpenUnassignedPriorityIncidents();
			$message = "Found " . $incidents->count() . " incidents that require escalation.\n" ;
                        Log::info($message);
                        print $message;
			foreach($incidents as $incident)
			{
				$message = "Creating an escalation for " . $incident->number . ".\n" ;
	                        Log::info($message);
        	                print $message;
				$escalation = new Escalation($group, $incident);
				//$escalation = $incident->createEscalation();
				if($escalation->getCurrentPhoneNumber())
		                {
                		        if($escalation->isCallTime())
		                        {
		                                if($escalation->isCallDelayExpired())
		                                {
		                                        $message = "Calling " . $escalation->getCurrentPhoneNumber() . " for group " . $escalation->group->getServiceNowGroup()->name . ".\n" ;
		                                        Log::info($message);
		                                        print $message;
		                                        $callstatus = $escalation->callGroup();
							if($callstatus)
							{
								$message = "Call to group " . $escalation->group->getServiceNowGroup()->name . " was SUCCESSFUL!\n";
	                                                        Log::info($message);
	                                                        print $message;
							} else {
								$message = "Call to group " . $escalation->group->getServiceNowGroup()->name . " FAILED!\n";
                                                                Log::info($message);
                                                                print $message;
							}
		                                } else {
		                                        $message = "escalation_delay is NOT expired.  Aborting Escalation.\n";
		                                        Log::info($message);
		                                        print $message;
		                                }
		                        } else {
		                                $message = "It is currently outside of the escalation schedule.  Aborting Escalation.\n";
		                                Log::info($message);
		                                print $message;
		                        }
		                } else {
		                        $message = "All escalation phone numbers have been exhausted.  Aborting Escalation.\n";
		                        Log::info($message);
		                        print $message;
		                }
			}
		}
	}
}
