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
$levels = array(
	0 => 'Admin',
	//1 => '',
	//2 => '',
	//3 => '',
	//4 => '',
	5 => 'User');
$users = getUserList();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="../themes/<?php echo $theme; ?>/style.css" />
	<script type="text/javascript" src="../javascript/motionpack.js"></script>
	<title>App Depot User Admin</title>
	<script type="text/javascript">
	  var accountheight = 21;
	  var dropdownstartheight = 150;
	  
	  // Initialize RPC values
	  var sub;
	  var add;
      var act;
      var del;
      
      function handleErr(msg, url, line_no)
		{
		 document.getElementById('generror').style.display = '';
	  	 document.getElementById('generror').className = 'error';
	  	 document.getElementById('generror').innerHTML = 'Error: '+msg+ 'at Line '+line_no;
		 return true; // It will not show error in browser JavaScript console.
		}

	  onerror = handleErr; // Function handleErr();
      
	  function isEmpty(mytext) {
		var re = /^\s{1,}$/g; //match any white space including space, tab, form-feed, etc.
			if ((mytext.value.length==0) || (mytext.value=='') || ((mytext.value.search(re)) > -1)) {
			return true;
			}
			else {
			return false;
			}
		}
	function doSelect(u)
		{
			document.userlookup.appdepot_username.value = u;
			document.userlookup.ldap_username.value = u;	
		}
	
	function doDelete(userid)
	 {
	 	if(!confirm("You are about to delete this user.  All information, including application permissions, will also be removed.  This cannot be undone. \n\nContinue?"))
	 		return false;
	 	if(window.XMLHttpRequest) {
			del = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			del = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var url = 'userupdate.php';
		var params = "do=delete&userid="+userid;
		
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
	  					window.location = 'useradmin.php';
	  				}
	  		}
	  }	
		
	function doActive(userid)
	  {
	  	if(window.XMLHttpRequest) {
			act = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			act = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var active = document.getElementById(userid).checked;
		var url = 'userupdate.php';
		var params = "do=active&userid="+userid+"&active="+active;
		
		// Send information to lookup script via post
		act.open("POST",url,true);
		
		// Set the headers
		act.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		act.setRequestHeader("Content-length", params.length);
		act.setRequestHeader("Connection", "close");
		
		act.onreadystatechange = callbackActive;
		act.send(params);
		return false;
	  }
	  
	function callbackActive()
	  {
	  	if(act.readyState == 4)
	  		{
	  			var response = act.responseXML;
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
	  					document.getElementById('generror').innerHTML = 'Updated';
	  				}
	  		}
	  }
	
	function doAdd()
	  {
	  	if(!formAddCheck())
	  		return false;
	  	if(window.XMLHttpRequest) {
			add = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			add = new ActiveXObject("Microsoft.XMLHTTP");
		}
		document.getElementById('addloader').style.display = '';
		document.getElementById('adderror').style.display = 'none';
		var adusername = document.userlookup.appdepot_username.value;
		var ldapusername = document.userlookup.ldap_username.value;
		var level = document.userlookup.user_level.value;
		var source = document.userlookup.auth.value;
		var url = 'userupdate.php';
		var params = "do=add&adusername="+adusername+"&ldapusername="+ldapusername+"&auth="+source+"&level="+level;
		
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
	  					window.location = 'useradmin.php';
	  				}
	  		}
	  }
	  
	function doLookup()
	  {
	  	if(!formCheckCheck())
	  		return false;
	  	if(window.XMLHttpRequest) {
			sub = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			sub = new ActiveXObject("Microsoft.XMLHTTP");
		}
		document.getElementById('subloader').style.display = '';
		document.getElementById('lookuperror').style.display = 'none';
		var username = document.userlookup.lookup.value;
		var source = document.userlookup.auth.value;
		var url = 'userlookup.php';
		var params = "username="+username+"&auth="+source;
		
		// Send information to lookup script via post
		sub.open("POST",url,true);
		
		// Set the headers
		sub.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		sub.setRequestHeader("Content-length", params.length);
		sub.setRequestHeader("Connection", "close");
		
		sub.onreadystatechange = callbackLookup;
		sub.send(params);
		return false;
	  }
	function callbackLookup()
	  {
	  	if(sub.readyState == 4)
	  		{
	  			var response = sub.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			if(result == 'error')
	  				{
	  					var error = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  					document.getElementById('subloader').style.display = 'none';
	  					document.getElementById('lookuperror').style.display = '';
	  					document.getElementById('lookuperror').className = 'error';
	  					document.getElementById('lookuperror').innerHTML = 'Error: '+error;
	  				}
	  			else
	  				{
	  					document.getElementById('subloader').style.display = 'none';
	  					document.getElementById('lookuperror').style.display = 'none';
	  					var resdisplay = document.getElementById('lookupresults');
	  					var showtable = '';
	  					showtable = '<br /><table class="smalltable">';
	  					showtable += '<tr><td class="smalltableheader">Choose</td><td class="smalltableheader">Username</td><td class="smalltableheader">Full Name</td>';
	  					showtable += '<td class="smalltableheader">Email</td></tr>';
	  					var accounts = resp[0].getElementsByTagName("account");
	  					var shader = 1;
	  				    for(var i=0; i<accounts.length; i++)
	  				    	{
	  				    		var uname = accounts[i].getElementsByTagName("username")[0].childNodes[0].nodeValue;
	  				    		var fullname = accounts[i].getElementsByTagName("fullname")[0].childNodes[0].nodeValue;
	  				    		var email = accounts[i].getElementsByTagName("email")[0].childNodes[0].nodeValue;
	  				    		if(shader == 1)
	  				    			{
	  				    			showtable += '<tr class="shaderow">';
	  				    		    shader = 0;
	  				    			}
	  				    		else
	  				    			{
	  				    			showtable += '<tr>';
	  				    		    shader = 1;	
	  				    			}
	  				    		showtable += '<td><input type="radio" name="choice" value="'+uname+'" onClick="doSelect(\''+uname+'\')"/></td>';
	  				    		showtable += '<td>'+uname+'</td><td>'+fullname+'</td>';
	  				    		showtable += '<td><a href="mailto:'+email+'">'+email+'</a></td></tr>';
	  				    	}
	  				   showtable += '</table>';
	  				   resdisplay.innerHTML = showtable;
	  				   resdisplay.style.display = '';
	  				   document.getElementById('addform').style.display = '';
	  				   document.getElementById('newuserslider').style.height = (dropdownstartheight + (accountheight * accounts.length)) + 'px';
	  				}
	  		}
	  }
	function formCheckCheck() 
	  {
	    if(isEmpty(document.userlookup.lookup))
	      {
	      alert("You must specify a username to look up");
	      return false;
	      }
	    return true;
  	 }
  	 function formAddCheck() 
	  {
	    if(isEmpty(document.userlookup.appdepot_username))
	      {
	      alert("You must specify an App Depot Username");
	      return false;
	      }
	    if(isEmpty(document.userlookup.ldap_username))
	      {
	      alert("You must choose a user");
	      return false;
	      }
	    return true;
  	 }
	</script>
</head>
<body>
<br />
<div class="dropheader" id="userheader" onClick="toggleSlide('newuserslider')">Add New User</div>
<div class="dropslider" id="newuserslider" style="display:none;height:50px">
<form name="userlookup" method="post" action="self">
<table>
	<tr>
	<td>Choose the Authentication Source:</td>
	<td><select name="auth">
	<?php 
		$sources = getAuthSources();
		foreach($sources as $key => $ldap)
			{
				echo '<option value="'.$ldap['ldap_id'].'">'.$ldap['sourcename'].'</option>';
			}
	?>
	</select></td>
	<td>&nbsp;</td>
	<td rowspan="2"><span id="subloader" style="width:400px;display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40px" /></span>
  	<span id="lookuperror" style="width:350px;display:none;"></span></td>
	</tr>
	<tr>
	<td>Lookup Username:</td>
	<td><input type="text" name="lookup" size="40" /></td>
	<td><input type="button" name="dolookup" value="Lookup" onClick="doLookup()" /></td>
	</tr>
</table> 
<div id="lookupresults" style="display:none;" ></div>
<div id="addform" style="display:none;" >
	<table>
		<tr>
		<td>App Depot Username:</td>
		<td colspan="2"><input type="text" name="appdepot_username" size="40" />
		<input type="hidden" name="ldap_username" />
		</td>
	<td rowspan="2"><span id="addloader" style="width:400px;display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40px" /></span>
  	<span id="adderror" style="width:350px;display:none;"></span></td>
		</tr>
		<tr>
		<td>User Level:</td>
		<td><select name="user_level">
			<?php
				foreach($levels as $key => $level)
					{
						if($key === 5)
							echo '<option value="'.$key.'" selected>'.$level.' </option>';
						else
							echo '<option value="'.$key.'">'.$level.' </option>';
					}
		    ?>
		  </select>
		</td>
		<td><input type="button" name="useradd" value="Add This User" onClick="doAdd()" /></td>
		</tr>
		</table>
</div>	
</form>
</div>
<br />
<hr />
<div id="generror"></div>
<div>
<?php
	foreach($levels as $key => $level)
		{
			echo '<div class="listheader">'.$level.' List</div>';
			echo '<table width="100%" class="listtable">';
			echo '<tr>';
			echo '<td class="listtableheader" width="60px">Active</td>';
			echo '<td class="listtableheader">Full Name</td><td class="listtableheader">App Depot Username</td>';
			echo '<td class="listtableheader">Auth Source</td><td class="listtableheader">E-Mail</td>';
			echo '<td class="listtableheader" width="200px">Last Login</td>';
			echo '<td class="listtableheader" width="60px">&nbsp;</td><td class="listtableheader" width="60px">&nbsp;</td>';
			$shaded = 1;
			foreach($users as $ukey => $user)
				{
					$ulevel = $user['user_level'];
					if($key == $ulevel)
					{
						$fullname = $user['fullname'];
						$email = $user['email'];
						$auth = $user['sourcename'];
						$username = $user['appdepot_username'];
						$lastlogin = $user['lastlogin'];
						$enabled = $user['enabled'];
						$userid = $user['user_id'];
						$found = true;
						if($email == 'Not Found In Source' && $fullname == 'Not Found In Source')
							$found = false;
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
						if(!$ulevel)
							echo '<td><input type="checkbox" value="1" name="enabled" checked disabled /></td>';
						elseif(!$enabled)
							echo '<td><input type="checkbox" value="1" name="enabled" id="'.$user['user_id'].'" onClick="doActive('.$user['user_id'].')"></td>';	
						else
							echo '<td><input type="checkbox" value="1" name="enabled" id="'.$user['user_id'].'" checked onClick="doActive('.$user['user_id'].')"></td>';	
						if(!$found)
							echo "<td class='error'>";
						else
							echo '<td>';
						echo "$fullname</td>";
						echo "<td>$username</td><td>$auth</td>";
						if(!$found)
							echo "<td class='error'>$email</td>";
						else
							echo "<td><a href='mailto:$email'>$email</a></td>";
						if(userLoggedIn($username))
							echo '<td><span class="success">Logged In</span></td>';
						else
							echo "<td>$lastlogin</td>";				
						echo "<td><a href='useredit.php?userid=".$user['user_id']."'>Edit</a></td>";
						echo "<td><a href='#' onClick='doDelete(".$user['user_id'].")'>Delete</a></td>";
						echo '</tr>';
					}
				}
			echo '</table><br />';
		}
?>
</div>
</body>
</html>