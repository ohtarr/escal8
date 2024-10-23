<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Schedule;

class Timeframe extends Model
{
    protected $guarded = [];

	public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
