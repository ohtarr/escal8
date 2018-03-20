<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Group;
use App\CallLog;
use App\Tropo;
use Carbon\Carbon;

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

/*
	public function createEscalations()
	{
		$groups = Group::all();
		foreach($groups as $group)
		{
			$incidents = $group->getOpenUnassignedPriorityIncidents();
			foreach($incidents as $incident)
			{
				$this->createEscalation($group, $incident);
			}
		}
	}


	public function createEscalation($group, $incident)
	{
		$escalation = new Escalation;
		$escalation->group = $group;
		$escalation->incident = $incident;
		return $escalation;
	}
/**/

	public function processEscalations()
	{
        $groups = Group::all();
        foreach($groups as $group)
        {
            $incidents = $group->getOpenUnassignedPriorityIncidents();
            foreach($incidents as $incident)
            {
				$escalation = $incident->createEscalation();
				$escalation->process();
			}
        }
	}
}
