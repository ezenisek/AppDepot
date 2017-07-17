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
require_once('includes/settings.php');
require_once('includes/functions.php');
dbConnect($dbhost,$database,$username,$password);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-Type: text/xml; charset=utf-8");

// This file is called by any external application to verify that the
// connection is valid, the user session is valid, the user has access to the
// application, the appdepot version is correct, and the appdepot system is
// not on lockdown.

$lockdown = getNVP('lockdown');
$adminall = getNVP('adminallapps');
$adid = getNVP('adid');
function endProcess($result,$content,$info='')
	{
		 $xml = "<?xml version=\"1.0\" ?><response><result>$result</result>";
		 $xml .= "<content>$content</content>";

		 if($result == 'error')
		 	$severity = 2;
		 else
		 	{
		 		$severity = 0;
		 		$xml .= '<sessionid>'.$info['sessionid'].'</sessionid>';
		 		$xml .= '<username>'.$info['username'].'</username>';
		 		$xml .= '<userlevel>'.$info['userlevel'].'</userlevel>';
		 		$xml .= '<fullname>'.$info['fullname'].'</fullname>';
		 		$xml .= '<email>'.$info['useremail'].'</email>';
		 		$xml .= '<authsource>'.$info['authsource'].'</authsource>';
		 	}

		 $xml .= "</response>";
		 echo $xml;
         writeLog($content,$severity);
		 die();
	}
if(!isset($_REQUEST['sessionid']))
	{
		$error = "Invalid sessionid or sessionid not included in request.";
		endProcess('error',$error);
	}
if(!isset($_REQUEST['appid']))
	{
		$error = "Invalid appid or appid not included in request.";
		endProcess('error',$error);
	}
$sessionid = $_REQUEST['sessionid'];
$appid = $_REQUEST['appid'];

// Validate the session
$query = "SELECT expires, session_data FROM sessions WHERE session_id = '$sessionid'";
$result = mysql_query($query) or endProcess('error',"Could not get session information from databse (checksession) because ".mysql_error());
if(!mysql_num_rows($result))
	{
		$error = "Invalid session supplied in this request.";
		endProcess('error',$error);
	}

$row = mysql_fetch_row($result);
$expires = $row[0];
$sessinfo = array();
$sessinfo = unserialize_session_data($row[1]);

// Check lockdown status and user enabled status.  If we're locked down and
// this user is NOT an admin, or this user is disabled in the system
// we need to destroy the session and return an error.

$level = $sessinfo['userlevel'];
$username = $sessinfo['username'];
// Check lockdown
if($lockdown && $level)
	{
		$error = "App Depot is currently unavailable due to lockdown.";
    	endProcess('error',$error);
	}

// Check user enabled
$query = "SELECT enabled,user_id,ldap_server,ldap_username FROM users WHERE appdepot_username = '$username'";
$result = mysql_query($query) or endProcess('error',"Could not get user information from databse (checksession) because ".mysql_error());
if(!mysql_num_rows($result))
	{
		$error = "User $username does not exist.";
		endProcess('error',$error);
	}
$row = mysql_fetch_row($result);
$userid = $row[1];
$auth = $row[2];
$ldapusername = $row[3];
if(!$row[0])
	{
		$error = "App Depot is currently unavailable to $username.";
    	endProcess('error',$error);
	}

// Make sure this user has access to this application.  For that, we need to get
// some app info as well.
$query = "SELECT public,app_name FROM applications WHERE app_id = '$appid'";
$result = mysql_query($query) or endProcess('error',"Could not get application information from database because ".mysql_error());
$row = mysql_fetch_row($result);
$appname = $row[1];
if(!$row[0]) // Application is not public
	{
			if(!$adminall || $level)
			{
				$query = "SELECT p_id FROM permissions WHERE user_id = '$userid' AND app_id = '$appid'";
				$result = mysql_query($query) or endProcess('error',"Could not get permission information from database because ".mysql_error());
				if(!mysql_num_rows($result))  // No permission entries can be found = user doesn't have permission for this app
					{
						$error = "$username does not have permissions to this application.";
				    	endProcess('error',$error);
					}

			}
	}

// Get some more information about this user and verify their LDAP source
$conn = LDAPConnect($auth);
$ldapinfo = LDAPUserVerify($ldapusername,$conn,$auth);
$query = "SELECT ldap_fullname_field, ldap_email_field, sourcename FROM ldapsources WHERE ldap_id = '$auth'";
$result = mysql_query($query) or endProcess('error',"Could not get LDAP information from database because ".mysql_error());
$row = mysql_fetch_row($result);
$fnfield = $row[0];
$efield = $row[1];
$authsource = $row[2];
$useremail = $ldapinfo[0][$efield][0];
$fullname = $ldapinfo[0][$fnfield][0];

// Update the session
$sessiontimeout = getNVP('sessiontimeout')*60;
$expire = time() + $sessiontimeout;
$query = "UPDATE sessions SET expires = $expire WHERE session_id = '$sessionid'";
mysql_query($query) or endProcess('error',"Could update session information in databse because ".mysql_error());


// If we're this far, then it looks like this application is a valid run.  We can
// send back the success message and required information
$message = "Session Remotely Verfied ($username,$appname)";
$info = array();
$info['sessionid'] = $sessionid;
$info['username'] = $username;
$info['userlevel'] = $level;
$info['authsource'] = $authsource;
$info['fullname'] = $fullname;
$info['useremail'] = $useremail;
endProcess('success',$message,$info)
?>
