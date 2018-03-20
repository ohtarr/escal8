<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timeframe extends Model
{
    protected $guarded = [];

	public function schedule()
    {
        return $this->belongsTo('App\Schedule');
    }
}
