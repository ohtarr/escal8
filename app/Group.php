<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ServiceNowIncident;
use App\ServiceNowGroup;
use Illuminate\Support\Facades\Log;

class Group extends Model
{

	protected $casts = [
		'phones' => 'array',
	];

	public $ServiceNowGroup;

    public function schedule()
    {
        return $this->belongsTo('App\Schedule');
    }

	public function getOpenUnassignedPriorityIncidents()
	{
        return ServiceNowIncident::where("assignment_group",$this->sys_id)->where("state","!=",6)->where("active",1)->where("assigned_to","")->where("priority","<=",$this->min_priority)->get(); 
	}

	public function getServiceNowGroup()
	{
		if($this->ServiceNowGroup)
		{
			return $this->ServiceNowGroup;
		} else {
			$this->ServiceNowGroup = ServiceNowGroup::find($this->sys_id);
			return $this->ServiceNowGroup;
		}
	}

	public function processIncidents()
	{
		$message = $this->getServiceNowGroup()->name . ": Processing Escalations for group...\n";
		Log::info($message);
		print $message;
		$incidents = $this->getOpenUnassignedPriorityIncidents();
		foreach($incidents as $incident)
		{
			$message = $this->getServiceNowGroup()->name . " " . $incident->number . ": Creating escalation.\n";
			Log::info($message);
			print $message;
			$escalation = new Escalation($this, $incident);
			$escalation->process();
		}
	}
}
