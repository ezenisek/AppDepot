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

if($do == 'add' || $do == 'edit')
	{
		$vars = array(
			'sourcename' => '',
            'host' => '',
            'port' => '',
            'searchuser' => '',
            'password' => '',
            'base_dn' => '',
            'ldap_name_field' => '',
            'ldap_fullname_field' => '',
            'ldap_email_field' => '' );
        $keylist = '';
        $vallist = '';
        $editlist = '';
		foreach($vars as $key => $var)
			{
				if(!isset($_POST[$key]))
					{
						$error = "The request could not be completed, it is malformed.";
						endProcess('error',$error);	
					}
				$vars[$key] = scrubInput($_POST[$key]);
				$keylist .= $key.',';
				$vallist .= "'".$vars[$key]."',";
				$editlist .= "$key = '".$vars[$key]."',";
			}
		$keylist = substr($keylist,0,-1);
		$vallist = substr($vallist,0,-1);
		$editlist = substr($editlist,0,-1);
		if($do == 'add')
			{
				$query = "INSERT INTO ldapsources ($keylist) VALUES ($vallist)";
				mysql_query($query) or dieLog("Could not add new ldap source to database because ".mysql_error());
				
				// Check that it's been added
				$query = "SELECT ldap_id FROM ldapsources WHERE host = '".$vars['host']."'";
				$result = mysql_query($query) or dieLog("Could not check new ldap source in database because ".mysql_error());
				if(!mysql_num_rows($result))
					{
						$error = "Could not add the new source to the database for an unknown reason.";
						endProcess('error',$error);	
					}
				endProcess('success',"LDAP Source ".$vars['sourcename']." added");
			}
		else
			{
				if(!isset($_POST['ldapid']))
				{
					$error = "The request could not be completed, it is malformed.";
					endProcess('error',$error);	
				}
				$id = $_POST['ldapid'];
				$query = "UPDATE ldapsources SET $editlist WHERE ldap_id = '$id'";
				mysql_query($query) or dieLog("Could not edit ldap source $ldapid in database because ".mysql_error());
				endProcess('success',"LDAP Source ".$vars['sourcename']." edited");
			}	
	}

if($do == 'delete')
	{
		if(!isset($_POST['ldapid']))
			{
				$error = "The request could not be completed, it is malformed.";
				endProcess('error',$error);	
			}
		
		// Check if this Source has any users in the database.  If it does, we
		// send back an error message.  We can't delete a source that has active
		// users
		$ldapid = $_POST['ldapid'];
		$query = "SELECT user_id FROM users WHERE ldap_server = '$ldapid'";
		$result = mysql_query($query) or dieLog("Could not get LDAP users from the database because ".mysql_error());
		if(mysql_num_rows($result))
			{
				$error = "The LDAP source you're trying to delete has active users. Move or delete them first.";
				endProcess('error',$error);
			}
		
		// Now we can delete this source
		$query = "DELETE FROM ldapsources WHERE ldap_id = '$ldapid'";
		$result = mysql_query($query) or dieLog("Could not get LDAP users from the database because ".mysql_error());
		
		endProcess('success','LDAP Source Deleted');
	}

?>
