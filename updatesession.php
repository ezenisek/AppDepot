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
function endProcess($result,$content)
	{
		 $xml = "<?xml version=\"1.0\" ?><response><result>$result</result>";
		 $xml .= "<content>$content</content></response>";		
		 echo $xml;
		 if($result == 'error')
		 	$severity = 1;
		 else
		 	$severity = 0;
         writeLog($content,$severity);
		 die();
	}
if(!isset($_REQUEST['sessionid']))
	{
		$error = "Invalid sessionid or sessionid not included in request.";
		endProcess('error',$error);		
	}
if(!isset($_REQUEST['app']))
	{
		$app = "Unspecified";	
	}
else
	{
		$app = $_REQUEST['app'];
	}
$sessionid = $_REQUEST['sessionid'];

// Check the database to be sure that this session is registered and valid
$query = "SELECT session_id FROM sessions WHERE session_id = '$sessionid' AND expires > UNIX_TIMESTAMP()";
$result = mysql_query($query) or dieLog("Could not get session information from databse (updatesession) because ".mysql_error());
if(!mysql_num_rows($result))
	{
		$error = "Invalid session supplied in this request.";
		endProcess('error',$error);	
	}

// If the session is valid and current, we can go ahead and update it.
$sessiontimeout = getNVP('sessiontimeout')*60;
$expire = time() + $sessiontimeout;
$query = "UPDATE sessions SET expires = $expire WHERE session_id = '$sessionid'";
mysql_query($query) or dieLog("Could update session information in databse (updatesession) because ".mysql_error());

// If we're this far, we've updated the session and all is good.
endProcess('success','Session Updated');
?>
