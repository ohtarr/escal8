<?php

//Example Model to place in your App folder.

namespace App;

use ohtarr\ServiceNowModel;

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
	switch($this->priority)
	{
		case 1:
			$string = "critical";
			break;
		case 2:
			$string = "high";
			break;
		case 3:
			$string = "medium";
			break;
		case 4:
			$string = "low";
			break;
	}
        return $string;
    }

	public function generateVoiceMessage()
	{
		return "A new " . $this->getPriorityString() . " priority incident has been opened." . $this->stringToVoice($this->number) . "," . $this->short_description;
	}

    public static function stringToVoice($name)
	{
		return implode(" ", str_split($name));	
	}

}
