<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Timeframe;
use App\Group;

class Schedule extends Model
{

    public function timeframes()
    {
        return $this->hasMany('App\Timeframe');
    }

    public function groups()
    {
        return $this->hasMany('App\Group');
    }

	public function createTimeframe($day, $start, $end)
	{
		return Timeframe::create(['schedule_id' => $this->id,'day'=>$day, 'start'=>$start, 'end'=>$end]);
	}

	public function getTimeframes()
	{
		return Timeframe::where('schedule_id',$this->id)->get();
	}

}
