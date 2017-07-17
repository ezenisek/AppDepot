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
require_once('../includes/fckeditor/fckeditor.php');
$userlevel = sessionVerify();
$theme = getNVP('theme');
if($userlevel)
	{
		dieLog("Unauthorized access attempt to Admin Page");
	}
$settings = getSettings('../includes/settings.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="../themes/<?php echo $theme; ?>/style.css" />
	<title>App Depot Settings Admin Page </title>
	<script type="text/javascript" src="../javascript/motionpack.js"></script>
	<script type="text/javascript">
	function isEmpty(mytext) {
		var re = /^\s{1,}$/g; //match any white space including space, tab, form-feed, etc.
			if ((mytext.value.length==0) || (mytext.value=='') || ((mytext.value.search(re)) > -1)) {
			return true;
			}
			else {
			return false;
			}
		}
	function isNumeric(strString)
   		{
   			var strValidChars = "0123456789.-";
   			var strChar;
   			var blnResult = true;
			if (strString.length == 0) return false;
			//  test strString consists of valid characters listed above
   			for (i = 0; i < strString.length && blnResult == true; i++)
      		{
      			strChar = strString.charAt(i);
      			if (strValidChars.indexOf(strChar) == -1)
         		{
         		blnResult = false;
         		}
      		}
   			return blnResult;
   		}	
	  
	  function doSubmit()
	  {
	  	if(isEmpty(document.settings.adminemail))
	      {
	      alert("You must specify an administrator email");
	      return false;
	      }
	    else if(isEmpty(document.settings.logfile))
	      {
	      alert("You must specify a logfile");
	      return false;
	      }
	      else if(isEmpty(document.settings.admin_showlog_history))
	      {
	      alert("You must specify Admin Log History");
	      return false;
	      }
	      else if(isEmpty(document.settings.sessiontimeout))
	      {
	      alert("You must specify Session Timeout");
	      return false;
	      }
	      else if(isEmpty(document.settings.sessionchecktime))
	      {
	      alert("You must specify the value for the Session Check Timer");
	      return false;
	      }
	      else if(isEmpty(document.settings.dbhost))
	      {
	      alert("You must specify a Database Host");
	      return false;
	      }
	      else if(isEmpty(document.settings.database))
	      {
	      alert("You must specify a Database Name");
	      return false;
	      }
	      else if(isEmpty(document.settings.username))
	      {
	      alert("You must specify a Database Username");
	      return false;
	      }
	      else if(isEmpty(document.settings.password))
	      {
	      alert("You must specify a Database Password");
	      return false;
	      }
	      else if(isEmpty(document.settings.rootdir))
	      {
	      alert("You must specify a Root Directory");
	      return false;
	      }
	    if(!isNumeric(document.settings.admin_showlog_history.value))
	    {
	    	alert("Admin Log History must be a numeric value");
	    	return false;
	    }
	    if(!isNumeric(document.settings.sessiontimeout.value))
	    {
	    	alert("Session Timeout must be a numeric value");
	    	return false;
	    }
		if(!isNumeric(document.settings.sessionchecktime.value))
	    {
	    	alert("Session Check Timer must be a numeric value");
	    	return false;
	    }

	    return true;
	  }
	  </script>
</head>
<body>
<form name="settings" action="updatesettings.php" method="post">
<br />
<div class="listheader">App Depot Settings</div>
<div class="dropslider">
	<h2>General Settings</h2>
	<div class="smalltext"></div>
	<div class="infobox" >
	<table>
	<tr>
		<td>Admin Email:</td>
		<td><input type="text" name="adminemail" size="40" value="<?php echo getNVP('adminemail'); ?>" /></td>
	</tr>
	<tr>
		<td>Theme:</td>
		<td><select name="theme">
		<?php
			// Get all the themes from the themes directory
			$path = '../themes/';
			foreach(scandir($path) as $file)
				{
					echo $file;
					if(substr($file,0,1)!="." && is_dir($path.$file))
						{
							if($file == getNVP('theme'))
								echo '<option value="'.$file.'" selected >'.$file.'</option>';
							else
								echo '<option value="'.$file.'" >'.$file.'</option>';
						}
				}
		?></select></td>
	</tr>	
	<tr>
		<td>Root Directory:</td>
		<td><input type="text" name="rootdir" size="30" value="<?php echo getNVP('rootdir'); ?>" /></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td>Login Message:</td>
		<td>
		  <?php
          $leditor = new FCKeditor('logintext');
          $leditor->BasePath = '../includes/fckeditor/';
          $leditor->ToolbarSet = 'Custom';
          $leditor->Value = getNVP('logintext');
          $leditor->Height = '200';
          $leditor->Width = '600';
          $leditor->Create();
          ?>
        </td>
		<!-- <td><textarea name="logintext" rows="3" cols="50"><?php echo getNVP('logintext'); ?></textarea></td> -->
	</tr>
	<tr>
		<td>Welome Text:</td>
		<td>
		  <?php
          $weditor = new FCKeditor('welcometext');
          $weditor->BasePath = '../includes/fckeditor/';
          $weditor->ToolbarSet = 'Custom';
          $weditor->Value = getNVP('welcometext');
          $weditor->Height = '200';
          $weditor->Width = '600';
          $weditor->Create();
          ?>
        </td>
		<!-- <td><textarea name="welcometext" rows="3" cols="50"><?php echo getNVP('welcometext'); ?></textarea></td> -->
	</tr>	
	<tr>
		<td>Admin Message:</td>
		<td>
		  <?php
          $aeditor = new FCKeditor('adminmessage');
          $aeditor->BasePath = '../includes/fckeditor/';
          $aeditor->ToolbarSet = 'Custom';
          $aeditor->Value = getNVP('adminmessage');
          $aeditor->Height = '200';
          $aeditor->Width = '600';
          $aeditor->Create();
          ?>
        </td>
		<!-- <td><textarea name="adminmessage" rows="3" cols="50"><?php echo getNVP('adminmessage'); ?></textarea></td> -->
	</tr>
	
	</table>
	</div>	
	<h2>Security Settings</h2>
	<div class="smalltext">
	</div>
	<div class="infobox">
	<table>
	<tr>
		<td>Give Admins All Apps:</td>
		<td>
		<input type="radio" value="1" name="adminallapps" <?php if(getNVP('adminallapps')) echo 'checked'; ?> />Yes 
		&nbsp;&nbsp;&nbsp;
		<input type="radio" value="0" name="adminallapps" <?php if(!getNVP('adminallapps')) echo 'checked'; ?> />No
		</td>
	</tr>
	<tr>
		<td>Admin Lockout Timer:</td>
		<td><input type="text" name="adminlockouttime" size="2" value="<?php echo getNVP('adminlockouttime'); ?>" /> Minutes</td>
	</tr>
	<tr>
		<td>Failed Logins Allowed:</td>
		<td><input type="text" name="failedloginattempts" size="2" value="<?php echo getNVP('failedloginattempts'); ?>" /></td>
	</tr>	
	</table>
	</div>
	<h2>Log Settings</h2>
	<div class="smalltext">The logfile is where AppDepot will record any errors that cannot be recorded in the Database logs.  By default, 
	high severity errors will be displayed to any admins by using a small overlay at the bottom of the screen.  Admin Log History is
	how many days back App Depot should check for these errors when an Admin has logged in.  Set to zero to disable high severity error display.  
	</div>
	<div class="infobox" >
	<table>
	<tr>
		<td>LogFile:</td>
		<td><input type="text" name="logfile" size="40" value="<?php echo $settings['logfile']['value']; ?>" /></td>
	</tr>
	<tr>
		<td>Admin Log History:</td>
		<td><input type="text" name="admin_showlog_history" size="2" value="<?php echo getNVP('admin_showlog_history'); ?>" /> Days</td>
	</tr>
	</table>
	</div>
	<h2>Session Settings</h2>
	<div class="smalltext">Session Timeout is how long a user can remain inactive before they are automatically logged out of App Depot.  The 
	session check timer is how often App Depot checks to verify the user's session is still valid.  Setting the check timer too low may result in
	poor performance.
	</div>
	<div class="infobox" >
	<table>
	<tr>
		<td>Session Timeout:</td>
		<td><input type="text" name="sessiontimeout" size="2" value="<?php echo getNVP('sessiontimeout'); ?>" /> Minutes</td>
	</tr>
	<tr>
		<td>Session Check Timer:</td>
		<td><input type="text" name="sessionchecktime" size="2" value="<?php echo getNVP('sessionchecktime'); ?>" /> Seconds</td>
	</tr>
	</table>
	</div>
	<h2>Database Settings</h2>
	<div class="smalltext">These are the settings App Depot uses to connect to your database.  Generally they will not be changed once set.</div>
	<div class="infobox" >
	<table>
	<tr>
		<td>Database Host:</td>
		<td><input type="text" name="dbhost" size="40" value="<?php echo $settings['dbhost']['value']; ?>" /></td>
	</tr>
	<tr>
		<td>Database Name:</td>
		<td><input type="text" name="database" size="30" value="<?php echo $settings['database']['value']; ?>" /></td>
	</tr>
	<tr>
		<td>Database User:</td>
		<td><input type="text" name="username" size="30" value="<?php echo $settings['username']['value']; ?>" /></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" name="password" size="30" value="<?php echo $settings['password']['value']; ?>" /></td>
	</tr>
	</table>
	</div>
</div>
<center><input type="submit" name="submit" value="Update Settings" onClick="return doSubmit()" /></center>
</body>
</html>