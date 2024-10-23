<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Timeframe;
use App\Models\Group;

class Schedule extends Model
{

    public function timeframes()
    {
        return $this->hasMany(Timeframe::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
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
