<?
/**
* Project Config
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.1
*/


 Define('DB_HOST', 'localhost_123');
 Define('DB_NAME', 'majordomo_devel_notwork');
 Define('DB_USER', 'root_123');
 Define('DB_PASSWORD', 'root_123');

 Define('DIR_TEMPLATES', "./templates/");
 Define('DIR_MODULES', "./modules/");
 Define('DEBUG_MODE', 1);
 Define('UPDATES_REPOSITORY_NAME', 'smarthome');

 Define('PROJECT_TITLE', 'MajordomoSL');
 Define('PROJECT_BUGTRACK', "bugtrack@smartliving.ru");

 if (@$_ENV["COMPUTERNAME"]) {
  Define('COMPUTER_NAME', strtolower($_ENV["COMPUTERNAME"])); 
 } else {
  Define('COMPUTER_NAME', 'mycomp');                       // Your computer name (optional)
 }


 Define('DOC_ROOT', dirname(__FILE__));              // Your htdocs location (should be detected automatically)

 Define('SERVER_ROOT', 'c:/_majordomo');
 

 if (@$_ENV["S2G_BASE_URL"]) {
  Define('BASE_URL', $_ENV["S2G_BASE_URL"]);
 } else {
  Define('BASE_URL', 'http://127.0.0.1:80');              // Your base URL:port (!!!)
 }


 Define('ROOT', DOC_ROOT."/");
 Define('ROOTHTML', "/");
 Define('PROJECT_DOMAIN', @$_SERVER['SERVER_NAME']);

 Define('ONEWIRE_SERVER', 'tcp://localhost:8234');    // 1-wire OWFS server

 /*
 Define('HOME_NETWORK', '192.168.0.*');                  // home network (optional)
 Define('EXT_ACCESS_USERNAME', 'user');                  // access details for external network (internet)
 Define('EXT_ACCESS_PASSWORD', 'password');
 */

 //Define('DROPBOX_SHOPPING_LIST', 'c:/data/dropbox/list.txt');  // (Optional)

?>