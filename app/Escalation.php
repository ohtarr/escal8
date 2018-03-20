<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Group;
use App\ServiceNowIncident;
use App\CallLog;
use Carbon\Carbon;
use App\Tropo;
use App\Schedule;

class Escalation extends Model
{

	public $group;
	public $incident;

	public function __construct(Group $group, ServiceNowIncident $incident)
	{
		$this->group = $group;
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

	public function callGroup()
	{
		if($this->getCurrentPhoneNumber())
		{
			for($count = 0; $count <= 3; $count++)
			{
		        $status = Tropo::callvoice($this->getCurrentPhoneNumber(),$this->incident->generateVoiceMessage(), $this->group->tropo_key, $this->group->caller_id);
				if($status == 1)
				{
					$this->incident->addComment("Called " . $this->getCurrentPhoneNumber() . " and played the following message : " . $this->incident->generateVoiceMessage());
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

	public function process()
	{
		if($this->isCallDelayExpired() && $this->isCallTime())
		{
			$this->callGroup();
		}
	}
}

