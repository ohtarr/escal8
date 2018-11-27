<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Group;
use App\ServiceNowIncident;
use App\CallLog;
use Carbon\Carbon;
use App\Voice;

use Illuminate\Support\Facades\Log;

class Escalation extends Model
{

	public $group;
	public $incident;

	public function __construct(Group $group, ServiceNowIncident $incident)
	{
		$this->group = $group;
		$this->group->getServiceNowGroup();
		$this->incident = $incident;
	}

    public function schedule()
    {
        return $this->belongsTo('Schedule');
    }

	public function getCallLogs()
	{
		return CallLog::where('incident_sys_id',$this->incident->sys_id)->where('group_id',$this->group->id)->get();
	}

	public function getLastCallLog()
	{
		return $this->getCallLogs()->sortBy('created_at')->last();
	}

	public function getCurrentPhoneNumber()
	{
		if(isset($this->group->phones[$this->getCallNumber()]))
		{
	        return $this->group->phones[$this->getCallNumber()];
		}
		return null;
	}

	public function createCallLog($status)
	{
		$calllog = new CallLog;
		$calllog->group_id = $this->group->id;
		$calllog->incident_sys_id = $this->incident->sys_id;
		$calllog->to = $this->getCurrentPhoneNumber();
		$calllog->from = $this->group->caller_id;
		$calllog->callnum = $this->getCallNumber();
		$calllog->msg = $this->incident->generateVoiceMessage();
		$calllog->status = $status;
		$calllog->save();
	}

	public function getCallNumber()
	{
		return $this->getCallLogs()->count();
	}

	public function generateVoiceMessage()
	{
		return "A new " . $this->incident->getPriorityString() . " priority incident has been opened." . Voice::stringToVoice($this->incident->number) . "," . $this->incident->short_description;
	}

	public function callGroup()
	{
		if($this->getCurrentPhoneNumber())
		{
			for($count = 0; $count <= 3; $count++)
			{
				$status = Voice::Notify($this->getCurrentPhoneNumber(), $this->incident->generateVoiceMessage());
				if($status == 1)
				{
					$this->incident->addComment("Called " .$this->group->getServiceNowGroup()->name . " group at " . $this->getCurrentPhoneNumber() . " and played the following message : \n" . $this->incident->generateVoiceMessage());
					$this->createCallLog(1);
					return true;
				}
			}
		}
		return false;
	}

	public function isCallTime()
	{
		$now = Carbon::now('America/Chicago');
		$timeframes = $this->group->schedule->timeframes()->where('day',$now->dayOfWeek)->where('start','<',$now)->where('end','>',$now)->get();
		if($timeframes->isNotEmpty())
		{
			return true;
		}
		return false;
	}

	public function isCallDelayExpired()
	{
		$lastlog = $this->getLastCallLog();
		if(!$lastlog || $lastlog->created_at < Carbon::now()->subMinutes($this->group->escalation_delay))
		{
			return true;
		}
		return false;
	}
/*
	public function process()
	{
		if($this->getCurrentPhoneNumber())
		{
			if($this->isCallTime())
			{
				if($this->isCallDelayExpired())
				{
					$message = "Calling " . $this->getCurrentPhoneNumber() . " for group " . $this->group->getServiceNowGroup()->name . ".\n" ;
	        	                Log::info($message);
                		        print $message;
					$this->callGroup();
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
/**/
}

