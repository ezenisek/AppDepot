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
    
    /********************************************************************
     * Application Depot Functions File
     * This file contains all the primary functions used by AppDepot
     * *****************************************************************/
function dbConnect($dbhost,$database,$username,$password)
  {
      // This function connects to the database and returns the connection.
      // The variables here are loaded from includes/settings.php     
        $conn = mysql_connect($dbhost,$username,$password) or dieLog("1  Could not connect to $database because " . mysql_error());
        $getdatabase = mysql_select_db($database) or dieLog("2 Could not connect to $database because " . mysql_error());
        return $conn;
  }

function scrubInput($text)
  {
  	 // This function scrubs the input for insertion into the database
  	//$text = str_replace("<", "&lt;", $text);
	//$text = str_replace(">", "&gt;", $text);
	//$text = strip_tags($text);
	//$text = htmlspecialchars($text, ENT_NOQUOTES);
	$text = mysql_real_escape_string($text);
	
	return $text;
  }

function LDAPConnect($ldapid)
  {
      // This function creates our initial LDAP connection and returns the 
      // connection reference based on the id of the LDAP connection from the
      // database.
      $query = "SELECT host, port, searchuser, password, ldap_secure FROM " .
      		"ldapsources WHERE ldap_id = '$ldapid'";
      $result = mysql_query($query) or dieLog("Could not get LDAP sources because ".mysql_error());
      $row = mysql_fetch_row($result);
  
        $password = $row[3];
        $host = $row[0];
        $port = $row[1];
        $rdn = $row[2];
      	
        if(!$ds=@ldap_connect($host,$port))
        { 
           $error = "Could not connect to LDAP Server at $host.";
           writeLog($error);
           return false;
        }
        
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
                 
        if(!$ldapBind = @ldap_bind($ds, $rdn, $password))
         { 
           $error = "Could not bind to LDAP Server at $host with credentials $rdn.";
           writeLog($error);
           return false;
         }
        return $ds;
  }

function LDAPUserVerify($username,$conn,$ldapid)
  {	
  	// This function takes a username and verifies that it exists in LDAP.
  	// If so, it returns an array of results.  If not, it returns false;
  	$query = "SELECT base_dn,ldap_name_field,ldap_email_field,ldap_fullname_field FROM " .
      		"ldapsources WHERE ldap_id = '$ldapid'";
    $result = mysql_query($query) or dieLog("Could not get LDAP sources because ".mysql_error());
    $row = mysql_fetch_row($result);
    $sdn = $row[0];
    $namefield = $row[1];
    $emailfield = $row[2];
    $fullnamefield = $row[3];
  	$filter = '(&('.$namefield.'='.$username.'))';
	$returnAttribs = array("$emailfield","$fullnamefield","distinguishedname","$namefield");
    $searchResults = @ldap_search($conn,$sdn,$filter,$returnAttribs,0,0,10);

    if(!@ldap_count_entries($conn,$searchResults))
		return false;
	else
		return ldap_get_entries($conn,$searchResults);
  }

function getUserList()
  {
  	// This function checks the database for all users, and then gets their information
  	// from whatever ldap source they belong to.  It returns an array of values.
  	
  	// First find out how many ldap sources we have and get their information
  	// Put the info into a ldapsource array we can use throughout the function
  	$ldapsources = getAuthSources();
  	
  	// Now get all the users we have and correspond them to their ldap server
  	$query = "SELECT * FROM users order by user_level ASC, appdepot_username ASC";
  	$result = mysql_query($query) or dieLog("Could not get userlist from database because ".mysql_error());	
  	$userlist = array();
  	while($row = mysql_fetch_assoc($result))
  		{
  			foreach($row as $key => $value)
  				{
  					$userlist[$row['user_id']][$key] = $value;
  				}
  			$ldapsources[$row['ldap_server']]['username'][$row['user_id']] = $row['ldap_username'];
  			$userlist[$row['user_id']]['sourcename'] = $ldapsources[$row['ldap_server']]['sourcename'];
    	}
  	
  	// For every LDAP source we have, set up a connection and do the search.
  	foreach($ldapsources as $key => $ldapsource)
  		{
  			// Check to see if there are any users from this source
  			if(isset($ldapsource['username']))
  			{
	  			// Set up connection
	  			$conn = LDAPConnect($key) or dieLog("Could not connect to LDAP server $key");	
	  			
	  			// Do the search, and get the results
	  			$sdn = $ldapsource['base_dn'];
	  			$namefield = strtolower($ldapsource['ldap_name_field']);
	  			// Set up the filter
	  			$filter = '(|';
	  			foreach($ldapsource['username'] as $userkey => $username)
	  				{
	  					$filter .= "($namefield=$username)"; 	
	  				}
	  		    $filter .= ')';
	  		    $emailfield = strtolower($ldapsource['ldap_email_field']);
	  		    $fullnamefield = strtolower($ldapsource['ldap_fullname_field']);
	  		    $returnAttribs = array("$namefield","$emailfield","$fullnamefield","distinguishedname");
	  			$searchResults = ldap_search($conn,$sdn,$filter,$returnAttribs,0,0,10);
	  			$arrResults = ldap_get_entries($conn,$searchResults);
	  			if(!ldap_count_entries($conn,$searchResults))
					{
						foreach($ldapsource['username'] as $userkey => $thisuser)
							{
								$userlist[$userkey]['fullname'] = "User not found in LDAP";
								$userlist[$userkey]['email'] = '-';	
							}				
					}
				else
					{
						foreach($arrResults as $key => $thisresult)
							{
								if(is_numeric($key))  // Discard the Count
									{
										foreach($userlist as $key => $thisuser)
											{
												if($thisuser['ldap_username'] == $thisresult[$namefield][0] && $thisuser['ldap_server'] == $ldapsource['ldap_id'])  // Match the usernames
												{
													if(isset($thisresult[$fullnamefield][0]))
														$userlist[$key]['fullname'] = $thisresult[$fullnamefield][0];
													else
														$userlist[$key]['fullname'] = 'N/A';
													if(isset($thisresult[$emailfield][0]))
														$userlist[$key]['email'] = $thisresult[$emailfield][0];
													else
														$userlist[$key]['email'] = 'N/A';
												}
											}	
									}
							}
					}
  			}
  		}
  	
  	//Check the userlist and see if any users exist that DO NOT exist in their LDAP source
  	foreach($userlist as $key => $thisuser)
  		{
  			if(!isset($thisuser['fullname']))
  				{
  					$userlist[$key]['fullname'] = 'Not Found In Source'; 
  					$userlist[$key]['email'] = 'Not Found In Source';
  				}
  		}
	return $userlist;
  }

function getAuthSources()
  {
  	// Returns a list of authentication sources in an array
  	$query = "SELECT * FROM ldapsources";
  	$result = mysql_query($query) or dieLog("Could not get Authentication Sources from the database because ".mysql_error());
  	$ldapsources = array();
  	while($row = mysql_fetch_assoc($result))
  		{
  			foreach($row as $key => $value)
  				{
  					$ldapsources[$row['ldap_id']][$key] = $value;
  				}				
  		}
  	return $ldapsources;
  }

function unserialize_session_data( $serialized_string ) 
{
   // Takes session_data from the database and creates an array of values just like
   // the $_SESSION	array
   
   $variables = array(  );
   $a = preg_split( "/(\w+)\|/", $serialized_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
   for( $i = 0; $i < count( $a ); $i = $i+2 ) {
       $variables[$a[$i]] = unserialize( $a[$i+1] );
   }
   return( $variables );
}

function userLoggedIn($username)
  {
  	// Checkes to see if the specified user is logged in.
  	$query = "SELECT session_data FROM sessions WHERE expires > UNIX_TIMESTAMP()";
  	$result = mysql_query($query) or dieLog("Could not get logged in user information from database because ".mysql_error());
  	$loggedin = false;
  	while($row = mysql_fetch_row($result))
  		{
  			$data = unserialize_session_data($row[0]);
  			if(isset($data['username']) && $data['username'] == $username)
  				$loggedin = true;
  		}
  	return $loggedin;
  }

function sessionVerify()
  {
  	// This function verifes the user's session, updates the time, and 
  	// returns the access level.  If
  	// the session isn't set or invalid it returns false.
  	// Check if the session exists
  	$rootdir = getNVP('rootdir');
  	if(!isset($_SESSION['username']))
  		{
  			$message = "Your session is no longer valid.  Please log back in.";
			$location = "$rootdir/login.php?message=$message";
			echo $location;
			header("Location: $location");
  			return false;
  		}  	
  	// Check the ip
  	if($_SERVER['REMOTE_ADDR'] != $_SESSION['ip'])
  		{
  			writeLog("Unexpected IP in ".$_SERVER['PHP_SELF'].":".$_SERVER['REMOTE_ADDR']." expecting ".$_SESSION['ip']);
  			return false;
  		}
  	return $_SESSION['userlevel'];
  }

function createCatTree($showApps = false)
  {
  	/* This function builds the javascript output for the tree menu, and returns
      it as text.  The tree menu has some fairly specific rules it needs to follow
      in order to render correctly.  I'll try to explain it here.
      The whole tree is defined as an overall folder definition, and a folder 
      definition looks like so:
      [
        Node Definition: node1,
        Node Definition: node2
      ]
      
      Each node definition looks like this:
      [
        String: node name,
        [
          String: href,
          String: target,
          String: image,
          String: tooltip,
          Folder definition: node context menu,
          String: background image
        ],
        Folder definition: sub folder
      ]
  }
  */
  
  // Get our top level categories
  $query = "SELECT category_id FROM categories WHERE parent_id = 0 ORDER BY sortorder";
  $catresult = mysql_query($query) or dieLog("Could not get category list from the database because ".mysql_error());
  $theme = getNVP('theme');
  $icon = 'category';
  if(!mysql_num_rows($catresult))
  	{
  		$output = "[['No Categories have been defined', ['javascript:toggleSlide(\"newcatslider\")',,'$icon','Add a new Category by clicking Add New Category above.'],,]]";
  		return $output;
  	}
  $output = "[['App Depot Categories', ['javascript:foo()',,'$icon',''],[";
  while($catrow = mysql_fetch_row($catresult))
  	{
  		$output .= createCatBranch($catrow[0],$showApps).',';	
  	}
  $output = substr($output,0,-1); //Chop off last comma
  $output .= '],]]';
  return $output;
  }

function createCatBranch($id,$showApps)
  {
  	// This function recursively creates the code to show categories in the tree
  	// with or without associated applications.
  	$cat = 'category';
  	$app = 'application';
  	// Get category info
  	$query = "SELECT * FROM categories WHERE category_id = '$id'";
  	$result = mysql_query($query) or dieLog("Could not get single category information from database because ".mysql_error());
  	$row = mysql_fetch_assoc($result);
  	$name = $row['name'];
  	$tip = addslashes(addslashes($row['description']));
  	$output = "['$name',['javascript:selectCat($id)',,'$cat','$tip'],";
  		// Check if there are any 'children' categories of this category
  		$query = "SELECT category_id FROM categories WHERE parent_id = '$id'";
  		$result = mysql_query($query) or dieLog("Could not get sub category information from database because ".mysql_error());
  		$startbrak = false;
  		if(mysql_num_rows($result))
  			{
  				// There are, so we keep running deeper
  				$output .= '[';
  				$startbrak = true;
  				while($row = mysql_fetch_row($result))
  					{
  						$output .= createCatBranch($row[0],$showApps).',';	
  					}
  			}
  		if($showApps)
  			{
  				// Also list all the applications within this category
  				$query = "SELECT app_id, app_name, description FROM applications WHERE category_id = '$id'";
  				$result = mysql_query($query) or dieLog("Could not get cat app list from database because ".mysql_error());
  				if(mysql_num_rows($result))
  				{
  					if(!$startbrak)
  					{
  						$output .= '[';
	  					$startbrak = true;
  					}
	  				while($row = mysql_fetch_row($result))
	  					{
	  						$aname = $row[1];
	  						$aid = $row[0];
	  						$ades = addslashes(addslashes($row[2]));
	  						$output .= "['$aname',['javascript:selectApp($aid)',,'$app','$ades'],,],";	
	  					}
  				}
  			}
  		 if($startbrak)
  		 	$output = substr($output,0,-1).']'; //Chop off last comma
  		$output .= ',]';
  		return $output;
  }
  
function listSubs($catid)
  {
  	// Returns a list of all categories directly under the one given.
  	// Recursive
  	$sublist = array();
  	$query = "SELECT category_id FROM categories WHERE parent_id = '$catid'";
  	$result = mysql_query($query) or dieLog("Could not get sub cats from databse because ".mysql_error());
  	while($row = mysql_fetch_row($result))
  		{
  			$sublist[] = $row[0];
  			$sublist = array_merge($sublist,listSubs($row[0]));
  		}
  	return $sublist;
  }
  
function getMenu($username,$level = 5)
  {
    // Create the user application menu based on the username sent and the user
    // level
    $output = '<ul>';
    $output .= '<li><a href="#d" onclick="currentApp(\'d\',0)">Dashboard</a></li>';
    
    // First get a list of all applications this user has privelages for.  We'll
    // use this to add the links, but also to determine what categories need to 
    // be displayed.  We'll need the user id to get this done.
    $query = "SELECT user_id FROM users WHERE appdepot_username = '$username'";
    $result = mysql_query($query) or dieLog("Could not get user id from database because ".mysql_error());
    $row = mysql_fetch_row($result);
    $userid = $row[0];
    $applist = array();
    
    if($level == '0' && getNVP('adminallapps'))
   		{ 
    		$query = "SELECT app_id, app_name, url, category_id FROM applications";
   		}
   	else
   		{
    		$query = "SELECT app_id, app_name, url, category_id FROM applications " .
    				"WHERE (app_id IN (SELECT app_id FROM permissions WHERE user_id = '$userid') " .
    				"OR public = 1) ORDER BY app_name";
    	}
    // Get the apps from the query designated above
    $result = mysql_query($query) or dieLog("Could not get app list from database because ".mysql_error());
    while($row = mysql_fetch_row($result))
    	{
    		$applist[$row[0]]['app_id'] = $row[0];
    		$applist[$row[0]]['app_name'] = $row[1];
    		$applist[$row[0]]['url'] = $row[2];
    		$applist[$row[0]]['category_id'] = $row[3];	
    	}
    
    // Now that we have a list of applications, we can start working on the 
    // categories.  We call the createMenuBranch with a parent_id of 0 so that
    // we start on the top level
   
    // Let's start drilling
    $output .= createMenuBranch(0,$applist,$userid);
    	 

    if($level == '0')
    {
    	$output .= '<li><a href="#a" onclick="currentApp(\'a\',0)">Admin</a></li>';
    }
        
    $output .= '</ul>';
    return $output;
  }
  
function createMenuBranch($parentid,$applist,$userid)
  {
  	// Get all the categories that have a parent of $parentid, check to see
  	// if we should display it by looking through the applist, and display it
  	// if we need to.
  	$output = '';
  	// First things first, get all the categories with that parent id
  	$catlist = array();
  	$query = "SELECT category_id, name FROM categories WHERE parent_id = '$parentid' ORDER BY sortorder";
  	$result = mysql_query($query) or dieLog("Could not get cat list from database because ".mysql_error());
  	while($row = mysql_fetch_row($result))
  		{
  			//Check to see if we need to display this category.  We need to display it if
  			//any of our applications are in this category, or if any of our applications are
  			//in any category directly beneath this one.
			$subcats = listSubs($row[0]);
			$display = false;
			foreach($applist as $thisapp)
				{
					//If the current category has not already been shown we need to 
					//check if this application is in the current category or any of its sub categories
					if(!$display)
					if(($thisapp['category_id'] == $row[0]) || (in_array($thisapp['category_id'],$subcats)))
					{
						//Yes?  Then we display this category
						$output .= '<li><a href="#">'.$row[1].'</a><ul>';
						$display = true;
					}
				}
			
			// If we display this category, then we'll also need to drill down to subcategories
			// And display any applications this user has that belong in this category
			if($display)
			{
				$branch = createMenuBranch($row[0],$applist,$userid);
				if(!empty($branch))
					$output .= $branch;
					
				foreach($applist as $thisapp)
					{
						if($thisapp['category_id'] == $row[0])
							{
								$output .= '<li>';
								$output .= '<a href="#'.$thisapp['app_id'].'" onClick="currentApp('.
										   $thisapp['app_id'].','.$userid.')" >'.$thisapp['app_name'].'</a>';
								$output .= '</li>';	
							}
					}	
				$output .= '</ul></li>';
			}		   		
  		}
  	return $output;
  }  

function validUrl ($url)
{
		$url = @parse_url($url);

		if ( ! $url) {
			return false;
		}

		$url = array_map('trim', $url);
		$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
		$path = (isset($url['path'])) ? $url['path'] : '';

		if ($path == '')
		{
			$path = '/';
		}

		$path .= ( isset ( $url['query'] ) ) ? "?$url[query]" : '';

		if ( isset ( $url['host'] ) AND $url['host'] != gethostbyname ( $url['host'] ) )
		{
				$fp = @fsockopen($url['host'], $url['port'], $errno, $errstr, 3);

				if ( ! $fp )
				{
					return false;
				}
				fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
				$headers = fread ( $fp, 128 );
				fclose ( $fp );
		
			$headers = ( is_array ( $headers ) ) ? implode ( "\n", $headers ) : $headers;
			return ( bool ) preg_match ( '#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers );
		}
		return false;
}  
  
function dbConsistencyCheck()
{
	// Do a database consistency check.  This checks that all listed 
	// permissions belong to a real user and a real application.  
	$results = '';
	$query = "SELECT user_id, app_id FROM permissions";
	if(!$result = mysql_query($query)) return false;
	while($row = mysql_fetch_row($result))
		{
			$uid =$row[0];
			$aid = $row[1];
			$query = "SELECT user_id FROM users WHERE user_id = '$uid'";
			if(!mysql_num_rows($result))
				{
					$query = "DELETE FROM permissions WHERE user_id = '$uid'";
					if(!mysql_query($query)) return false;
					$results .= "User $uid not found in database.  Deleting permissions...\n ";
				}
			$query = "SELECT app_id FROM applications WHERE app_id = '$aid'";
			if(!mysql_num_rows($result))
				{
					$query = "DELETE FROM permissions WHERE app_id = '$aid'";
					if(!mysql_query($query)) return false;
					$results .= "Application $aid not found in database.  Deleting permissions...\n ";
				}
		}
	if($results == '')
		$results = "No inconsistencies found.";
	return $results;
}
  
function writeLog($entry, $severity=0, $app='AppDepot')
  {
   	// This function writes the supplied information to the log table in the
   	// database.  If the database cannot be accessed for whatever reason, it
   	// writes to a log file instead.
      $query = "INSERT INTO logs(timestamp,entry,application,severity) VALUES (NOW(),'$entry','$app','$severity')";
      if(!mysql_query($query))
        {
          global $logfile;
          if(!($fp = fopen($logfile,'a')))
            {
                return false;              
            }
          $output = date('Y-m-d H:i:s').' - '.$entry."\n";
          if(!fwrite($fp,$output))
          	return false;
        }
      return true;
  }

function dieLog($entry, $app = 'AppDepot')
  {
  	// This function writes the supplied entry to the log and kills the program
  	// with a severity of 3.
  	writeLog("\nDIED: ".$entry,3,$app);
  	die($entry);
  }

function getSettings($file)
  {
  	// Get all the settings from the settings file and put them in an array
  	// corresponding to their line numbers in the file
  	if(!$contents = file($file))
  		return false; 
  	$settings = array();
  	foreach($contents as $key => $line)
  		{
  			if(substr(trim($line),0,1) == '$') // is this is a variable?
  			{
  				$linebits = explode('=',$line);
  				$name = substr(trim($linebits[0]),1);
  				$value = str_replace (array ("'","\"",";"),"",trim($linebits[1]));
  				$settings[$name]['name'] = $name;
  				$settings[$name]['value'] = $value;	
  				$settings[$name]['line'] = $key;
  			}	
  		}
  	return $settings;
  }

function getUsername($userid)
  {
  	// Gets the username of the specified user id
  	$query = "SELECT appdepot_username FROM users WHERE user_id = '$userid'";
  	$result = mysql_query($query) or dieLog("Could not get username from database because ".mysql_error());
  	$row = mysql_fetch_row($result);
  	return $row[0];
  }

function getAppname($appid)
  {
  	// Gets the username of the specified user id
  	$query = "SELECT app_name FROM applications WHERE app_id = '$appid'";
  	$result = mysql_query($query) or dieLog("Could not get application name from database because ".mysql_error());
  	$row = mysql_fetch_row($result);
  	return $row[0];
  }

function getLastFailedLogin($userid)
 {
 	// Gets the last failed login for this user
 	$query = "SELECT MAX(timestamp) FROM logins where user_id = '$userid'";
 	$result = mysql_query($query) or dieLog("Could not get last failed login from database because ".mysql_error());
  	$row = mysql_fetch_row($result);
  	if(!mysql_num_rows($result))
  		return false;
  	return $row[0]; 	
 }
 
function addLogin($userid,$result)
 {
 	// Add a login attempt to the database	
 	$query = "INSERT INTO logins (timestamp,user_id,result) VALUES(NOW(),'$userid','$result')";
 	mysql_query($query) or dieLog("Could not insert login information into the database because ".mysql_error());
 }
 
function excessiveFailedLogins($userid)
 {
   // Checks to see if this user has had an excessive number of failed logins since the last successful attempt	
   $attempts = getNVP('failedloginattempts');
   $query = "SELECT login_id FROM logins WHERE user_id = '$userid' AND result = 0 AND timestamp >= (SELECT MAX(timestamp) FROM logins WHERE user_id = '$userid' AND result = 1)";
   $result = mysql_query($query) or dieLog("Could not check failed login information because ".mysql_error());
   if(mysql_num_rows($result) >= $attempts)
   	return true;
   else
    return false;	
 }
 
function rmdirr($dirname)
{
    // Sanity check
    if (!file_exists($dirname)) {
        return false;
    }

    // Simple delete for a file
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }

    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Recurse
        rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
    }

    // Clean up
    $dir->close();
    return @rmdir($dirname);
}


function getNVP($name)
  {
  	// This function returns the value of the supplied name from the NVP table in
  	// the database.  If the name doesn't exist in the database, it returns false.
    $query = "SELECT value FROM nvp WHERE name = '$name'";
    $result = mysql_query($query) or dieLog("Could not get nvp ($name) because ".mysql_error());
    if(mysql_num_rows($result))
    	{
    		$row = mysql_fetch_row($result);
    		$value = $row[0];
    		return trim($value);
    	}
    else
    	{
    		writeLog("Could not get NVP $name",2);	
    		return false;
    	}
  }
 
function setNVP($name, $value)
  {
  	// This function sets a NVP in the database.
  	$value = scrubInput($value);
  	$query = "DELETE FROM nvp WHERE name = '$name'";
  	$result = mysql_query($query) or dieLog("Could not delete nvp ($name) because ".mysql_error());
  	$query = "INSERT INTO nvp(name, value) VALUES('$name','$value')";
  	$result = mysql_query($query) or dieLog("Could not set nvp ($name,$value) because ".mysql_error());
  }

function createPDFReport($reportdata,$title = 'AppDepotReport',$fontsize=10,$orientation = 'P')
  {
      class PDF extends FPDF
      {
        //Page header
        function Header()
        {
            $rootdir = getNVP('rootdir');
            $theme = getNVP('theme');
            //Logo
            $this->Image($_SERVER['DOCUMENT_ROOT'].$rootdir.'/themes/'.$theme.'/images/logosmall.jpg',10,8,40);
            //Arial bold 15
            $this->SetFont('Arial','B',15);
            //Move to the right
            $this->Cell(50);
            //Title
            $title = $this->title;
            $width = (strlen($title)*2.8);
            $this->Cell($width,10,$title,'B',0,'C');
            //Line break
            $this->Ln(20);
        }
          
        //Page footer
        function Footer()
        {
            //Position at 1.5 cm from bottom
            $this->SetY(-15);
            //Arial italic 8
            $this->SetFont('Arial','I',8);
            //Page number
            $ftext = 'Report Generated by App Depot - www.appdepot.org          Page '.$this->PageNo().'/{nb}';
            $this->Cell(0,10,$ftext,0,0,'C');
        }
        
        function DoSection($section,$subnumber,$pagewidth,$defaultfontsize)
	  	{
	     // This is the section handler function for PDF Reports
	       // Set up fonts
	       $largefont = $defaultfontsize;
	       $smallfont = $defaultfontsize - 1;
	       
	       $maximumfieldsize = 80;
	            
	       // Indent for subsections
	       if($subnumber)
	       $this->Cell($subnumber*10);
	
	       // Get Max Length
	       $maxlength = array();
	       $maxlength[0] = '';
	       $fontsize = $smallfont - $subnumber;
	       $this->SetFont('Times','',$fontsize);
	       //print_r($section['data']);
	       if(isset($section['data']))
	       foreach($section['data'] as $linenum => $line)
	         {
	         	$i = 0;
	           foreach($line as $colnum => $col)
	             {
	               if(!is_array($col)) // If it is an array, it is a sub, and we ignore it.
	               {	             	
	               if(!isset($maxlength[$i]) || $maxlength[$i] <= $this->GetStringWidth($col))
	                 {
	                    if($this->GetStringWidth($col) > $maximumfieldsize)
	                      {
	                        // This field breaks the mold.  Trim it down so it doesn't overflow.
	                        $section['data'][$linenum][$colnum] = substr($col,0,$maximumfieldsize);
	                        $maxlength[$i] = 75;
	                      }
	                     else
	                       {
	                         $maxlength[$i] = $this->GetStringWidth($col);
	                       }
	                  }
	                $i++;
	                }
	              }
	         }
	         $i = 0;
	         $fontsize = $largefont - $subnumber;
	         $this->SetFont('Arial','',$fontsize);  
	         //print_r($maxlength);
	         
	         foreach($section['header'] as $key => $head)
	           {
	             if($maxlength[$i] < $this->GetStringWidth($head))
	                $maxlength[$i] = $this->GetStringWidth($head);
	             $i++;
	           }
	         $i = 0;
	                
	         // Max lengths are all figured out.  Now we need to distribute
	         // the columns evenly across the page based on that.
	         // To do so, we take the total page width, subtract the sum of 
	         // all the maxwidths, divide by the number of columns, and add the
	         // resulting number to each column as additional space.
	         $totalmax = array_sum($maxlength);
	         $pagewidth = $pagewidth - ($subnumber*20);
	         if($totalmax > $pagewidth)
	           {
	             echo "Error... report data too large";
	           }
	         $leftover = $pagewidth - $totalmax;
	         $addtoeach = $leftover / count($section['header']);
	         foreach($maxlength as $key => $value)
	           {
	             $maxlength[$key] = $value+$addtoeach;
	           }
	              
	         // Set font according to subsection depth
	         $fontsize = $largefont - $subnumber;
	         $this->SetFont('Arial','',$fontsize);  
	         foreach($section['header'] as $head)
	           {
	             $this->Cell($maxlength[$i],5,$head,'B',0);
	             $i++;
	           }              
	         $this->Ln(5);
	         // Indent for subsections
	         if($subnumber)
	         $this->Cell($subnumber*10);
	              
	         if(isset($section['data']))
	         foreach($section['data'] as $line)
	           {
	             $i = 0;
	             $fontsize = $smallfont - $subnumber;
	             $this->SetFont('Times','',$fontsize);
	             foreach($line as $col)
	               {
	                 // If it's an array, then it's a subsection
	                 if(!is_array($col))
	                   {
	                     $this->Cell($maxlength[$i],6,$col,0,0);    
	                     $i++;
	                   }
	               }
	             $this->Ln(4);
	             // Indent for subsections
	             if($subnumber)
	             $this->Cell($subnumber*10);
	             
	             // Check for subsection
	             if(isset($line['sub']))
	               {
	                 $this->Ln(2);
	                 foreach($line['sub'] as $sub)
	                 {
	                   $this->DoSection($sub,$subnumber+1,$pagewidth,$defaultfontsize);
	                 }
	               }
	           }
	            
	       $this->Ln(6);
	   }
	  }

      $pdf=new PDF($orientation);
      $pdf->AliasNbPages();
      $pdf->SetTitle($reportdata[0]);
      $pdf->AddPage();
      $pagewidth = $pdf->w - 20;
      foreach($reportdata as $key => $section)
        {
          if($key != 0)
            {
              $pdf->DoSection($section,0,$pagewidth,$fontsize);
            }
        }
          
      header('Content-type: application/pdf');
      header('Content-Disposition: attachment; filename="'.$title.'.pdf"');
    
      $pdf->Output($title.'.pdf','D');
  } 
  
?>
