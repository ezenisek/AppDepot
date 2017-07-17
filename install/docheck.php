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
		 die();
	}

//Check our incomming variables
$vars = array('dbhost','database','username','password','logfile','rootdir',
			  'ldaphost','port','searchuser','ldappassword','basedn','namefield','adminuser');
foreach($vars as $var)
	{
		if(!isset($_POST[$var]))
			{
				endProcess('error',"The request could not be completed, it is malformed. (Missing $var)");	
			}
	}
$dbhost = $_POST['dbhost'];
$database = $_POST['database'];
$username = $_POST['username'];
$password = $_POST['password'];
$logfile = urldecode($_POST['logfile']);
$rootdir = urldecode($_POST['rootdir']);
$host = urldecode($_POST['ldaphost']);
$port = $_POST['port'];
$searchuser = $_POST['searchuser'];
$ldappassword = $_POST['ldappassword'];
$basedn = $_POST['basedn'];
$namefield = $_POST['namefield'];
$adminuser = $_POST['adminuser'];


// First we verify that the installation folder and each file in it is writable
$dir = '../install';
if(!is_writable($dir))
	{
		endProcess('error','The installation folder is not writable.  Please verify permissions for the installation folder.');
	}
if($handle = @opendir($dir))
	{
	while($file = @readdir($handle))
		{
			if ($file != "." && $file != "..") 
			{
				if(!is_writable($dir.'/'.$file))
					endProcess('error',"The file $file in the installation directory is not writable.  Please verify permissions for this file.");
			}
		}
	}
else
	endProcess('error','Could not open the installation directory for reading.  Please verify permissions for the installation folder.');

// Do a quick check for register globals
if(ini_get('register_globals'))
	endProcess('error',"The register_globals directive in php.ini is currently set to ON.  It must be Off for App Depot to function properly.");

// Now check the settings file
if(!is_writable('../includes/settings.php'))
	endProcess('error',"The settings file in the includes directory is not writable.  Please verify permissions for this file.");


// Check each of the variables and make sure they're 'correct'
// Log File
if(!$file = @fopen($logfile,'w'))
	endProcess('error',"Could not open or create log file.  Please verify permissions for the file or folder specified."); 

// Root Dir
if(!strstr($_SERVER['PHP_SELF'],$rootdir))
	endProcess('error',"Your root directory does not appear to be correct.  Please verify it.  Note that it is case sensitive. (1)");
if(!$file = @fopen($_SERVER['DOCUMENT_ROOT'].$rootdir.'/install/test.txt','w'))
	endProcess('error',"Your root directory does not appear to be correct.  Please verify it.  Note that it is case sensitive. (2)");

// Database
if(!@mysql_connect($dbhost,$username,$password))
	endProcess('error',"Could not connect to your database server using the supplied information.  Please verify the information is correct.");
if(!$getdatabase = @mysql_select_db($database))
	endProcess('error',"Could not connect to $database.  Please verify the database name is correct and that $username has the correct permissions.");

$query = "CREATE TABLE IF NOT EXISTS test (testid int(10) unsigned NOT NULL auto_increment, value varchar (100) NOT NULL default '', PRIMARY KEY (testid)) ENGINE=InnoDB DEFAULT CHARSET=latin1";
mysql_query($query) or endProcess('error',"Connected to database but could not CREATE a test table.  Please verify the user permissions are correct.");
$query = "INSERT INTO test(value) VALUES('text')";
mysql_query($query) or endProcess('error',"Connected to database but could INSERT information into test table.  Please verify the user permissions are correct.");  	
$query = "DELETE FROM test WHERE value = 'text'";
mysql_query($query) or endProcess('error',"Connected to database but could not DELETE the test row.  Please verify the user permissions are correct.");	
$query = "DROP TABLE IF EXISTS test";
mysql_query($query) or endProcess('error',"Connected to database but could not DROP the test table.  Please verify the user permissions are correct.");

// Verify we aren't already installed
/*
$result = mysql_query("show tables like 'nvp'");
if(mysql_num_rows($result))
	endProcess('error',"It appears as if another installation of App Depot already exists in that database.  Please specify an empty database for this new installation.");
*/

// Check the LDAP Server Connection
if(!$ds=@ldap_connect($host,$port))
    endProcess('error',"Could not connect to the specified LDAP server.  Please verify your host and port is correct.");

ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);

// Check binding with the specified user                 
if(!$ldapBind = @ldap_bind($ds, $searchuser, $ldappassword))
     endProcess('error',"The LDAP server was found, but the binding has failed.  Please verify your connection username and password is correct.");    

// Check that the admin user actually exists in the specified source
$filter = '(&('.$namefield.'='.$adminuser.'))';
$returnAttribs = array("distinguishedname","$namefield");
$searchResults = ldap_search($ds,$basedn,$filter,$returnAttribs,0,0,10);
if(!ldap_count_entries($ds,$searchResults))
	endProcess('error',"The LDAP connection was successful, but the username $adminuser was not found.  Please verify the username you have specified is correct and that the Search DN and Username field is accurate for your LDAP source.");

// If we're this far, everything checks out and we're good to go.
endProcess('success','All the required information is verified.  You\'re ready to install!');
?>
