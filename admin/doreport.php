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
require_once('../includes/fpdf.php');

if(!isset($_POST['choice']))
  {
    $message = "The report generator could not perfrom the requested action.";
    include("../includes/error.php");
    exit();
  }

$username = $_SESSION['username']; 
 
$choice = $_POST['choice'];

// Report data is sent in an array with the following structure
// $reportdata[0] = 'Overall Report Title Goes Here';
// $reportdata[1]['header'] = array('header1','header2','header3');
// $reportdata[1]['data'][1] = array('col1','col2','col3');
// $reportdata[1]['data'][2] = array('col1','col2','col3');

// Subreports are done like so
// $reportdata[1]['data'][2]['sub'][0]['header'] = array('header1','header2','header3');
// $reportdata[1]['data'][2]['sub'][0]['data'][1] = array('col1','col2','col3');
// etc

// Each new row in the $reportdata array is a seperate section with the 
// appropriate header and data information.
// If sub sections are utilized, they MUST be formatted as above, including the 
// ['sub'] array just as indicated.


$reportdata = array();
$fontsize = 10;

switch($choice) {
  //********************************************
  //    BEGIN SWITCH STATEMENT FOR REPORT CHIOCE
  //   	BEGIN TEXTUAL REPORTS
  //********************************************
  
  //******************Start App List Report***********************
  case 'applist':
  $orderby = $_POST['applistorderby'];
  $order = $_POST['applistorder'];
  $query = "SELECT c.name, a.app_name, a.url, a.date_installed, a.contact_name, public FROM applications a, categories c WHERE a.category_id = c.category_id ORDER BY $orderby $order";
  $result = mysql_query($query) or dieLog("Could not get data from database for App List Report because ".mysql_error());
  $title = 'ApplicationList';
  $reportdata[0] = 'App Depot Application List';
  $reportdata[1]['header'] = array('App Name','Category','URL','Installation Date','Contact Name','Type');
  while($row = mysql_fetch_row($result))
  	{
  		if($row[5])
  			$publictext = 'Public';
  		else
  			$publictext = 'Protected';
  		$date = date('Y-m-d',strtotime($row[3]));
  		$reportdata[1]['data'][] = array($row[1],$row[0],$row[2],$date,$row[4],$publictext);
  	}
  writeLog("App List Report Run by $username"); 
  break;  // Case applist
  //************************End App List Report*************************
  
   //******************Start User List Report***********************
  case 'userlist':
  $orderby = $_POST['userlistorderby'];
  $order = $_POST['userlistorder'];
  $query = "SELECT u.user_id, u.appdepot_username, u.lastlogin, u.user_level, l.sourcename FROM users u, ldapsources l WHERE l.ldap_id = u.ldap_server ORDER BY $orderby $order";
  $result = mysql_query($query) or dieLog("Could not get data from database for User List Report because ".mysql_error());
  $title = 'UserList';
  $users = getUserList();
  $reportdata[0] = 'App Depot User List';
  $reportdata[1]['header'] = array('Username','Full Name','Auth Source','Last Login','User Type');
  while($row = mysql_fetch_row($result))
  	{
  		switch($row[3])
  			{
  				case 0:
  				$userlevel = 'Admin';
  				break;
  				case 5:
  				$userlevel = 'User';
  				break;
  			}
  		if(!empty($row[2]))
  			$date = date('Y-m-d',strtotime($row[2]));
  		else
  			$date = 'Never';
  			
  		$fullname = $users[$row[0]]['fullname'];
  		$reportdata[1]['data'][] = array($row[1],$fullname,$row[4],$date,$userlevel);
  	}
  writeLog("User List Report Run by $username");
  break;  // Case userlist
  //************************End User List Report*************************
  
  //******************Start Permissions Report***********************
  case 'permissions':
  $groupby = $_POST['permissionsgroupby'];
  $order = $_POST['permissionsorder'];
  $users = getUserList();
  $title = 'Permissions';
  $reportdata[0] = 'App Depot Permissions Report';
  $i = 1;
  if($groupby == 'application')
  	{
  		// List all applications and their users
  		$query = "SELECT app_id, app_name, url FROM applications WHERE public != 1 ORDER BY app_name $order";
  		$appresult = mysql_query($query) or dieLog("Could not get application data from database for Permissions Report because ".mysql_error());
  		while($approw = mysql_fetch_row($appresult))
  			{
  				$reportdata[$i]['header'] = array('Application','URL');
  				$reportdata[$i]['data'][1] = array($approw[1],$approw[2]);
  				$reportdata[$i]['data'][2]['sub'][0]['header'] = array('Username','Full Name','User Level','Last Usage');
  				$appid = $approw[0];
  				$query = "SELECT u.user_id, u.appdepot_username, u.user_level FROM users u, permissions p WHERE u.user_id = p.user_id AND p.app_id = $appid ORDER BY u.appdepot_username $order";
  				$userresult = mysql_query($query) or dieLog("Could not get user data from database for Permissions Report becuse ".mysql_error());
  				if(!mysql_num_rows($userresult))
  				{
  					$reportdata[$i]['data'][2]['sub'][0]['data'][] = array('No Users','','','');
  				}
  				else
  				{
  				while($userrow = mysql_fetch_row($userresult))
  					{
  						$userid = $userrow[0];	
  						$query = "SELECT MAX(timestamp) FROM app_use WHERE user_id = $userid AND app_id = $appid";
  						$xresult = mysql_query($query) or dieLog("Could not get usage information from database because ".mysql_error());
  						$xrow = mysql_fetch_row($xresult);
  						$fullname = $users[$userid]['fullname'];
  						switch($userrow[2])
				  			{
				  				case 0:
				  				$userlevel = 'Admin';
				  				break;
				  				case 5:
				  				$userlevel = 'User';
				  				break;
				  			}
  						if(!empty($xrow[0]))
  							$lastuse = date('Y-m-d H:i',strtotime($xrow[0]));
  						else
  							$lastuse = 'Never';
  						
  						$reportdata[$i]['data'][2]['sub'][0]['data'][] = array($userrow[1],$fullname,$userlevel,$lastuse);	
  					} 	
  				}
  				$i++;
  			} 	
  	}
  else
  	{
  		// List all users and their applications
  		$query = "SELECT user_id, appdepot_username, user_level FROM users ORDER BY appdepot_username $order";
  		$userresult = mysql_query($query) or dieLog("Could not get user list from database for Permissions because ".mysql_error());
  		while($userrow = mysql_fetch_row($userresult))
  			{
  				$reportdata[$i]['header'] = array('Username','Full Name','User Level');
  				$userid = $userrow[0];
  				switch($userrow[2])
				  	{
				  		case 0:
				 			$userlevel = 'Admin';
						break;
		 				case 5:
			  				$userlevel = 'User';
		  				break;
		  			}
  				$reportdata[$i]['data'][1] = array($userrow[1],$users[$userid]['fullname'],$userlevel);
  				$reportdata[$i]['data'][2]['sub'][0]['header'] = array('Application','URL','Last Usage');
  				$query = "SELECT a.app_id, a.app_name, a.url FROM applications a, permissions p WHERE a.app_id = p.app_id AND p.user_id = $userid ORDER BY a.app_name $order";
  				$appresult = mysql_query($query) or dieLog("Could not get application data from database for Permissions Report because ".mysql_error());
  				if(!mysql_num_rows($appresult))
  				{
  					$reportdata[$i]['data'][2]['sub'][0]['data'][] = array('No Applications','','','');
  				}
  				else
  				{
  				while($approw = mysql_fetch_row($appresult))
  					{
  						$appid = $approw[0];
  						$query = "SELECT MAX(timestamp) FROM app_use WHERE user_id = $userid AND app_id = $appid";
  						$xresult = mysql_query($query) or dieLog("Could not get usage information from database because ".mysql_error());
  						$xrow = mysql_fetch_row($xresult);
  						if(!empty($xrow[0]))
  							$lastuse = date('Y-m-d H:i',strtotime($xrow[0]));
  						else
  							$lastuse = 'Never';
  						
  						$reportdata[$i]['data'][2]['sub'][0]['data'][] = array($approw[1],$approw[2],$lastuse);	
  					}
  				}
  				$i++;
  			}  			
  	}
  writeLog("Permissions Report Run by $username");
  break;  // Case permissions
  //************************End Permissions Report*************************
  
   //******************Start Application Usage Report***********************
  case 'appusage':
  $orderby = $_POST['appusageorderby'];
  $order = $_POST['appusageorder'];
  $start = $_POST['appusagedatestart'];
  $end = $_POST['appusagedateend'];
  $query = "SELECT a.app_id, c.name, a.app_name, a.url, public FROM applications a, categories c WHERE a.category_id = c.category_id ORDER BY $orderby $order";
  $appresult = mysql_query($query) or dieLog("Could not get data from database for App Usage Report because ".mysql_error());
  $title = 'AppUsage';
  $users = getUserList();
  $reportdata[0] = "Application Usage Report for $start to $end";
  $i = 1;
  while($approw = mysql_fetch_row($appresult))
  	{
  		$reportdata[$i]['header'] = array('Application','Category','URL','Type');
  		$appid = $approw[0];
  		if($approw[4])
  			$type = 'Public';
  		else
  			$type = 'Protected';
  		$reportdata[$i]['data'][1] = array($approw[2],$approw[1],$approw[3],$type);
  		$reportdata[$i]['data'][2]['sub'][0]['header'] = array('Username','Fullname','Access Time');
  		$query = "SELECT u.user_id, u.appdepot_username, t.timestamp FROM users u, app_use t WHERE u.user_id = t.user_id AND t.app_id = '$appid' AND t.timestamp >= '$start' AND t.timestamp <= '$end'  ORDER BY t.timestamp DESC";
  		$userresult = mysql_query($query) or dieLog("Could not get application usage from database for App Usage Report because ".mysql_error());
  		if(!mysql_num_rows($userresult))
  			{
  				$reportdata[$i]['data'][2]['sub'][0]['data'][] = array('Not Used','','');	
  			}
  		else
  		while($userrow = mysql_fetch_row($userresult))
  			{
  				$date = date('Y-m-d H:i',strtotime($userrow[2]));
  				$fullname = $users[$userrow[0]]['fullname'];
  				$reportdata[$i]['data'][2]['sub'][0]['data'][] = array($userrow[1],$fullname,$date);	
  			}
		$i++;		
  	}
  writeLog("App Usage Report Run by $username");
  break;  // Case appusage
  //************************End App Usage Report*************************
  
   //******************Start User Activity Report***********************
  case 'useractivity':
  $orderby = $_POST['useractivityorderby'];
  $order = $_POST['useractivityorder'];
  $start = $_POST['uadatestart'];
  $end = $_POST['uadateend'];
  $title = 'UserActivity';
  $users = getUserList();
  $reportdata[0] = "User Activity Report for $start to $end";
  $query = "SELECT user_id, appdepot_username, user_level FROM users ORDER BY $orderby $order";
  $userresult = mysql_query($query) or dieLog("Could not get data from database for User Activity Report because ".mysql_error());
  $i = 1;
  while($userrow = mysql_fetch_row($userresult))
  	{
  		$reportdata[$i]['header'] = array('Username','Full Name','User Level');
  		$userid = $userrow[0];
 		switch($userrow[2])
			{
				case 0:
					$userlevel = 'Admin';
				break;
		 		case 5:
					$userlevel = 'User';
				break;
  			}
  		$reportdata[$i]['data'][1] = array($userrow[1],$users[$userid]['fullname'],$userlevel);
  		$reportdata[$i]['data'][2]['sub'][0]['header'] = array('Application','Category','Access Time');
  		$query = "SELECT a.app_id, a.app_name, c.name, t.timestamp FROM applications a, categories c, app_use t WHERE a.app_id = t.app_id AND a.category_id = c.category_id AND t.user_id = '$userid' AND t.timestamp >= '$start' AND t.timestamp <= '$end'  ORDER BY t.timestamp DESC";
  		$appresult = mysql_query($query) or dieLog("Could not get user activity from database for App Usage Report because ".mysql_error());
  		if(!mysql_num_rows($appresult))
  			{
  				$reportdata[$i]['data'][2]['sub'][0]['data'][] = array('Not Active','','');	
  			}
  		else
  		while($approw = mysql_fetch_row($appresult))
  			{
  				$date = date('Y-m-d H:i',strtotime($approw[3]));
  				$reportdata[$i]['data'][2]['sub'][0]['data'][] = array($approw[1],$approw[2],$date);	
  			}
		$i++;		
  	}
  writeLog("User Activity Report Run by $username");
  break;  // Case useractivity
  //************************End User Activity Report*************************  
  
   //******************Start Inactive User Report***********************
  case 'inactiveuser':
  $orderby = $_POST['inactiveuserorderby'];
  $order = $_POST['inactiveuserorder'];
  $start = $_POST['inactiveuserdatestart'];
  $query = "SELECT u.user_id, u.appdepot_username, u.lastlogin, u.user_level, l.sourcename FROM users u, ldapsources l WHERE l.ldap_id = u.ldap_server AND (lastlogin <= '$start' OR lastlogin is NULL) ORDER BY $orderby $order";
  $result = mysql_query($query) or dieLog("Could not get data from database for Inactive User Report because ".mysql_error());
  $title = 'InactiveUsers';
  $users = getUserList();
  $reportdata[0] = "Users Inactive Since $start";
  $reportdata[1]['header'] = array('Username','Full Name','Auth Source','Last Login','User Type');
  if(!mysql_num_rows($result))
  {
  	$reportdata[1]['data'][] = array('There are no inactive users from the date specified','','','','');
  }
  else
  while($row = mysql_fetch_row($result))
  	{
  		switch($row[3])
  			{
  				case 0:
  				$userlevel = 'Admin';
  				break;
  				case 5:
  				$userlevel = 'User';
  				break;
  			}
  		if(!empty($row[2]))
  			$date = date('Y-m-d',strtotime($row[2]));
  		else
  			$date = 'Never';
  			
  		$fullname = $users[$row[0]]['fullname'];
  		$reportdata[1]['data'][] = array($row[1],$fullname,$row[4],$date,$userlevel);
  	}
  writeLog("Inactive User Report Run by $username");
  break;  // Case inactiveuser
  //************************End Inactive User Report*************************
  
  //******************Start Inactive App Report***********************
  case 'inactiveapp':
  $orderby = $_POST['inactiveapporderby'];
  $order = $_POST['inactiveapporder'];
  $start = $_POST['inactiveappdatestart'];
  $query = "SELECT c.name, a.app_name, a.url, a.date_installed, a.contact_name, public, a.app_id FROM applications a, categories c WHERE a.category_id = c.category_id AND a.app_id IN " .
  		"(SELECT app_id FROM app_use WHERE timestamp <= '$start' OR timestamp is NULL) ORDER BY $orderby $order";
  $result = mysql_query($query) or dieLog("Could not get data from database for Inactive App Report because ".mysql_error());
  $title = 'InactiveApplications';
  $reportdata[0] = "Inactive Applications Since $start";
  $reportdata[1]['header'] = array('App Name','Category','URL','Last Used','Contact Name','Type');
  if(!mysql_num_rows($result))
  {
  	$reportdata[1]['data'][] = array('There are no inactive applications from the date specified','','','','','');
  }
  else
  while($row = mysql_fetch_row($result))
  	{
  		if($row[5])
  			$publictext = 'Public';
  		else
  			$publictext = 'Protected';
  		$query = "SELECT MAX(timestamp) FROM app_use WHERE app_id = ".$row[6];
  		$xresult = mysql_query($query) or dieLog("Could not get last useage information from database for Inactive App Report because ".mysql_error());
  		$xrow = mysql_fetch_row($xresult);
  		$date = date('Y-m-d',strtotime($xrow[0]));
  		$reportdata[1]['data'][] = array($row[1],$row[0],$row[2],$date,$row[4],$publictext);
  	}
  writeLog("Inactive App Report Run by $username");
  break;  // Case inactiveapp
  //************************End Inactive App Report*************************
  
  //******************************************
  // 	END TEXTUAL REPORTS
  //******************************************	
  
  
  //******************************************
  // 	BEGIN LOG REPORTS
  //******************************************	
  //******************Start Log Dump Report***********************
  case 'logdump':
  $orderby = $_POST['logdumporderby'];
  $order = $_POST['logdumporder'];
  $start = $_POST['lddatestart'];
  $end = $_POST['lddateend'];
  $fontsize = 8;
  $title = 'logdump';
  $reportdata[0] = "All Log Entries from $start to $end";
  $reportdata[1]['header'] = array('Timestamp','Severity','Application','Entry');
  $query = "SELECT timestamp, entry, severity, application FROM logs WHERE timestamp >= '$start' AND timestamp <= '$end' ORDER BY $orderby $order";
  $result = mysql_query($query) or dieLog("Could not get log dump from database because ".mysql_error());
  if(!mysql_num_rows($result))
  	{
  		$reportdata[1]['data'][] = array('','','','There are no log entries for the specified time period');
  	}
  else
  while($row = mysql_fetch_row($result))
  	{
  		$reportdata[1]['data'][] = array($row[0],$row[2],$row[3],$row[1]);
  	}
  writeLog("Log Dump Report Run by $username");
  break;
  //******************End Log Dump Report***********************
  
  //******************Start Log Dump Report***********************
  case 'logsearch':
  $orderby = $_POST['logsearchorderby'];
  $order = $_POST['logsearchorder'];
  $start = $_POST['lsdatestart'];
  $end = $_POST['lsdateend'];
  $terms = $_POST['logsearchtext'];
  $fontsize = 8;
  $title = 'logsearch';
  $reportdata[0] = "Log Search Results";
  $reportdata[1]['header'] = array('Timestamp','Severity','Application','Entry');
  $query = "SELECT timestamp, entry, severity, application FROM logs WHERE timestamp >= '$start' AND timestamp <= '$end' AND entry LIKE '%$terms%' ORDER BY $orderby $order";
  $result = mysql_query($query) or dieLog("Could not get log dump from database because ".mysql_error());
  if(!mysql_num_rows($result))
  	{
  		$reportdata[1]['data'][] = array('','','','There are no resulting log entries for the specified time period');
  	}
  else
  while($row = mysql_fetch_row($result))
  	{
  		$reportdata[1]['data'][] = array($row[0],$row[2],$row[3],$row[1]);
  	}
  writeLog("Log Search Report Run by $username");
  break;
  //******************End Log Dump Report***********************
  
  //******************************************
  // 	END LOG REPORTS
  //******************************************	
  
  //******************************************
  // 	END SWITCH STATEMENT FOR REPORT CHOICE
  //******************************************	
}

if((!isset($_POST['format'])) || $_POST['format'] != 'xls')
 	{
	 	createPDFReport($reportdata,$title,$fontsize);
 	}
		else
 	{
    	createExcelReport($reportdata);
  	}
	
//print_r($reportdata);
?>
