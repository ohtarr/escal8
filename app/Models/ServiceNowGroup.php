<?php

//Example Model to place in your App folder.

namespace App\Models;

use ohtarr\ServiceNowModel;

class ServiceNowGroup extends ServiceNowModel
{
	protected $guarded = [];

	public $table = "sys_user_group";

    public function __construct(array $attributes = [])
    {
        $this->snowbaseurl = env('SNOWBASEURL'); //https://mycompany.service-now.com/api/now/v1/table
        $this->snowusername = env("SNOWUSERNAME");
        $this->snowpassword = env("SNOWPASSWORD");
		parent::__construct($attributes);
    }

}
