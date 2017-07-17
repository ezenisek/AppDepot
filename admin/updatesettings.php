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
if($userlevel)
	{
		dieLog("Unauthorized access attempt to Admin Page");
	}
function endProcess($result,$content)
	{
		 if($result == 'error')
		 {
		 	$severity = 1;
		 	writeLog($content,$severity);
		 	header('Location:../includes/error.php?message='.urlencode($content));
		 }
		 else
		 {
		 	$severity = 0;
		 	writeLog($content,$severity);
		 	header('Location:../includes/success.php?message='.urlencode($content));
		 }
         die();
	}

// Check for all our variables
$dbvars = array(
	'adminemail' => '',
	'admin_showlog_history' => '',
	'sessiontimeout' => '',
	'adminmessage' => '',
	'logintext' => '',
	'welcometext' => '',
	'adminallapps' => '',
	'rootdir' => '',
	'theme' => '',
	'adminlockouttime' => '',
    'failedloginattempts' => '',
	'sessionchecktime' => '');  // Variables that go into the NVP table
$filevars = array(
	'dbhost' => '',
	'database' => '',
	'username' => '',
	'password' => '',
	'logfile' => '');  // Variables that go into the settings.php file.
$vars = array_merge($dbvars,$filevars);
foreach($vars as $key => $var)
	{
		if(!isset($_POST[$key]))
			endProcess('error',"The request is malformed.  $key is not defined.");
		if(array_key_exists($key,$dbvars))
			{
				$dbvars[$key] = scrubInput($_POST[$key]);	
			}
		if(array_key_exists($key,$filevars))
			{
				$filevars[$key] = $_POST[$key];	
			}
	}


// Update the database
foreach($dbvars as $key => $var)
	{
		$query = "UPDATE nvp SET value = '$var' WHERE name = '$key'";
		mysql_query($query) or dieLog("Could not update NVP $key because ".mysql_error()); 		
	}

// Update the settings file

		// Get the contents
		$file = '../includes/settings.php';
		$contents = file($file) or dieLog("Could not get settings file contents");
  		
  		// Replace what needs to be replaced
  		foreach($contents as $key => $line)
  		{
  			if(substr(trim($line),0,1) == '$') // is this is a variable?
  			{
  				$linebits = explode('=',$line);
  				$name = substr(trim($linebits[0]),1);
  				$contents[$key] = '$'.$name.' = \''.$filevars[$name].'\';';			
  			}	
  		}
  		
  		//Re-Write the file
  		$writeme = '';
  		foreach($contents as $line)
    	{
      		$writeme .= trim($line)."\n";
    	}
     
    	$fh = fopen($file,'w+') or endProcess('error','Cannot open the configuration file for writing');
        if(!fwrite($fh,trim($writeme)))
          {
           endProcess('error','Could not write to'.$file);
          }
	

endProcess('success',"App Depot settings have been successfully updated.  You may need to log out and back in for changes to be applied to your session.");

?>
