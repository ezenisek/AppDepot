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
	
// Make sure our do is set so we know what we're doing
if(!isset($_POST['do']))
	{
		$error = "The request could not be completed, it is malformed.";
		endProcess('error',$error);
	}
$do = $_POST['do'];

if($do == 'add')
	{
		// Add the specified user, after we make sure we have all the info we need
		if(!isset($_POST['adusername']) || !isset($_POST['ldapusername']) || !isset($_POST['auth']) || !isset($_POST['level']))
			{
				$error = "The request could not be completed, it is malformed.";
				endProcess('error',$error);
			}
		$adusername = $_POST['adusername'];
		$ldapusername = $_POST['ldapusername'];
		$ldapsource = $_POST['auth'];
		$level = $_POST['level'];
		
		// Check that the username doesn't already exist.
		$query = "SELECT user_id FROM users WHERE appdepot_username = '$adusername'";
		$result = mysql_query($query) or endProcess('error',"Could not check username because ".mysql_error());
		if(mysql_num_rows($result))
			{
				$error = "Provided username already exists in App Depot";
				endProcess('error',$error);
			}
		
		// Add the user	
		$query = "INSERT INTO users (appdepot_username, ldap_username, ldap_server, user_level)" .
				" VALUES('$adusername','$ldapusername','$ldapsource','$level')";
		mysql_query($query) or endProcess('error',"Could not insert user into database because ".mysql_error());
		
		// Check that the new user was inserted
		$query = "SELECT user_id FROM users WHERE appdepot_username = '$adusername'";
		$result = mysql_query($query) or endProcess('error',"Could not check username because ".mysql_error());
		if(!mysql_num_rows($result))
			{
				$error = "Could not insert new user for an unknown reason";
				endProcess('error',$error);
			}
		
		// This far?  Good, we're done.
		endProcess('success',"User $adusername Added");
	}
	
if($do == 'delete')
	{
	   // Deleting a user from the database.  We must also be sure to delete all
	   // references they have to any applications.
	   
	   // Check the info
	   if(!isset($_POST['userid']))
	   	{
				$error = "The request could not be completed, it is malformed.";
				endProcess('error',$error);
		}
	   $userid = $_POST['userid'];
	   $query = "DELETE FROM permissions WHERE user_id = '$userid'";
	   $result = mysql_query($query) or endProcess('error',"Could not clear app permissions because ".mysql_error());
	   
	   // Get the username for the logs
	   
	   $query = "SELECT appdepot_username FROM users WHERE user_id = '$userid'";
	   $result = mysql_query($query) or endProcess('error',"Could not get user information because ".mysql_error());
	   $row = mysql_fetch_row($result);
	   $username = $row[0];
	   
	   $query = "DELETE FROM users WHERE user_id = '$userid'";
	   $result = mysql_query($query) or endProcess('error',"Could not remove user because ".mysql_error());
	   
	   // This far?  Good, we're done.
		endProcess('success',"User $username Removed");	   
	}
	
if($do == 'active')
	{
	  // Changing a user's active status in the database (Enabled).
	  // Check the info
	  if(!isset($_POST['userid']) || !isset($_POST['active']))
	   	{
				$error = "The request could not be completed, it is malformed.";
				endProcess('error',$error);
		}
	  $active = $_POST['active'];
	  if($active == 'true')
	  	$active = 1;
	  else
	  	$active = 0;
	  $userid = $_POST['userid'];
	  $query = "UPDATE users SET enabled = $active WHERE user_id = '$userid'";
	  $result = mysql_query($query) or endProcess('error',"Could not update active because ".mysql_error());
	   
	   // This far?  Good, we're done.
		endProcess('success',"User $userid (".getUsername($userid).") set active to $active");
	}

if($do == 'edit')
	{
		// Updating this user based on posted variables
		if(!isset($_POST['userid']))
			{
				$error = "The request could not be completed, it is malformed.";
				endProcess('error',$error);
			}	
	    $userid = $_POST['userid'];
		$adusername = $_POST['adusername'];
		$ldapusername = $_POST['ldapusername'];
		$ldapserver = $_POST['auth'];
		$level = $_POST['level'];
		
		// Verify that this user exists in the specified LDAP source
		$conn = LDAPConnect($ldapserver);
		if(!LDAPUserVerify($ldapusername,$conn,$ldapserver))
			{
				$error = "The user information could not be verified in the specified LDAP source";
				endProcess('error',$error);
			}
		
		// Do the update
		$query = "UPDATE users SET " .
				"appdepot_username = '$adusername'," .
				"ldap_username = '$ldapusername'," .
				"ldap_server = '$ldapserver'," .
				"user_level = '$level'" .
				" WHERE user_id = '$userid'";
		$result = mysql_query($query) or endProcess('error',"Could not update user because ".mysql_error());
		
		// This far?  Good, we're done.
		endProcess('success',"User $userid ($adusername) updated");
	}
?>
