<?php
/*
    **THIS NOTICE MUST APPEAR ON ALL PAGES AND VERSIONS OF AppDepot**
       
    Application Depot.
    Copyright 2009 NMSU Research IT, New Mexico State University
    Originally developed by Ed Zenisek, Stephen Carr, and Abel Sanchez.
    
    AppDepot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    AppDepot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once('../includes/functions.php');

//Check our incomming variables
$vars = array('adminemail','dbhost','database','username','password','logfile','rootdir',
			  'ldaphost','port','searchuser','ldappassword','basedn','namefield','sourcename',
			  'fnfield','emailfield','adminuser');
foreach($vars as $var)
	{
		if(!isset($_POST[$var]))
			{
				$message = "The installation could not be completed, the request is malformed. (Missing $var)";
				require_once('../includes/error.php');
				die();
			}
	}

$adminemail = $_POST['adminemail'];
$dbhost = $_POST['dbhost'];
$database = $_POST['database'];
$username = $_POST['username'];
$password = $_POST['password'];
$logfile = urldecode($_POST['logfile']);
$rootdir = urldecode($_POST['rootdir']);
$sourcename = $_POST['sourcename'];
$host = urldecode($_POST['ldaphost']);
$port = $_POST['port'];
$searchuser = $_POST['searchuser'];
$ldappassword = $_POST['ldappassword'];
$basedn = $_POST['basedn'];
$namefield = $_POST['namefield'];
$fnfield = $_POST['fnfield'];
$emailfield = $_POST['emailfield'];
$adminuser = $_POST['adminuser'];

// Create a new ADID for this installation
$adid = '';
$possible = "0123456789bcdfghjkmnpqrstvwxyz"; 
$i = 0; 
$length = 10;
while ($i < $length) 
  { 
    // pick a random character from the possible ones
    $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);  
    $adid .= $char;
    $i++;
  }

// Set up some default values.
$nvparray = array(
	'adid' => $adid,
	'version' => '1.0',
    'adminemail' => $adminemail,
    'rootdir' => $rootdir,
    'theme' => 'Crimson',
    'lockdown' => 0,
    'adminallapps' => 1,
    'admin_showlog_history' => 5,
    'sessiontimeout' => 30,
    'sessionchecktime' => 30,
    'adminlockouttime' => 60,
    'failedloginattempts' => 5,
    'welcometext' => 'Welcome to App Depot.  Please use the dropdown menu below to choose your application.  You may collapse this menu by clicking the collapse tab on the right.',
    'logintext' => '<p>Please enter your username and password to log in to App Depot</p>',
    'adminmessage' => 'Welcome to App Depot.  Visit appdepot.org for more information.'
	);

// Setup the configuration file
		$filevars = array(
			'dbhost' => $dbhost,
			'database' => $database,
			'username' => $username,
			'password' => $password,
			'logfile' => $logfile);
			
		// Get the contents
		$file = '../includes/settings.php';
		if(!$contents = file($file))
			{
				$message = "The installation could not be completed because the settings file could not be opened.";
				require_once('../includes/error.php');
				die();
			}
		  		
		// Replace what needs to be replaced
		foreach($contents as $key => $line)
		  {
		  	if(substr(trim($line),0,1) == '$') // is this is a variable?
		  		{
		  			$linebits = explode('=',$line);
		  			$name = substr(trim($linebits[0]),1);
		  			$contents[$key] = '$'.$name.' = \''.$filevars[$name].'\';';			
		  		}	
		  	}
		  		
		 //Re-Write the file
		$writeme = '';
		foreach($contents as $line)
		  {
		     $writeme .= trim($line)."\n";
		  }
		     
		if(!$fh = fopen($file,'w+'))
		  {
		  	$message = "The installation could not be completed because the settings file could not be opened for writing.";
			require_once('../includes/error.php');
			die();
		  }
		if(!fwrite($fh,trim($writeme)))
		   {
		      $message = "The installation could not be completed because the settings file could not be written.";
			  require_once('../includes/error.php');
			  die();
		   }

// Setup the database
	dbConnect($dbhost,$database,$username,$password);
	if(!@mysql_select_db($database))
		{
			$message = "The database context could not be changed, this may be due to a permissions or connection error.  The installation has failed.";
			require_once('../includes/error.php');
			die();
		} 
	
	// Load the db creation sql script
	$dbfile = 'addbsetup.txt';
	if(!$query = file_get_contents($dbfile))
	  {
	    $message = "Could not load database settings from $dbfile.  Installation Failure.";
	    require_once('../includes/error.php');
		die();
	  }
	
	// Seperate it by command
	$qbits = explode(';',$query);
	
	// Run each command against the database
	  foreach($qbits as $q)
	    {
	      if($q != ' ' && !empty($q))
	      if(!mysql_query($q))
			{
				$message = 'Could not create database tables.  Installation Failure.<br />Reason: '.mysql_error();
				require_once('../includes/error.php');
				die();
			}
	    }
	
	// Test that the database exists as intended
	$reason = mysql_error();
    $query = "SELECT * from users";
    if(!$result = mysql_query($query))
    	{
    		$message = 'Tables could not be created because '.$reason;
    		require_once('../includes/error.php');
			die();
    	}

// Load all of our NVP values into the new database
foreach($nvparray as $key => $value)
	{
		setNVP($key,$value);	
	}

// Load the LDAP Source into the database
$query = "INSERT INTO ldapsources(host,port,searchuser,password,ldap_name_field,ldap_email_field,base_dn,ldap_fullname_field,sourcename) " .
		"VALUES('$host','$port','$searchuser','$ldappassword','$namefield','$emailfield','$basedn','$fnfield','$sourcename')";
if(!mysql_query($query))
	{
		$message = 'Could not insert LDAP information into database(1).  Installation Failure.<br />Reason: '.mysql_error();
		require_once('../includes/error.php');
		die();
	}
$query = "SELECT MAX(ldap_id) FROM ldapsources";
if(!$result = mysql_query($query) || !mysql_num_rows($result))
	{
		$message = 'Could not insert LDAP information into database(2).  Installation Failure.<br />Reason: '.mysql_error();
		require_once('../includes/error.php');
		die();
	}
$row = @mysql_fetch_row($result);
$serverid = $row[0];

// Insert the new user
$query = "INSERT INTO users(appdepot_username,ldap_username,ldap_server,user_level) " .
		"VALUES('$adminuser','$adminuser','1','0')";
if(!mysql_query($query))
	{
		$message = 'Could not insert user information into database(1).  Installation Failure.<br />Reason: '.mysql_error();
		require_once('../includes/error.php');
		die();
	}
$query = "SELECT MAX(ldap_id) FROM ldapsources";
if(!$result = mysql_query($query) || !mysql_num_rows($result))
	{
		$message = 'Could not insert user information into database(2).  Installation Failure.<br />Reason: '.mysql_error();
		require_once('../includes/error.php');
		die();
	}
	
// If we're this far, then we've created the database, updated the settings and NVP information, added the new LDAP Server and the user, and 
// App Depot should be ready to use.  The only thing remaining is to delete the installation directory and this file.  This of course, cannot be done
// by this file itself, so we forward on to the login page which has a directive at the top to delete the installation folder, if it exists.
writeLog("App Depot has been successfully installed");
$message = urlencode("App Depot has been successfully installed.  Please log in and select 'Admin' from the menu to continue setup.");
header('Location: ../login.php?i=y&message='.$message);
?>
