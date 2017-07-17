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
	<title>App Depot LDAP Admin</title>
	<script type="text/javascript" src="../javascript/motionpack.js"></script>
    <script type="text/javascript">  
    var conn;
    var del;
    var add;
      
    function isEmpty(mytext) {
		var re = /^\s{1,}$/g; //match any white space including space, tab, form-feed, etc.
			if ((mytext.value.length==0) || (mytext.value=='') || ((mytext.value.search(re)) > -1)) {
			return true;
			}
			else {
			return false;
			}
		}
	
	function addSource()
		{
		if(!checkConn() || !checkForm()) return false;
	  	if(window.XMLHttpRequest) {
			add = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			add = new ActiveXObject("Microsoft.XMLHTTP");
		}
		document.getElementById('addloader').style.display = '';
		document.getElementById('adderror').style.display = 'none';
		
		var sourcename = document.newldap.sourcename.value;
		var host = document.newldap.host.value;
		var port = document.newldap.port.value;
		var user = document.newldap.searchuser.value;
		var password = document.newldap.password.value;
		var basedn = document.newldap.base_dn.value;
		var namef = document.newldap.ldap_name_field.value;
		var emailf = document.newldap.ldap_email_field.value;
		var displayf = document.newldap.ldap_fullname_field.value;
		
		var url = 'ldapupdate.php';
		var params = "do=add&sourcename="+sourcename+"&host="+host;
		params += "&port="+port+"&searchuser="+user+"&password="+password;
		params += "&base_dn="+basedn+"&ldap_name_field="+namef;
		params += "&ldap_email_field="+emailf+"&ldap_fullname_field="+displayf;
				
		// Send information to lookup script via post
		add.open("POST",url,true);
		
		// Set the headers
		add.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		add.setRequestHeader("Content-length", params.length);
		add.setRequestHeader("Connection", "close");
		
		add.onreadystatechange = callbackAdd;
		add.send(params);
		return false;
	  }
	  
	function callbackAdd()
	  {
	  	if(add.readyState == 4)
	  		{
	  			var response = add.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			if(result == 'error')
	  				{
	  					var error = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  					document.getElementById('addloader').style.display = 'none';
	  					document.getElementById('adderror').style.display = '';
	  					document.getElementById('adderror').className = 'error';
	  					document.getElementById('adderror').innerHTML = 'Error: '+error;
	  				}
	  			else
	  				{
	  					document.getElementById('addloader').style.display = 'none';
	  					document.getElementById('adderror').style.display = 'none';
	  					window.location = 'ldapadmin.php';
	  				}
	  		}
	  }
	
	function doDelete(ldapid)
	 {
	 	if(!confirm("You are about to delete this source.  Please ensure that there are no users with this source in App Depot.  All information, including application permissions, will also be removed.  This cannot be undone. \n\nContinue?"))
	 		return false;
	 	if(window.XMLHttpRequest) {
			del = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			del = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var url = 'ldapupdate.php';
		var params = "do=delete&ldapid="+ldapid;
		
		// Send information to lookup script via post
		del.open("POST",url,true);
		
		// Set the headers
		del.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		del.setRequestHeader("Content-length", params.length);
		del.setRequestHeader("Connection", "close");
		
		del.onreadystatechange = callbackDelete;
		del.send(params);
		return false;
	 }
		
	function callbackDelete()
	  {
	  	if(del.readyState == 4)
	  		{
	  			var response = del.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			if(result == 'error')
	  				{
	  					var error = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  					document.getElementById('generror').style.display = '';
	  					document.getElementById('generror').className = 'error';
	  					document.getElementById('generror').innerHTML = 'Error: '+error;
	  				}
	  			else
	  				{
	  					document.getElementById('generror').style.display = '';
	  					document.getElementById('generror').className = 'success';
	  					window.location = 'ldapadmin.php';
	  				}
	  		}
	  }	
	
	function connTest()
		{
			if(!checkConn()) return false;
			
			if(window.XMLHttpRequest) {
				conn = new XMLHttpRequest();
			} else if(window.ActiveXObject) {
				conn = new ActiveXObject("Microsoft.XMLHTTP");
			}
			document.getElementById('loader').style.display = '';
		    document.getElementById('error').style.display = 'none';
			var host = document.newldap.host.value;
			var port = document.newldap.port.value;
			var user = document.newldap.searchuser.value;
			var password = document.newldap.password.value;
			var url = 'checkldapsource.php';
			var params = "host="+host+"&port="+port+"&user="+user+"&password="+password;
			
			// Send information to lookup script via post
			conn.open("POST",url,true);
			
			// Set the headers
			conn.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			conn.setRequestHeader("Content-length", params.length);
			conn.setRequestHeader("Connection", "close");
			
			conn.onreadystatechange = callbackConnTest;
			conn.send(params);
			return false;	
		}
	
	function callbackConnTest()
	  {
	  	if(conn.readyState == 4)
	  		{
	  			var response = conn.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			document.getElementById('loader').style.display = 'none';
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			if(result == 'error')
	  				{
	  					var error = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  					document.getElementById('error').style.display = '';
	  					document.getElementById('error').className = 'error';
	  					document.getElementById('error').innerHTML = error;
	  				}
	  			else
	  				{
	  					document.getElementById('error').style.display = '';
	  					document.getElementById('error').className = 'success';
	  					document.getElementById('error').innerHTML = 'Test OK';
	  				}
	  		}
	  }
		
	function checkConn()
		{
			if(isEmpty(document.newldap.sourcename))
				{
					alert("You must specify a source name");
					return false;	
				}
			if(isEmpty(document.newldap.host))
				{
					alert("You must specify a host address");
					return false;	
				}
			if(isEmpty(document.newldap.port))
				{
					alert("You must specify a port");
					return false;	
				}
			if(isEmpty(document.newldap.searchuser))
				{
					alert("You must specify a search user");
					return false;	
				}
			if(isEmpty(document.newldap.password))
				{
					alert("You must specify a password");
					return false;	
				}
			return true;
		}
		
	function checkForm()
		{
			if(isEmpty(document.newldap.base_dn))
				{
					alert("You must specify a search dn");
					return false;	
				}
			if(isEmpty(document.newldap.ldap_name_field))
				{
					alert("You must specify a name field");
					return false;	
				}
			if(isEmpty(document.newldap.ldap_email_field))
				{
					alert("You must specify an email field");
					return false;	
				}
			if(isEmpty(document.newldap.ldap_fullname_field))
				{
					alert("You must specify a display name field");
					return false;	
				}
			return true;
		}	
    </script>
</head>
<body>
	<br />
	<div class="dropheader" id="ldapheader" onClick="toggleSlide('newldapslider')">Add New LDAP Source</div>
	<div class="dropslider" id="newldapslider" style="display:none;height:430px">
	<form name="newldap" action="ldapadmin.php" method="post">
	<table>
		<tr><td colspan="4"><h2>Connection Settings</h2></td></tr>
		<tr>
			<td>Source Name:</td>
			<td><input type="text" name="sourcename" size="30" /></td>
			<td rowspan="5" width="170px"><input type="button" value="Test Connection" onclick="connTest()" />
							<div id="loader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40px" /></div>
							<div id="error"></div>
			</td>
		</tr>
		<tr>
			<td>Host Address:</td>
			<td><input type="text" name="host" size="30" /></td>
		</tr>
		<tr>
			<td>Port: </td>
			<td><input type="text" name="port" size="5" /></td>
		</tr>
		<tr>
			<td>Connection Username:</td>
			<td colspan="2"><input type="text" name="searchuser" size="50" /></td>
		</tr>
		<tr>
			<td class="smalltext" colspan="3">Ex: <i>UID=Username,OU=Users,O=company</i> or <i>CN=Username,CN=Users,DC=mycompany,DC=com</i></td>
		</tr>
		<tr>
			<td>Connection Password:</td>
			<td><input type="password" name="password" size="30" /></td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan="4"><h2>Search Settings</h2></td></tr>
		<tr><td class="smalltext" colspan="4">These settings determine what attributes App Depot will look for when searching users in your LDAP system.  The listed values are defaults for OpenLDAP.</td?</tr>
		
		<tr>
			<td>Search DN:</td>
			<td colspan="3"><input type="text" name="base_dn" size="50" /></td>
		</tr>
		<tr>
			<td class="smalltext" colspan="3">Ex: <i>OU=Users,O=company,O=com</i> or <i>CN=Users,DC=mycompany,DC=com</i></td>
		</tr>
		<tr>
			<td>Name Field:</td>
			<td><input type="text" name="ldap_name_field" size="30" value="cn"/></td>
			<td rowspan="3"><div id="adderror" style="display:none"></div>
							<div id="addloader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40px" /></div>
			</td>
		</tr>
		<tr>
			<td>Display Name Field:</td>
			<td><input type="text" name="ldap_fullname_field" size="30" value="displayname"/></td>
		</tr>
		<tr>
			<td>Email Field:</td>
			<td><input type="text" name="ldap_email_field" size="30" value="mail"/></td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan="4"><center><input type="button" name="sub" value="Add LDAP Source" onclick="addSource()" /></center></td></tr>	
	</table>
	</form>
	</div>
	<br />
	<hr />
	<div id="generror"></div>
	<div>
	<?php
		echo '<div class="listheader">LDAP Source List</div>';
		echo '<table width="100%" class="listtable">';
		echo '<tr>';
		echo '<td class="listtableheader">Source Name</td><td class="listtableheader">Host</td>';
		echo '<td class="listtableheader">Port</td><td class="listtableheader">Status</td>';
		echo '<td class="listtableheader" width="60px">&nbsp;</td><td class="listtableheader" width="60px">&nbsp;</td>';
		$shaded = 1;
		$query = "SELECT * FROM ldapsources";
		$result = mysql_query($query) or dieLog("Could not get LDAP information from database because ".mysql_error());
		while($row = mysql_fetch_assoc($result))
			{
				if($shaded)
					{
						echo '<tr class="shaderow">';
						$shaded = 0;
					}
				else
					{
						echo '<tr>';
						$shaded = 1;	
					}
				echo '<td>'.$row['sourcename'].'</td>';
				echo '<td>'.$row['host'].'</td>';
				echo '<td>'.$row['port'].'</td>';
				if(ldapConnect($row['ldap_id']))
					echo '<td class="success">Connection OK</td>';
				else
					echo '<td class="error">Connection FAILED</td>';
				echo "<td><a href='ldapedit.php?ldapid=".$row['ldap_id']."'>Edit</a></td>";
				echo "<td><a href='#' onClick='doDelete(".$row['ldap_id'].")'>Delete</a></td>";
				echo '</tr>';	
			}
		echo '</table>';
	?>
	</div>
</body>
</html>