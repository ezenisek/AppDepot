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

if($do == 'add' || $do == 'delete')
	{
		if(!isset($_POST['users']) || !isset($_POST['apps']))
			{
				$error = "The request could not be completed, it is malformed.";
				endProcess('error',$error);
			}
		$users = array();
		$apps = array();
		
		$users = explode(',',$_POST['users']);
		$apps = explode(',',$_POST['apps']);
		
		//print_r($users);
		//print_r($apps);
		
		if(empty($users) || empty($apps))
			{
				$error = "The request could not be completed.  The User list or Application list is empty.";
				endProcess('error',$error);
			}
		
		$userlist = array();
		$applist = array();
		
		if($do == 'add')
			{
				// See where we're coming from, and, if we're adding, wipe the database fresh accordingly
				if(count($users) == 1)
				{
					// We're coming from single user edit.  Wipe all their app permissions
					$user = $users[0];
					$query = "DELETE FROM permissions WHERE user_id = '$user'";	
				}
				elseif(count($apps) == 1)
				{
					// We're coming from single app edit.  Wipe all it's user permissions
					$app = $apps[0];
					$query = "DELETE FROM permissions WHERE app_id = '$app'";
				}
				mysql_query($query) or dieLog("Could not update permissions in database because ".mysql_error());
			}
		
		foreach($users as $user)
			{
				if($user != '')
				{	
					foreach($apps as $app)
					{
						if($app != '')
						{
							// If we're doing an add, we can add the selected ones back in.
							if($do == 'add')
							{
								
								$query = "INSERT INTO permissions(user_id, app_id) " .
										"VALUES ('$user','$app')";				
								$action = 'Updated';	
							}
							else
							{
								$query = "DELETE FROM permissions WHERE user_id = '$user' " .
										"AND app_id = '$app'";
								$action = 'Deleted';			
							}
							mysql_query($query) or dieLog("Could not update permissions in database because ".mysql_error());	
							$applist[] = $app;
						}
					}
					$userlist[] = $user;
				}
			}
		$applist = implode(',',array_unique($applist));
		$userlist = implode(',',array_unique($userlist));
		$content = "$action permissions for apps($applist) to users($userlist)";
		endProcess('success',$content);					
	}

?>
