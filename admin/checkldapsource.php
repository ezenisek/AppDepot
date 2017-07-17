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
$userlevel = sessionVerify();
if($userlevel)
	{
		dieLog("Unauthorized access attempt to Admin Page");
	}
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
if(!isset($_POST['host']) || !isset($_POST['port']) || !isset($_POST['user']) || !isset($_POST['password']))
	{
		$error = "The request could not be completed, it is malformed.";
		endProcess('error',$error);
	}
$host = $_POST['host'];
$port = $_POST['port'];
$user = $_POST['user'];
$password = $_POST['password'];

// Check that the connection settings work
if(!$ds=@ldap_connect($host,$port))
   { 
      $error = "Could not connect to LDAP Server at $host.";
      endProcess('error',$error);
   }
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
                 
if(!$ldapBind = @ldap_bind($ds, $user, $password))
   { 
      $error = "Access Denied to $host";
      endProcess('error',$error);
      return false;
   }
  
// If we're this far, we're good.
endProcess('Success','Connection Success');
?>
