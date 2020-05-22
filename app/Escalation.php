<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Group;
use App\ServiceNowIncident;
use App\CallLog;
use Carbon\Carbon;
use App\Voice;
use App\AmazonConnect;
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

	public function getSuccessfullCallLogs()
	{
		return CallLog::where('incident_sys_id',$this->incident->sys_id)->where('group_id',$this->group->id)->where("status", 1)->get();
	}

 	public function getAllCallLogs()
	{
		return CallLog::where('incident_sys_id',$this->incident->sys_id)->where('group_id',$this->group->id)->get();
	}

	public function getLastSuccessfulCallLog()
	{
		return $this->getSuccessfullCallLogs()->sortBy('created_at')->last();
	}
	
	public function getCurrentPhoneNumber()
	{
		if(isset($this->group->phones[$this->getCallNumber()]))
		{
	        return $this->group->phones[$this->getCallNumber()];
		}
		return null;
	}

	public function createCallLog($status, $contactid)
	{
		$calllog = new CallLog;
		$calllog->group_id = $this->group->id;
		$calllog->incident_sys_id = $this->incident->sys_id;
		$calllog->to = $this->getCurrentPhoneNumber();
		$calllog->from = $this->group->caller_id;
		$calllog->callnum = $this->getCallNumber();
		$calllog->msg = $this->generateVoiceMessage();
		$calllog->status = $status;
		$calllog->voice = $this->group->voice;
		$calllog->sms = $this->group->sms;
		$calllog->contactid = $contactid;
		$calllog->save();
	}

	public function getCallNumber()
	{
		return $this->getSuccessfullCallLogs()->count();
	}

	public function generateVoiceMessage()
	{
		return "A " . $this->incident->getPriorityString() . " priority ticket has been assigned to your group," . $this->stringToVoice($this->incident->number) . "," . $this->incident->short_description;
	}

	public function callGroup()
	{
		if($this->getCurrentPhoneNumber())
		{
			$msg = $this->generateVoiceMessage();
			for($count = 0; $count <= 2; $count++)
			{
				$status = AmazonConnect::Notify($this->group->caller_id, $this->getCurrentPhoneNumber(), $msg);
				if($status)
				{
					$this->incident->addComment("Called " .$this->group->getServiceNowGroup()->name . " group at " . $this->getCurrentPhoneNumber() . " and played the following message : \n" . $msg);
					$this->createCallLog(1,$status->get('ContactId'));
					return true;
				} else {
					$this->createCallLog(0, null);
				}
			}
		}
		return false;
	}

	public function smsGroup()
	{
		if($this->getCurrentPhoneNumber())
		{
			for($count = 0; $count <= 3; $count++)
			{
				$status = Voice::NotifyVoice($this->getCurrentPhoneNumber(), $this->generateVoiceMessage());
				if($status == 1)
				{
					$this->incident->addComment("Texted " .$this->group->getServiceNowGroup()->name . " group at " . $this->getCurrentPhoneNumber() . " and played the following message : \n" . $this->generateVoiceMessage());
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
		$lastlog = $this->getLastSuccessfulCallLog();
		if(!$lastlog || $lastlog->created_at < Carbon::now()->subMinutes($this->group->escalation_delay))
		{
			return true;
		}
		return false;
	}

/* 	public function isCallable()
	{
		$this->incident = $this->incident->getFresh();
		if($this->isCallTime() && $this->isCallDelayExpired() && $this->incident->assigned_to == "" && $this->incident->priority < $this->group->min_priority && $this->getCurrentPhoneNumber())
		{
			return true;
		}
	} */

	public function process()
	{
		$message = $this->group->getServiceNowGroup()->name . " " . $this->incident->number . ": Processing Escalation...\n";
		Log::info($message);
		print $message;
		$exit = 0;
		if(!$this->getCurrentPhoneNumber())
		{
			$message = $this->group->getServiceNowGroup()->name . " " . $this->incident->number . ": All escalation phone numbers have been exhausted.  Aborting Escalation.\n";
			Log::info($message);
			print $message;
			$exit=1;
		}
		if(!$this->isCallTime())
		{
			$message = $this->group->getServiceNowGroup()->name . " " . $this->incident->number . ": It is currently outside of the escalation schedule.  Aborting Escalation.\n";
			Log::info($message);
			print $message;
			$exit=1;
		}
		if(!$this->isCallDelayExpired())
		{
			$message = $this->group->getServiceNowGroup()->name . " " . $this->incident->number . ": escalation_delay is NOT expired.  Aborting Escalation.\n";
			Log::info($message);
			print $message;
			$exit=1;
		}
		if($exit == 1)
		{
			return null;
		}
		$message = $this->group->getServiceNowGroup()->name . " " . $this->incident->number . ": Calling Group...\n";
		Log::info($message);
		print $message;
		$callstatus = $this->callGroup();
		if($callstatus)
		{
			$message = $this->group->getServiceNowGroup()->name . " " . $this->incident->number . ": Call to group was SUCCESSFUL!\n";
			Log::info($message);
			print $message;
			return true;
		} else {
			$message = $this->group->getServiceNowGroup()->name . " " . $this->incident->number . ": Call to group FAILED!\n";
			Log::info($message);
			print $message;
			return false;
		}

	}

	public static function stringToVoice($name)
	{
		return implode(" ", str_split($name));	
	}

}

