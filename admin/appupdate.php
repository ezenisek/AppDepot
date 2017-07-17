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
			'app_name' => '',
            'description' => '',
            'url' => '',
            'category_id' => '',
            'public' => '',
            'author' => '',
            'contact_name' => '',
            'date_installed' => '',
            'contact_email' => '' );
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
				
				$query = "INSERT INTO applications ($keylist) VALUES ($vallist)";
				mysql_query($query) or dieLog("Could not add new application to database because ".mysql_error());
				
				// Check that it's been added
				$query = "SELECT app_id FROM applications WHERE app_name = '".$vars['app_name']."'";
				$result = mysql_query($query) or dieLog("Could not check new application in database because ".mysql_error());
				if(!mysql_num_rows($result))
					{
						$error = "Could not add the new application to the database for an unknown reason.";
						endProcess('error',$error);	
					}
				endProcess('success',"Application ".$vars['app_name']." added");
			}
		else
			{
				if(!isset($_POST['app_id']))
				{
					$error = "The request could not be completed, it is malformed.";
					endProcess('error',$error);	
				}
				$id = $_POST['app_id'];
				$query = "UPDATE applications SET $editlist WHERE app_id = '$id'";
				mysql_query($query) or dieLog("Could not edit application $id in database because ".mysql_error());
				endProcess('success',"Application ".$vars['app_name']." edited");
			}	
	}

if($do == 'delete')
	{
		if(!isset($_POST['appid']))
			{
				$error = "The request could not be completed, it is malformed.";
				endProcess('error',$error);	
			}
		$id = $_POST['appid'];
		
		// Delete any application associations this app has
		$query = "DELETE FROM permissions WHERE app_id = '$id'";
		$result = mysql_query($query) or dieLog("Could not delete application permissions from the database because ".mysql_error());
		
		// Now we can delete this app
		$query = "DELETE FROM applications WHERE app_id = '$id'";
		$result = mysql_query($query) or dieLog("Could not delete application from the database because ".mysql_error());
		
		endProcess('success',"Application $id (".getAppname($id).") Deleted");
	}
	
if($do == 'active')
	{
	  // Changing an application's enabled status in the database (Enabled).
	  // Check the info
	  if(!isset($_POST['appid']) || !isset($_POST['active']))
	   	{
				$error = "The request could not be completed, it is malformed.";
				endProcess('error',$error);
		}
	  $active = $_POST['active'];
	  if($active == 'true')
	  	$active = 1;
	  else
	  	$active = 0;
	  $appid = $_POST['appid'];
	  $query = "UPDATE applications SET enabled = $active WHERE app_id = '$appid'";
	  $result = mysql_query($query) or endProcess('error',"Could not update enabled because ".mysql_error());
	   
	   // This far?  Good, we're done.
		endProcess('success',"Application $appid (".getAppname($appid).") set enabled to $active");
	}
?>

