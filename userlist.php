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

// This file is called by an external program for the purposes of getting a 
// list of all users in the system.  This is helpful for verifying user
// access or populating user lists or dropdowns where the external program
// wants to piggy back access to App Depot

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
		 		foreach($info as $user)
		 			{
		 				$xml .= '<user>';
		 				$xml .= '<user_id>'.$user['user_id'].'</user_id>';
		 				$xml .= '<username>'.$user['appdepot_username'].'</username>';
		 				$xml .= '<fullname>'.$user['fullname'].'</fullname>';
		 				$xml .= '<email>'.$user['email'].'</email>';
		 				$xml .= '<sourcename>'.$user['sourcename'].'</sourcename>';
		 				$xml .= '<lastlogin>'.$user['lastlogin'].'</lastlogin>';
		 				$xml .= '</user>';
		 			}
		 	}
		 
		 $xml .= "</response>";		
		 echo $xml;
         writeLog($content,$severity);
		 die();
	}
/*
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
*/
// If we're this far, then it looks like this is a valid request.  We can 
// send back the success message and required information

$users = getUserList();

$message = "User List Supplied to application $appid";
endProcess('success',$message,$users)

?>
