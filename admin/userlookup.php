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
$error = 0;
if(!isset($_POST['username']) || empty($_POST['username']))
	{
		$error = "Invalid username or username not included in request.";
		endProcess('error',$error);
	}
if(!isset($_POST['auth']) || empty($_POST['auth']))
	{
		$error = "Invalid auth source, or auth source not included in request.";
		endProcess('error',$error);
	}
$username = '*'.$_POST['username'].'*';
$auth = $_POST['auth'];
function endProcess($result,$content,$Results = false)
	{
		 global $auth;
		 $xml = "<?xml version=\"1.0\" ?><response><result>$result</result>";
		 $xml .= "<content>$content</content>";
		 if($Results)
		 {
			 $sources = getAuthSources();
			 // Process the results into XML
			 foreach($Results as $key => $res)
			 	{
			 		if(is_numeric($key))
			 			{
			 				$username = $res[$sources[$auth]['ldap_name_field']][0];
			 				if(isset($res[$sources[$auth]['ldap_fullname_field']][0]))
			 					$fullname = $res[$sources[$auth]['ldap_fullname_field']][0];
			 				else
			 					$fullname = 'N/A';
			 				if(isset($res[$sources[$auth]['ldap_email_field']][0]))
			 					$email = $res[$sources[$auth]['ldap_email_field']][0];
			 				else
			 					$email = 'N/A';
			 				$xml .= "<account>";
			 				$xml .= "<username>$username</username>";
			 				$xml .= "<fullname>$fullname</fullname>";
			 				$xml .= "<email>$email</email>";
			 				$xml .="</account>";
			 			}
			 	}
		 }
		 $xml .= "</response>";		
		 echo $xml;
		 if($result == 'error')
		 	$severity = 1;
		 else
		 	$severity = 0;
         writeLog($content,$severity);
		 die();
	}
 
 
    // Connect to LDAP and search for this user
	if(!$conn = LDAPConnect($auth))
		{
			$error = "Could not connect to the LDAP Server ($auth)";
			endProcess('error',$error);
		}
	// Do the search, and put the results in an array
	$arrResults = array();
	if(!$arrResults = LDAPUserVerify($username,$conn,$auth))
		{
			// User does not exist in LDAP
		    $error = "LDAP User Search Returned No Results";
		    endProcess('error',$error);
		}
	endProcess('success','LDAP Search Returned Results',$arrResults);
?>