<?php

//Example Model to place in your App folder.

namespace App;

use ohtarr\ServiceNowModel;
use GuzzleHttp\Client as GuzzleHttpClient;
use App\CallLog;
use App\Tropo;
use App\Escalation;
use App\Group;

class ServiceNowIncident extends ServiceNowModel
{
	protected $guarded = [];

	public $table = "incident";

    public function __construct(array $attributes = [])
    {
        $this->snowbaseurl = env('SNOWBASEURL'); //https://mycompany.service-now.com/api/now/v1/table
        $this->snowusername = env("SNOWUSERNAME");
        $this->snowpassword = env("SNOWPASSWORD");
		parent::__construct($attributes);
    }

    public function addComment($comment)
    {
        $this->comments = $comment;
        $this->save();
    }

    public function isOpen()
    {
        if ($this->state == 4 || $this->state == 6 || $this->state == 7)
        {
            return false;
        } else {
            return true;
        }
    }

    public function getPriorityString()
    {
        $string = null;
        if($this->priority == 1)
        {
            $string =  "critical";
        }
        if($this->priority == 2)
        {
            $string = "high";
		}
        if($this->priority == 3)
        {
            $string = "medium";
        }
        if($this->priority == 4)
        {
            $string = "low";
        }
        return $string;
    }

/*
	public function getCallLogs()
	{
		return CallLog::where('incident_sys_id',$this->sys_id)->get();
	}
/**/
	public function generateVoiceMessage()
	{
		return "A new " . $this->getPriorityString() . " priority incident has been opened." . Tropo::stringToVoice($this->number) . "," . $this->short_description;
	}

	public function createEscalation()
	{
		$group = Group::where("sys_id",$this->assignment_group['value'])->first();
		return new Escalation($group, $this);
	}

}
