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
    
    // App Depot Installation File
    $settingsfile = '../includes/settings.php';
    $theme = 'Crimson';
    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="../themes/<?php echo $theme; ?>/style.css" />
	<link rel="stylesheet" type="text/css" href="../themes/<?php echo $theme; ?>/ddsmoothmenu.css" />
	<title>Application Depot Installation</title>
	<style type="text/css">
	ol{
	}
	ul{
	text-indent:20px;
	}
	</style>
	<script type="text/javascript">
	
	var sub;
	var subbed = false;
	
	function doVerify()
	  {
	  	if(!formCheck())
	  		return false;
	  	if(window.XMLHttpRequest) {
			sub = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			sub = new ActiveXObject("Microsoft.XMLHTTP");
		}
		document.getElementById('installloader').style.display = '';
		var username = document.install.username.value;
		var password = document.install.password.value;
		var rootdir = encodeURI(document.install.rootdir.value);
		var logfile = encodeURI(document.install.logfile.value);
		var database = document.install.database.value;
		var dbhost = encodeURI(document.install.dbhost.value);
		var ldaphost = encodeURI(document.install.ldaphost.value);
		var port = document.install.port.value;
		var searchuser = document.install.searchuser.value;
		var ldappassword = document.install.ldappassword.value;
		var basedn = document.install.basedn.value;
		var namefield = document.install.namefield.value;
		var adminuser = document.install.adminuser.value;
		
		
		var url = 'docheck.php';
		var params = "username="+username+"&password="+password;
		params += "&rootdir="+rootdir+"&logfile="+logfile;
		params += "&database="+database+"&dbhost="+dbhost;
		params += "&ldaphost="+ldaphost;
		params += "&port="+port+"&searchuser="+searchuser;
		params += "&ldappassword="+ldappassword+"&basedn="+basedn;
		params += "&namefield="+namefield+"&adminuser="+adminuser;
		
		// Send username and password to verification script via post
		sub.open("POST",url,true);
		
		// Set the headers
		sub.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		sub.setRequestHeader("Content-length", params.length);
		sub.setRequestHeader("Connection", "close");
		
		sub.onreadystatechange = callbackVerify;
		sub.send(params);
		return false;
	  }
	function callbackVerify()
	  {
	  	if(sub.readyState == 4)
	  		{
	  			var response = sub.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			var msg = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  			document.getElementById('installloader').style.display = 'none';
	  			if(result == 'error')
	  				{  					  					
	  					document.getElementById('checkresults').className = 'error';
	  				}
	  			else
	  				{
	  					document.getElementById('checkresults').className = 'success';
	  					document.getElementById('installbut').disabled = false;
	  				}
	  			document.getElementById('checkresults').innerHTML = msg;
	  			subbed = true;
	  		}
	  }
	
	function isEmpty(mytext) {
		var re = /^\s{1,}$/g; //match any white space including space, tab, form-feed, etc.
			if ((mytext.value.length==0) || (mytext.value=='') || ((mytext.value.search(re)) > -1)) {
			return true;
			}
			else {
			return false;
			}
		}
	
	function formCheck()
		{
			if(isEmpty(document.install.adminemail))
				{
					alert("Please enter an administrator email address");
					return false;	
				}	
			if(isEmpty(document.install.logfile))
				{
					alert("Please enter a valid log file location");
					return false;	
				}
			if(isEmpty(document.install.dbhost))
			    {
			      alert("You must specify a Database Host");
			      return false;
			    }
		    if(isEmpty(document.install.database))
			    {
			      alert("You must specify a Database Name");
			      return false;
			    }
			if(isEmpty(document.install.username))
			    {
			      alert("You must specify a Database Username");
			      return false;
			    }
			if(isEmpty(document.install.password))
			    {
			      alert("You must specify a Database Password");
			      return false;
			    }
			if(isEmpty(document.install.sourcename))
				{
					alert("You must specify a source name");
					return false;	
				}
			if(isEmpty(document.install.ldaphost))
				{
					alert("You must specify a host address");
					return false;	
				}
			if(isEmpty(document.install.port))
				{
					alert("You must specify a port");
					return false;	
				}
			if(isEmpty(document.install.searchuser))
				{
					alert("You must specify a search user");
					return false;	
				}
			if(isEmpty(document.install.ldappassword))
				{
					alert("You must specify a LDAP password");
					return false;	
				}
			if(isEmpty(document.install.basedn))
				{
					alert("You must specify a search dn");
					return false;	
				}
			if(isEmpty(document.install.namefield))
				{
					alert("You must specify a name field");
					return false;	
				}
			if(isEmpty(document.install.emailfield))
				{
					alert("You must specify an email field");
					return false;	
				}
			if(isEmpty(document.install.fnfield))
				{
					alert("You must specify a display name field");
					return false;	
				}
			if(isEmpty(document.install.adminuser))
				{
					alert("You must specify an admin username");
					return false;	
				}
			return true;
		}
	function setDisable()
		{
			document.install.installbut.disabled = true;
			if(subbed)
			{	
				document.getElementById('checkresults').className = '';
				document.getElementById('checkresults').innerHTML = 'You have made changes to your information, so you must re-verify before continuing';
			}	
		}
	function doInstall()
		{
			if(!formCheck())
	  		return false;
	  		document.install.submit();	
		}
	</script>
</head>
<body>
<form name="install" action="doinstall.php" method="post">
<br />
<center>
<div><img <img src="../themes/<?php echo $theme; ?>/images/logosmall.png" height="" />
<div class="listheader" style="width:950px">App Depot Installation</div>
<div class="dropslider" style="width:950px;text-align:left;">
<h2>Welcome to App Depot!</h2>
Thank you for choosing App Depot for your application security and user authentication.  We're sure that App Depot will make 
life for everyone at your organization, from users to programmers to managers, much simpler and more convenient.  We'd love to know what you think! 
Since App Depot is an open source project, we're always looking for good ideas, bug reports, and even programming help from the community.  
Visit the forums at <a href="http://appdepot.org">appdepot.org</a> and let us know what you think, give us your ideas, or just say hello.
<br /><br />Thanks again, and enjoy using App Depot!<br /><br /><br />
<h2>Installation Overview</h2>
Installing App Depot is a straightforward process, but there are a few requirements that must be met before we can continue.
<br />
<br />
<div class="smalltext" style="padding:0 0 0 25px;">
<ol>
	<li>The PHP user on your server must have read/write permissions to the following files:
	<ul>
		<li>The entire <i>install</i> directory and all files therein</li>
		<li>The <i>includes/settings.php</i> file</li>
		<li>Whatever location you specify as the log directory below</li>
	</ul></li>
	<li>You must create a MySQL database and a user for App Depot to use
	<ul>
		<li>Be sure to give the user permissions to the database you create</li>
		<li>App Depot will create all the tables and installation data for you</li>
	</ul></li>
	<li>You'll need at least one valid LDAP Authentication Source
	<ul>
		<li>This can be Active Directory, Open LDAP, or any other authentication source that uses the LDAP protocol</li>
		<li>All App Depot users MUST have an account in a valid LDAP source in order to log in</li>
		<li>You'll need to specify your first LDAP source below, and add a user from that source as an App Depot admin</li>
	</ul></li>
	<li>The PHP Directive 'register_globals' MUST be turned OFF.
		<ul>
		<?php 
			if(ini_get('register_globals'))	echo '<li class="error">It is currently ON.  Installation cannot continue.</li>';
			else echo '<li>It is currently set to OFF in your installation, no changes are required</li>';
		?>
		</ul>
	</li>
		
</ol>
</div>
<br />
If any of these conditions are not met, you will not be able to continue with the
installation. 
<br /><br /><br />
<h2>Required Information</h2>
	<div class="infobox" >
	<table>
	<tr><td class="listtableheader" colspan="2">General Settings</td></tr>
	<tr><td class="smalltext" colspan="2">The admin email is who your users can contact if they have issues.  The root directory is the 
	base directory for your App Depot installation.  For example, if your site is www.example.com and you have AppDepot installed at www.example.com/programs/AppDepot 
	then your root directory is /programs/AppDepot.  The logfile will be used to write logs in case of a database failure.</td>
	</tr>
	<tr>
		<td>Admin Email:</td>
		<td><input type="text" name="adminemail" size="40" value="" /></td>
	</tr>
	<tr>
		<td>Root Directory:</td>
		<td><input type="text" name="rootdir" size="30" value="/AppDepot" onChange="setDisable()"/></td>
	</tr>
	<tr>
		<td>LogFile:</td>
		<td><input type="text" name="logfile" size="40" value="errorlog.txt" onChange="setDisable()"/></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="listtableheader" colspan="2">Database Settings</td></tr>
	<tr><td class="smalltext" colspan="2">The database settings define how to connect to the database you have setup. If you're running AppDepot on the same
	machine as your MySQL installation, then host should be 'localhost'.  If not, then you'll need to specify the server address or IP.</td>
	</tr>
	<tr>
		<td>Database Host:</td>
		<td><input type="text" name="dbhost" size="40" value="localhost" onChange="setDisable()" /></td>
	</tr>
	<tr>
		<td NOWRAP>Database Name:</td>
		<td><input type="text" name="database" size="30" value="" onChange="setDisable()"/></td>
	</tr>
	<tr>
		<td>Database User:</td>
		<td><input type="text" name="username" size="30" value="" onChange="setDisable()" /></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" name="password" size="30" value="" onChange="setDisable()" /></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="listtableheader" colspan="2">LDAP Settings</td></tr>
	<tr><td class="smalltext" colspan="2">You'll need to specify a working LDAP source so App Depot can setup the initial admin user.  You'll
	be able to add additional LDAP sources once App Depot installation is complete.</td>
	</tr>
	<tr>
		<td>Source Name:</td>
		<td><input type="text" name="sourcename" size="30" /></td>
	</tr>
	<tr>
		<td>Host Address:</td>
		<td><input type="text" name="ldaphost" size="30" onChange="setDisable()" /></td>
	</tr>
	<tr>
		<td>Port: </td>
		<td><input type="text" name="port" size="5" onChange="setDisable()" /></td>
	</tr>
	<tr>
		<td>Connection Username:</td>
		<td colspan="2"><input type="text" name="searchuser" size="50" onChange="setDisable()" /></td>
	</tr>
	<tr>
		<td class="smalltext" colspan="3">Ex: <i>UID=Username,OU=Users,O=company</i> or <i>CN=Username,CN=Users,DC=mycompany,DC=com</i></td>
	</tr>
	<tr>
		<td>Connection Password:</td>
		<td><input type="password" name="ldappassword" size="30" onChange="setDisable()" /></td>
	</tr>
	<tr>
		<td>Search DN:</td>
		<td colspan="3"><input type="text" name="basedn" size="50" onChange="setDisable()" /></td>
	</tr>
	<tr>
		<td class="smalltext" colspan="3">Ex: <i>OU=Users,O=company,O=com</i> or <i>CN=Users,DC=mycompany,DC=com</i></td>
	</tr>
	<tr>
		<td>Username Field:</td>
		<td><input type="text" name="namefield" size="30" value="cn" onChange="setDisable()" /></td>
	</tr>
	<tr>
		<td>Display Name Field:</td>
		<td><input type="text" name="fnfield" size="30" value="displayname"/></td>
	</tr>
	<tr>
		<td>Email Field:</td>
		<td><input type="text" name="emailfield" size="30" value="mail"/></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="listtableheader" colspan="2">Admin User Settings</td></tr>
	<tr><td class="smalltext" colspan="2">These settings create the initial admin user for App Depot so you can log in and set up the rest of 
	your users and programs once the installation is complete.  Please enter a valid username from the LDAP source you provided above.</td>
	</tr>
	<tr>
		<td>Admin Username:</td>
		<td><input type="text" name="adminuser" size="30" value="" onChange="setDisable()" /></td>
	</tr>
	<tr><td rowspan="3" width="200px">
			<span id="installloader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40" /></span>
			</td><td rowspan="3">
			<span id="checkresults"></span>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td>
		<input type="button" name="verify" value="Verify Information" onClick="doVerify()" />
		</td>
		<td>
		<input type="button" name="install" value="Complete Installation" onClick="doInstall()" id="installbut" disabled />
		</td>
	</tr>
	</table>
	</div>
</div>
</center>
</form>
</body>
</html>