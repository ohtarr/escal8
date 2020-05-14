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
		}
	}
}
