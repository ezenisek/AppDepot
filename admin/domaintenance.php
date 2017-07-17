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
require_once('../includes/startup.php');

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: text/xml; charset=utf-8");

$details = "Complete.\n";

function endProcess($result,$content)
	{
		 global $details;
		 $xml = "<?xml version=\"1.0\" ?><response><result>$result</result>";
		 $xml .= "<content>$content</content>";
		 
		 if($result == 'error')
		 	$severity = 1;
		 else
		 	{
		 		$severity = 0;
		 		$xml .= '<details>'.urlencode($details).'</details>';
		 	}
		 
		 $xml .= "</response>";		
		 echo $xml;
         writeLog($content,$severity);
		 die();
	}
if(!isset($_POST['type']))
	{
		$error = "The request could not be completed, it is malformed.";
		endProcess('error',$error);
	}
$type = $_POST['type'];
if($type == 'iu' || $type == 'ia' || $type == 'lt')
	{
		if(!isset($_POST['date']))
			{
				$error = "The request could not be completed, it is malformed.";
				endProcess('error',$error);	
			}	
		$date = $_POST['date'];
	}
else
	$date = '';

if($type == 'db')
	{
		// Do a database consistency check.  This checks that all listed 
		// permissions belong to a real user and a real application.    
		// We use a function for this so we can re-use it in other
		// places.
		if(!$details = dbConsistencyCheck())
			{
				endProcess('error','Could not verify database consistency');
			}
		endProcess('success','Database Consistency Verified');
	}
if($type == 'lt')
	{
		// Truncate the database logs prior to the given date.  This is just
		// a simple delete.
		$query = "DELETE FROM logs WHERE timestamp < '$date'";
		mysql_query($query) or endProcess('error',"Could not truncate logs because ".mysql_error());
		$details = mysql_affected_rows()." log entries have been deleted.";
		endProcess('success',"Truncated Logs Prior to $date");	
	}
if($type == 'iu')
	{
		// Inactive user check.  This checks for users who have not logged in
		// since the given date and removes them from the database.  It also
		// removes their permissions.
		// We do a db consistency check when we're done.
		$users = '';
		$usernames = '';
		$query = "SELECT user_id, appdepot_username FROM users WHERE lastlogin < '$date'";
		$result = mysql_query($query) or endProcess('error',"Could not retrieve inactive users from database because ".urlencode(mysql_error()));
		while($row = mysql_fetch_row($result))
			{			
				$details .= 'Found inactive user '.$row[1].". Deleting...\n";
				$users .= $row[0].',';
				$usernames .= $row[1].',';
			}
		if(mysql_num_rows($result))
		{
			$users = substr($users,0,-1);  // Remove last comma
			$usernames = substr($usernames,0,-1); // Remove last comma
			$query = "DELETE FROM permissions WHERE user_id IN ($users)";
			mysql_query($query) or endProcess('error',"Could not remove inactive user permissions because ".urlencode(mysql_error()));
			$query = "DELETE FROM users WHERE user_id in ($users)";
			mysql_query($query) or endProcess('error',"Could not remove inactive users because ".urlencode(mysql_error()));
			$details .= mysql_affected_rows()." users have been deleted.";
		}
		else
			$details .= "No inactive users were found.\n";
		
		if(!$cdetails = dbConsistencyCheck())
			{
				endProcess('error','Could not verify database consistency');
			}
		$details .= "\n".$cdetails;
		 
		endProcess('success',"Removed inactive users ($usernames)");
	}
if($type == 'ia')
	{
		// Inactive application check.  This checks for apps which have not ben used
		// since the given date and removes them from the database.  It also
		// removes their permissions.
		// We do a db consistency check when we're done.		
		$apps = '';
		$appnames = '';
		$query = "SELECT app_id, app_name FROM applications WHERE app_id IN (SELECT app_id FROM app_use WHERE timestamp < '$date')";
		$result = mysql_query($query) or endProcess('error',"Could not retrieve inactive apps from database because ".urlencode(mysql_error()));
		while($row = mysql_fetch_row($result))
			{			
				$details .= 'Found inactive application '.$row[1].". Deleting...\n";
				$apps .= $row[0].',';
				$appnames .= $row[1].',';
			}
		if(mysql_num_rows($result))
		{
			$apps = substr($apps,0,-1);  // Remove last comma
			$appnames = substr($appnames,0,-1); // Remove last comma
			$query = "DELETE FROM permissions WHERE app_id in ($apps)";
			mysql_query($query) or endProcess('error',"Could not remove inactive application permissions because ".urlencode(mysql_error()));
			$query = "DELETE FROM applications WHERE app_id in ($apps)";
			mysql_query($query) or endProcess('error',"Could not remove inactive applications because ".urlencode(mysql_error()));
			$details .= mysql_affected_rows()." applications have been deleted.";
		}
		else
			$details .= "No inactive applications were found.\n";
		
		if(!$cdetails = dbConsistencyCheck())
			{
				endProcess('error','Could not verify database consistency');
			}
		$details .= "\n".$cdetails;
		 
		endProcess('success',"Removed inactive applications ($appnames)");
	}
if($type == 'uu')
	{
		// Unlinked user check.  This checks for users who do not exist in their 
		// listed ldap source and deletes them.  This ensures that users who have
		// been removed from ldap can easily be removed from app depot as well.
		// We do a db consistency check when we're done.	
		$users = '';
		$usernames = '';
		$found = false;
		$userlist = getUserList();
		foreach($userlist as $user)
			{
				if($user['email'] == 'Not Found In Source' && $user['fullname'] == 'Not Found In Source')
					{
						// This user cannot be found in the specified LDAP source, so we delete them from the database
						$uid = $user['user_id'];
						$found = true;
						$users .= $uid.',';
						$usernames .= $user['appdepot_username'];
					}	
			}
		if($found)
			{
				$users = substr($users,0,-1); // chop off last comma
				$usernames = substr($usernames,0,-1); // chop off last comma
				$query = "DELETE FROM permissions WHERE user_id IN ($users)";
				mysql_query($query) or endProcess('error',"Could not remove inactive user permissions because ".urlencode(mysql_error()));
				$query = "DELETE FROM users WHERE user_id in ($users)";
				mysql_query($query) or endProcess('error',"Could not remove inactive users because ".urlencode(mysql_error()));
				$details .= mysql_affected_rows()." users have been deleted.";	
			}
		else
			$details .= "No unlinked users were found.\n";
		
		if(!$cdetails = dbConsistencyCheck())
			{
				endProcess('error','Could not verify database consistency');
			}
		$details .= "\n".$cdetails;
		 
		endProcess('success',"Removed unlinked users ($usernames)");
	}
?>
