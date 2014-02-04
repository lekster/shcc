<?php
chdir(dirname(__FILE__).'/../../');
require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");
set_time_limit(0);
include_once(DIR_MODULES."control_modules/control_modules.class.php");
$ctl = new control_modules();
include_once(DIR_MODULES.'scheduled_job/scheduled_job.class.php');
$dev=new scheduled_job();
$dev->executeJobs();

