<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Group;

class auditGroups extends Command
{
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'escal8:auditGroups';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Audit all groups';

        /**
         * Create a new command instance.
         *
         * @return void
         */
        public function __construct()
        {
                parent::__construct();
        }

        /**
         * Execute the console command.
         *
         * @return mixed
         */
        public function handle()
        {
                $this->auditGroups();
        }

        public function auditGroups()
        {
                $groups = Group::all();

                foreach($groups as $group)
                {
                        unset($snow);
                        $snow = $group->getServiceNowGroup();
                        if(!$snow)
                        {
                                print "\n\n";
                                print "************************************************************************\n";
                                print "ESCAL8 ID: " . $group->id . "\n";
                                print "************************************************************************\n";
                                print "ONCALL PHONE NUMBERS:\n";
                                print_r($group->phones);
                                print "SNOW SYS_ID: " . $group->sys_id . "\n";
                                print "ESCAL8 DESCRIPTION: " . $group->name . "\n";
                        }
                }
        }

}