<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ServiceNowIncident;
use App\ServiceNowGroup;
use App\Tropo;
use App\CallLog;
use App\Escalation;
use App\Schedule;

class Group extends Model
{

	protected $casts = [
		'phones' => 'array',
	];

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

}
