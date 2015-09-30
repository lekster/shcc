#!/usr/bin/php
<?php

include '../htdocs/bootstrap.php';
chdir(GIRAR_BASE_DIR);

include ("pbr-lib-common/src/LDAP/adLDAP-4.0.4/src/adLDAP.php");







$ldaprdn  = 'immomsk\asmirnov';     // ldap rdn или dn
$ldappass = '1234';  // ассоциированный пароль

// соединение с сервером
$ldapconn = ldap_connect("Dc1.immo")
    or die("Не могу соединиться с сервером LDAP.");

if ($ldapconn) {

    // привязка к ldap-серверу
    $ldapbind = ldap_bind($ldapconn, $ldaprdn, $ldappass);

    // проверка привязки
    if ($ldapbind) {
        echo "LDAP-привязка успешна...";
    } else {
        echo "LDAP-привязка не удалась...";
    }

}



var_dump("*********************************************");


$options = array
(
	"base_dn" => "OU=inform-mobil,DC=immomsk,DC=ru",
	"account_suffix" => "",
	//"base_dn" => "OU=inform-mobil,DC=immo",
	"domain_controllers" => array("Dc1.immo"),
	"admin_username" => "Rational-search",
	"admin_password" => "e2LKvtot",
	"ad_port" => "389",
	"use_ssl" => false,
);


try {
    $adldap = new adLDAP($options);
}
catch (adLDAPException $e) {
    echo $e;
    exit();   
}
//var_dump($adldap);

$result = $adldap->authenticate("immomsk\asmirnov", "1234");
var_dump($result);


/*
if (array_key_exists("account_suffix",$options)){ $this->accountSuffix = $options["account_suffix"]; }
            if (array_key_exists("base_dn",$options)){ $this->baseDn = $options["base_dn"]; }
            if (array_key_exists("domain_controllers",$options)){ 
                if (!is_array($options["domain_controllers"])) { 
                    throw new adLDAPException('[domain_controllers] option must be an array');
                }
                $this->domainControllers = $options["domain_controllers"]; 
            }
            if (array_key_exists("admin_username",$options)){ $this->adminUsername = $options["admin_username"]; }
            if (array_key_exists("admin_password",$options)){ $this->adminPassword = $options["admin_password"]; }
            if (array_key_exists("real_primarygroup",$options)){ $this->realPrimaryGroup = $options["real_primarygroup"]; }
            if (array_key_exists("use_ssl",$options)){ $this->setUseSSL($options["use_ssl"]); }
            if (array_key_exists("use_tls",$options)){ $this->useTLS = $options["use_tls"]; }
            if (array_key_exists("recursive_groups",$options)){ $this->recursiveGroups = $options["recursive_groups"]; }
            if (array_key_exists("ad_port",$options)){ $this->setPort($options["ad_port"]); } 
            if (array_key_exists("sso",$options)){ 
*/

die();













/*
Examples file

To test any of the functions, just change the 0 to a 1.
*/

//error_reporting(E_ALL ^ E_NOTICE);

include (dirname(__FILE__) . "/../src/adLDAP.php");
try {
    $adldap = new adLDAP($options);
}
catch (adLDAPException $e) {
    echo $e;
    exit();   
}
//var_dump($ldap);

echo ("<pre>\n");

// authenticate a username/password
if (0) {
	$result = $adldap->authenticate("username", "password");
	var_dump($result);
}

// add a group to a group
if (0) {
	$result = $adldap->group()->addGroup("Parent Group Name", "Child Group Name");
	var_dump($result);
}

// add a user to a group
if (0) {
	$result = $adldap->group()->addUser("Group Name", "username");
	var_dump($result);
}

// create a group
if (0) {
	$attributes=array(
		"group_name"=>"Test Group",
		"description"=>"Just Testing",
		"container"=>array("Groups","A Container"),
	);
	$result = $adldap->group()->create($attributes);
	var_dump($result);
}

// retrieve information about a group
if (0) {
    // Raw data array returned
	$result = $adldap->group()->info("Group Name");
	var_dump($result);
}

// create a user account
if (0) {
	$attributes=array(
		"username"=>"freds",
		"logon_name"=>"freds@mydomain.local",
		"firstname"=>"Fred",
		"surname"=>"Smith",
		"company"=>"My Company",
		"department"=>"My Department",
		"email"=>"freds@mydomain.local",
		"container"=>array("Container Parent","Container Child"),
		"enabled"=>1,
		"password"=>"Password123",
	);
	
    try {
    	$result = $adldap->user()->create($attributes);
	    var_dump($result);
    }
    catch (adLDAPException $e) {
        echo $e;
        exit();   
    }
}

// retrieve the group membership for a user
if (0) {
	$result = $adldap->user()->groups("username");
	print_r($result);
}

// retrieve information about a user
if (0) {
    // Raw data array returned
	$result = $adldap->user()->info("username");
	print_r($result);
}

// check if a user is a member of a group
if (0) {
	$result = $adldap->user()->inGroup("username","Group Name");
	var_dump($result);
}

// modify a user account (this example will set "user must change password at next logon")
if (0) {
	$attributes=array(
		"change_password"=>1,
	);
	$result = $adldap->user()->modify("username",$attributes);
	var_dump($result);
}

// change the password of a user. It must meet your domain's password policy
if (0) {
    try {
        $result = $adldap->user()->password("username","Password123");
        var_dump($result);
    }
    catch (adLDAPException $e) {
        echo $e; 
        exit();   
    }
}

// see a user's last logon time
if (0) {
    try {
        $result = $adldap->user()->getLastLogon("username");
        var_dump(date('Y-m-d H:i:s', $result));
    }
    catch (adLDAPException $e) {
        echo $e; 
        exit();   
    }
}

// list the contents of the Users OU
if (0) {
    $result=$adldap->folder()->listing(array('Users'), adLDAP::ADLDAP_FOLDER, false);
    var_dump ($result);   
}
?>