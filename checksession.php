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
$error = 0;
$lockdown = getNVP('lockdown');
function endProcess($result,$content,$mins = 0, $secs = 0)
	{
		 $xml = "<?xml version=\"1.0\" ?><response><result>$result</result>";
		 $xml .= "<content>$content</content>";
		 if($mins || $secs)
		 	{
		 		$xml .= "<minutes>$mins</minutes>";
		 		$xml .= "<seconds>$secs</seconds>";
		 	}
		 $xml .= "</response>";		
		 echo $xml;
		 if($result == 'error')
		 	{
		 		$severity = 1;
         		writeLog($content,$severity);
		 	}
		 die();
	}
if(!isset($_REQUEST['sessionid']))
	{
		$error = "Invalid sessionid or sessionid not included in request.";
		endProcess('error',$error);		
	}
$sessionid = $_REQUEST['sessionid'];

// Get session informaition
$query = "SELECT expires, session_data FROM sessions WHERE session_id = '$sessionid'";
$result = mysql_query($query) or dieLog("Could not get session information from databse (checksession) because ".mysql_error());
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
// we need to destroy the session and return an error so the user will be
// forcefully logged out.
$level = $sessinfo['userlevel'];
$username = $sessinfo['username'];
// Check lockdown
if($lockdown && $level)
	{
		$error = "App Depot is currently unavailable";	
    	endProcess('error',$error);	
	}
// Check user enabled
$query = "SELECT enabled FROM users WHERE appdepot_username = '$username'";
$result = mysql_query($query) or dieLog("Could not get user information from databse (checksession) because ".mysql_error());
if(!mysql_num_rows($result))
	{
		$error = "User $username does not exist.";
		endProcess('error',$error);	
	}
$row = mysql_fetch_row($result);
if(!$row[0])
	{
		$error = "App Depot is currently unavailable";	
    	endProcess('error',$error);
	}
	
//Check to see how much time is left in this session
$diff = $expires - time();
$minutes = floor($diff/60);
$seconds = $diff % 60;

// Send the info back
endProcess('success','Session Verified',$minutes,$seconds);
?>
