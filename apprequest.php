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
require_once('includes/startup.php');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: text/xml; charset=utf-8");
function endProcess($result,$content,$name = '', $url = '')
	{
		 $xml = "<?xml version=\"1.0\" ?><response><result>$result</result>";
		 $xml .= "<content>$content</content>";		
		 if($result == 'error')
		 	{
		 		$severity = 1;
		 	}
		 else
		 	{
		 		$severity = 0;
		 		$xml .= "<url>$url</url>";
		 		$xml .= "<name>$name</name>";
		 	}
		 $xml .= "</response>";
		 echo $xml;
         writeLog($content,$severity);
		 die();
	}
	
// Make sure our do is set so we know what we're doing
if(!isset($_POST['appid']) || !isset($_POST['userid']))
	{
		$error = "The request could not be completed, it is malformed.  Please contact your administrator.";
		endProcess('error',$error);
	}
$appid = $_POST['appid'];
$userid = $_POST['userid'];

if(getNVP('adminallapps') && !sessionVerify())
	$admin = true;
else
	$admin = false;

// Get some informaiton about this application.  
$query = "SELECT app_name, url, enabled, public FROM applications WHERE app_id = '$appid'";
$result = mysql_query($query) or endProcess('error',"Could not get app information from database because ".mysql_error());
$approw = mysql_fetch_row($result);
if(!$approw[2])  // If not enabled
	{
		$error = 'This application is currently disabled.  It may be undergoing maintenance or be temporarily offline.';
		endProcess('error',$error);	
	}

if(!$approw[3] && !$admin) // If not public and not admin
	{
		// Double check to be sure this user has access to this application.
		$query = "SELECT u.appdepot_username FROM users u, permissions p WHERE p.app_id = '$appid' AND p.user_id = u.user_id";
		$result = mysql_query($query) or endProcess('error',"Could not get user/app information from database because ".mysql_error());
		if(!mysql_num_rows($result))
			{
				$error = 'You do not appear to have access to this application.';
				endProcess('error',$error);	
			}
	}

// Append the needed information to the end of the url
$sess = session_id();
$adid = getNVP('adid');
if(stristr($approw[1],'?'))
	$char = '&';
else
	$char = '?';
	
if(!$approw[3])
	$url = rawurlencode($approw[1].$char."adid=$adid&sessionid=$sess");
else
	$url = $approw[1];

// Write this access to the database
	$query = "INSERT INTO app_use(app_id,user_id,timestamp) VALUES ('$appid','$userid',NOW())";
	mysql_query($query) or dieLog("Could not insert usage information in the database because ".mysql_error());
		
// Send the confirmation
	endProcess('success',"Access to application ".getAppname($appid)." granted to user ".getUsername($userid),$approw[0],$url);
?>
