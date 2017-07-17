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
$userlevel = sessionVerify();
$theme = getNVP('theme');
if($userlevel)
	{
		dieLog("Unauthorized access attempt to Admin Page");
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="../themes/<?php echo $theme; ?>/style.css" />
	
	<title>App Depot Utilities</title>
	<script type="text/javascript" src="../javascript/motionpack.js"></script>
	<script type="text/javascript" src="../javascript/jacs.js"></script>
	<script type="text/javascript">
	function in_array(string, array)
	{
	   for (i = 0; i < array.length; i++)
	   {
	      if(array[i] == string)
	      {
	         return true;
	      }
	   }
	return false;
	}

	var graphs = new Array("apppopularity","userpopularity","adactivity","specappactivity","specuseractivity","loggrowth","logincompare");
	function doReport() {
		var val = '';
		for(i=0;i<document.reports.choice.length;i++)
			{
				if(document.reports.choice[i].checked)
					val = document.reports.choice[i].value;
			}
		if(in_array(val,graphs))
		{
			document.reports.action = 'dograph.php';
		}
		else
		{
			document.reports.action = 'doreport.php';			
		}
		document.reports.submit();	
	}
	</script>
</head>
<body>
<script type="text/javascript" src="../javascript/wz_tooltip.js"></script>
<form name="reports" action="doreport.php" method="post">
<br />
<div class="listheader">App Depot Reports</div>
<div class="dropslider">
<br />
	<div><h2><a href="#a" onClick="toggleSlide('textreportslider')">Textual Reports</a></h2></div>
	<div class="infobox" id="textreportslider" style="display:none;height:220px">
	<div class="smalltext"></div>
	<table class="listtable">
	<tr>
		<td class="listheader">Report Type</td>
		<td class="listheader" colspan="3">Report Options</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="applist"/>
	      <span class="tip" onmouseover="Tip('A report of all applications and some information about them.')"
	      onmouseout="UnTip()">Application List</span>
	      </td>
	      <td>
	      Ordered by 
	      <select name="applistorderby">
	      <option value="date_installed">Date Installed</option>
	      <option value="app_name">Application Name</option>
	      <option value="category_id">Category</option>
	      </select>
	      </td>
	      <td>
	      <select name="applistorder">
	      <option value="desc">Descending&nbsp;</option>
	      <option value="asc">Ascending</option>
	      </select>
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="userlist"/>
	      <span class="tip" onmouseover="Tip('A report of all users and some information about them.')"
	      onmouseout="UnTip()">User List</span>
	      </td>
	      <td>
	      Ordered by 
	      <select name="userlistorderby">
	      <option value="appdepot_username">Username</option>
	      <option value="lastlogin">Last Login</option>
	      <option value="ldap_server">Authentication Source</option>
	      <option value="user_level">User Level</option>
	      </select>
	      </td>
	      <td>
	      <select name="userlistorder">
	      <option value="desc">Descending&nbsp;</option>
	      <option value="asc">Ascending</option>
	      </select>
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="permissions"/>
	      <span class="tip" onmouseover="Tip('A report of App Depot permissions.')"
	      onmouseout="UnTip()">Permissions Report</span>
	      </td>
	      <td>
	      Grouped by 
	      <select name="permissionsgroupby">
	      <option value="application">Application Name</option>
	      <option value="user">Username</option>
	      </select>
	      </td>
	      <td>
	      <select name="permissionsorder">
	      <option value="desc">Descending&nbsp;</option>
	      <option value="asc">Ascending</option>
	      </select>
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="appusage"/>
	      <span class="tip" onmouseover="Tip('A report detailing application usage.')"
	      onmouseout="UnTip()">Application Usage</span>
	      </td>
	      <td>
	      Ordered by 
	      <select name="appusageorderby">
	      <option value="app_name">Application Name</option>
	      <option value="category_id">Category</option>
	      </select>
	      </td>
	      <td>
	      <select name="appusageorder">
	      <option value="desc">Descending&nbsp;</option>
	      <option value="asc">Ascending</option>
	      </select>
	      </td>
	      <td>
	      For period from 
	      <input type="text" name="appusagedatestart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.appusagedatestart,this)" />
	      to <input type="text" name="appusagedateend" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.appusagedateend,this)" />   
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="useractivity"/>
	      <span class="tip" onmouseover="Tip('A report detailing user activity.')"
	      onmouseout="UnTip()">User Activity</span>
	      </td>
	      <td>
	      Ordered by 
	      <select name="useractivityorderby">
	      <option value="appdepot_username">Username</option>
	      <option value="lastlogin">Last Login</option>
	      </select>
	      </td>
	      <td>
	      <select name="useractivityorder">
	      <option value="desc">Descending&nbsp;</option>
	      <option value="asc">Ascending</option>
	      </select>
	      </td>
	      <td>
	      For period from 
	      <input type="text" name="uadatestart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.uadatestart,this)" />
	      to <input type="text" name="uadateend" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.uadateend,this)" />   
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="inactiveuser"/>
	      <span class="tip" onmouseover="Tip('A report showing all users who have not logged in since the specified date.')"
	      onmouseout="UnTip()">Inactive Users</span>
	      </td>
	      <td>
	      Ordered by 
	      <select name="inactiveuserorderby">
	      <option value="appdepot_username">Username</option>
	      <option value="lastlogin">Last Login</option>
	      </select>
	      </td>
	      <td>
	      <select name="inactiveuserorder">
	      <option value="desc">Descending&nbsp;</option>
	      <option value="asc">Ascending</option>
	      </select>
	      </td>
	      <td>
	      List users inactive since  
	      <input type="text" name="inactiveuserdatestart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.inactiveuserdatestart,this)" /> 
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="inactiveapp"/>
	      <span class="tip" onmouseover="Tip('A report showing all applications which have not been used since the specified date.')"
	      onmouseout="UnTip()">Inactive Applications</span>
	      </td>
	      <td>
	      Ordered by 
	      <select name="inactiveapporderby">
	      <option value="app_name">Application Name</option>
	      <option value="lastlogin">Date Last Used</option>
	      </select>
	      </td>
	      <td>
	      <select name="inactiveapporder">
	      <option value="desc">Descending&nbsp;</option>
	      <option value="asc">Ascending</option>
	      </select>
	      </td>
	      <td>
	      List applications inactive since  
	      <input type="text" name="inactiveappdatestart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.inactiveappdatestart,this)" /> 
	      </td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	</table>
	</div>
	
	
	<div><h2><a href="#b" onClick="toggleSlide('graphreportslider')">Graphical Reports</a></h2></div>
	<div class="infobox" id="graphreportslider" style="display:none;height:195px">
	<div class="smalltext"></div>
	<table class="listtable">
	<tr>
		<td class="listheader">Graph Type</td>
		<td class="listheader" colspan="4">Graph Options</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="adactivity"/>
	      <span class="tip" onmouseover="Tip('A graph showing the overall activity of App Depot.')"
	      onmouseout="UnTip()">All App Depot Activity</span>
	      </td>
	      <td>
	      Listed by  
	      <select name="adactivitylistby">
	      <option value="hour">Hour</option>
	      <option value="day" selected>Day</option>
	      <option value="month">Month</option>
	      </select>
	      </td>
	      <td>
	      For period from 
	      <input type="text" name="adactivitystart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.adactivitystart,this)" />
	      to <input type="text" name="adactivityend" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.adactivityend,this)" />   
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="logincompare"/>
	      <span class="tip" onmouseover="Tip('A graph showing App Depot logins and failures.')"
	      onmouseout="UnTip()">App Depot Login Success/Failure</span>
	      </td>
	      <td>
	      Listed by  
	      <select name="logincomparelistby">
	      <option value="hour">Hour</option>
	      <option value="day" selected>Day</option>
	      <option value="month">Month</option>
	      </select>
	      </td>
	      <td>
	      For period from 
	      <input type="text" name="logincomparestart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.logincomparestart,this)" />
	      to <input type="text" name="logincompareend" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.logincompareend,this)" />   
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="specappactivity"/>
	      <span class="tip" onmouseover="Tip('A graph showing the activity of a specific application.')"
	      onmouseout="UnTip()">Specific Application Activity</span>
	      </td>
	      <td>
	      For  
	      <select name="specappactivityapp">
	      <?php
	      	$query = "SELECT app_id, app_name FROM applications";
	      	$result = mysql_query($query) or dieLog("Could not get application list for graph reports because ".mysql_error());
	      	while($row = mysql_fetch_row($result))
	      		{
	      			echo '<option value="'.$row[0].'">'.$row[1].'</option>';
	      		}
	      ?>
	      </select>
	      Listed by  
	      <select name="specappactivitylistby">
	      <option value="hour">Hour</option>
	      <option value="day" selected>Day</option>
	      <option value="month">Month</option>
	      </select>
	      </td>
	      <td>
	      For period from 
	      <input type="text" name="specappactivitystart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.specappactivitystart,this)" />
	      to <input type="text" name="specappactivityend" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.specappactivityend,this)" />   
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="specuseractivity"/>
	      <span class="tip" onmouseover="Tip('A graph showing the activity of a specific user.')"
	      onmouseout="UnTip()">Specific User Activity</span>
	      </td>
	      <td>
	      For  
	      <select name="specuseractivityuser">
	      <?php
	      	$query = "SELECT user_id, appdepot_username FROM users";
	      	$result = mysql_query($query) or dieLog("Could not get user list for graph reports because ".mysql_error());
	      	while($row = mysql_fetch_row($result))
	      		{
	      			echo '<option value="'.$row[0].'">'.$row[1].'</option>';
	      		}
	      ?>
	      </select>
	      Listed by  
	      <select name="specuseractivitylistby">
	      <option value="hour">Hour</option>
	      <option value="day" selected>Day</option>
	      <option value="month">Month</option>
	      </select>
	      </td>
	      <td>
	      For period from 
	      <input type="text" name="specuseractivitystart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.specuseractivitystart,this)" />
	      to <input type="text" name="specuseractivityend" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.specuseractivityend,this)" />   
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="apppopularity"/>
	      <span class="tip" onmouseover="Tip('A graph showing the popularity of the top applications.')"
	      onmouseout="UnTip()">Application Popularity</span>
	      </td>
	      <td>
	      Show top  
	      <input name="apppopularitytop" type="text" size="1" value="5" /> 
	      Apps
	      </td>
	      <td>
	      For period from 
	      <input type="text" name="apppopularitystart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.apppopularitystart,this)" />
	      to <input type="text" name="apppopularityend" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.apppopularityend,this)" />   
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="userpopularity"/>
	      <span class="tip" onmouseover="Tip('A graph showing the activity of the top users.')"
	      onmouseout="UnTip()">User Activity</span>
	      </td>
	      <td>
	      Show top  
	      <input name="userpopularitytop" type="text" size="1" value="5" /> 
	      Users
	      </td>
	      <td>
	      For period from 
	      <input type="text" name="userpopularitystart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.userpopularitystart,this)" />
	      to <input type="text" name="userpopularityend" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.userpopularityend,this)" />   
	      </td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	</table>
	</div>


	<div><h2><a href="#b" onClick="toggleSlide('logreportslider')">Log Reports</a></h2></div>
	<div class="infobox" id="logreportslider" style="display:none;height:160px">
	<div class="smalltext"></div>
	<table class="listtable">
	<tr>
		<td class="listheader">Report Type</td>
		<td class="listheader" colspan="4">Report Options</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="logdump"/>
	      <span class="tip" onmouseover="Tip('A list of all log entries in the database.')"
	      onmouseout="UnTip()">Full Database Log Dump</span>
	      </td>
	      <td>
	      Ordered by 
	      <select name="logdumporderby">
	      <option value="timestamp">Timestamp</option>
	      <option value="severity">Severity</option>
	      <option value="application">Application</option>
	      </select>
	      </td>
	      <td>
	      <select name="logdumporder">
	      <option value="desc">Descending&nbsp;</option>
	      <option value="asc">Ascending</option>
	      </select>
	      </td>
	      <td>
	      For period from 
	      <input type="text" name="lddatestart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.lddatestart,this)" />
	      to <input type="text" name="lddateend" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.lddateend,this)" />   
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="logsearch"/>
	      <span class="tip" onmouseover="Tip('A report searching the database logs for the requested information.')"
	      onmouseout="UnTip()">Log Search</span>
	      </td>
	      <td>
	      Ordered by 
	      <select name="logsearchorderby">
	      <option value="timestamp">Timestamp</option>
	      <option value="severity">Severity</option>
	      <option value="application">Application</option>
	      </select>
	      </td>
	      <td>
	      <select name="logsearchorder">
	      <option value="desc">Descending&nbsp;</option>
	      <option value="asc">Ascending</option>
	      </select>
	      </td>
	      <td>
	      For period from 
	      <input type="text" name="lsdatestart" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.lsdatestart,this)" />
	      to <input type="text" name="lsdateend" size="10" onclick="JACS.show(this,this)" /><img src="../javascript/scw.gif" onclick="JACS.show(document.reports.lsdateend,this)" />   
	      </td>
	      </tr>
	      <tr>
	      <td>&nbsp;</td>
	      <td colspan="3">
	      Containing the text  
	      <input type="text" name="logsearchtext" size="60" />   
	      </td>
	</tr>
	<tr>
	      <td>
	      <input type="radio" name="choice" value="loggrowth"/>
	      <span class="tip" onmouseover="Tip('A graph showing the growth of the logs over time.')"
	      onmouseout="UnTip()">Log Growth</span>
	      </td>
	</tr>
	<tr><td>&nbsp;</td></tr>
</table>
</div>
<input type="button" name="doreport" onClick="doReport()" value="Fetch Selected Report" />
</div>
</form>
</body>
</html>