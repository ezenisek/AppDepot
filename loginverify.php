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
$error = 0;
$lockdown = getNVP('lockdown');
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
if(!isset($_POST['username']) || empty($_POST['username']))
	{
		$error = "Invalid username or username not included in request.";
		endProcess('error',$error);
	}
if(!isset($_POST['password']) || empty($_POST['password']))
	{
		$error = "Invalid password or password not included in request.";
		endProcess('error',$error);
	}

	$username = $_POST['username'];
	$password = $_POST['password'];
	// Get user from the database
	$query = "SELECT ldap_username, ldap_server, user_id, user_level, enabled FROM " .
			"users WHERE appdepot_username = '$username'";
	$result = mysql_query($query) or dieLog("Could not get user information from database because ".mysql_error());
	if(!mysql_num_rows($result))
	{
		$error = "That username ($username) does not exist in Application Depot";
		endProcess('error',$error);
	}
    $urow = mysql_fetch_row($result);

    // Check to see if we're locked down.  If we are, only admins can log in.
    if($lockdown && $urow[3])
    {
    	$error = "App Depot is currently unavailable";
    	endProcess('error',$error);
    }

	// Check that the user is enabled to login
	if(!$urow[4] && $urow[3])  // Disabled and NOT admin
	{
		$error = "This account ($username) is currently locked out. " .
				 "Please contact an administrator.";
		endProcess('error',$error);
	}
	elseif(!$urow[4])  // Disabled and IS admin, so we check how long they've been locked out.
	{
		$lastfail = getLastFailedLogin($urow[2]);
		$interval = getNVP('adminlockouttime');  // In minutes
		$interval = 60*$interval;  // In seconds
		$now = time();
		if($lastfail)
			$then = strtotime($lastfail);
		else
			$then = 0;
		if($now >= ($then+$interval))
			{
				// The current time is greater than the wait time for admins.  Enable the account.
				$query = "UPDATE users SET enabled = 1 WHERE user_id = '$urow[2]'";
	            mysql_query($query) or endProcess('error',"Your account ($username) is locked and could not be updated.  Please contact an administrator.");
	            $now = date('Y-m-d H:i:s');
				writeLog("Admin user login allowed after lockout - lockout time expired. ($lastfail - $now)");
			}
		else
			{
				$error = "This account ($username) has been temporarily locked out due to excessive failed login attempts.";
				endProcess('error',$error);
			}
	}

	// Get the LDAP information and find the dn we need to search
	$ldapusername = $urow[0];
	$ldapserver = $urow[1];
	$userid = $urow[2];
	$userlevel = $urow[3];
	$lrow = mysql_fetch_assoc($result);
	$query = "SELECT ldap_fullname_field FROM ldapsources WHERE ldap_id = '$ldapserver'";
	$result = mysql_query($query) or dieLog("Could not get ldap information from database because ".mysql_error());
    if(!mysql_num_rows($result))
	{
		$error = "The authentication source for $username is invalid";
		endProcess('error',$error);
	}
	$lrow = mysql_fetch_row($result);
	$fullnamefield = $lrow[0];

	// Check the specified LDAP Source for this user
	// Start by connecting
	if(!$conn = LDAPConnect($ldapserver))
		{
			$error = "Could not connect to the LDAP Server";
			endProcess('error',$error);
		}
	// Do the search, and put the results in an array
	$arrResults = array();
	if(!$arrResults = LDAPUserVerify($ldapusername,$conn,$ldapserver))
		{
			// User does not exist in LDAP
		    $error = "Username ($username) does not exist or Authentication Source is Invalid";
		    endProcess('error',$error);
		}
	// Now try to bind using the search results and the password given.
	// If we can bind, the username and password is good.  If not, it's bad.
    if(!@ldap_bind($conn,$arrResults[0]['dn'],$password))
		{
			// User exists but password is incorrect
		    $error = "Invalid username or password - cannot log in user $username";

		    //Log the attempt in the login table
		    addLogin($urow[2],0);
		    if(excessiveFailedLogins($urow[2]))
		    	{
		    		$error = "This account ($username) has been temporarily locked out due to excessive failed login attempts";
		    		$query = "UPDATE users SET enabled = 0 WHERE user_id = '$urow[2]'";
	            	mysql_query($query) or endProcess('error',"Your account ($username) is locked.  Please contact an administrator");
		    	}
		    endProcess('error',$error);
		}

	// If we're this far, then we're good to go.  Set up the session and send the
	// successful response
	$_SESSION['realname'] = $arrResults[0][$fullnamefield][0];
	$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
	$_SESSION['username'] = $username;
	$_SESSION['userlevel'] = $userlevel;
	$query = "UPDATE users SET lastlogin = NOW() WHERE appdepot_username = '$username'";
	addLogin($urow[2],1);
	mysql_query($query) or dieLog("Could not update last login because ".mysql_error());
	endProcess('success',"Logged In $username at level $userlevel");
?>
