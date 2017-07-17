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
require_once('includes/startup.php');
$path = 'install';
$error = false;
// Check if we're coming from the install and the installation directory exists.  If so, delete it.
if(isset($_GET['i']))
{
	if(file_exists($path))
	if(!rmdirr($path))
		{
			writeLog("Could not clean and remove the installation directory");
			$error = "Could not clean and remove the installation directory for an unknown reason.  This is usually due to permissions issues." .
					" Please check the permissions on the installation directory in the App Depot root folder, or delete the installation directory " .
					"manually.";
		}
}

$theme = getNVP('theme');
$adminemail = getNVP('adminemail');
if(isset($_GET['message']))
	$logintext = urldecode($_GET['message']);
else
	$logintext = getNVP('logintext');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="themes/<?php echo $theme; ?>/style.css" />
	<title>Application Depot Login</title>
	<script type="text/javascript">
	function urlencode (str) {
	    str = (str + '').toString();
	    return encodeURIComponent(str);
	}
	function urldecode(str) {
		return decodeURIComponent((str + '').replace(/\+/g, '%20'));
	}

	 function breakout_of_frame()
  		{
    	if (top.location != location) {
     	 top.location.href = document.location.href ;
    	}
  		}

	  // Set up for our RPC call
	  var sub;

	  function isEmpty(mytext) {
		var re = /^\s{1,}$/g; //match any white space including space, tab, form-feed, etc.
			if ((mytext.value.length==0) || (mytext.value=='') || ((mytext.value.search(re)) > -1)) {
			return true;
			}
			else {
			return false;
			}
		}

	function doSubmit()
	  {
	  	if(!formCheck())
	  		return false;
	  	if(window.XMLHttpRequest) {
			sub = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			sub = new ActiveXObject("Microsoft.XMLHTTP");
		}
		document.getElementById('loginerror').style.display = 'none';
		document.getElementById('subloader').style.display = '';
		document.getElementById('placeholder').style.display = 'none';
		var username = document.login.username.value;
		var password = document.login.password.value;
        password = urlencode(password);
		var url = 'loginverify.php';
		var params = "username="+username+"&password="+password;

		// Send username and password to verification script via post
		sub.open("POST",url,true);

		// Set the headers
		sub.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		sub.setRequestHeader("Content-length", params.length);
		sub.setRequestHeader("Connection", "close");

		sub.onreadystatechange = callbackSubmit;
		sub.send(params);
		return false;
	  }
	function callbackSubmit()
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
	  					document.getElementById('loginerror').style.display = '';
	  					document.getElementById('loginerror').className = 'error';
	  					document.getElementById('loginerror').innerHTML = 'Error: '+error;
	  				}
	  			else
	  				{
	  				    window.location = 'mainwindow.php';
	  				}
	  		}
	  }
	function formCheck()
	  {
	    if(isEmpty(document.login.username))
	      {
	      alert("You must specify a username");
	      return false;
	      }
	    else if(isEmpty(document.login.password))
	      {
	      alert("You must specify a password");
	      return false;
	      }
	    else
	    return true;
  	 }

  	 function stopError() {
  	  alert('Error');
	  return true;
	}

window.onerror = stopError;

	</script>
</head>
<body onload="breakout_of_frame()" style="overflow:hidden;">
<?php if($error)
		echo '<div class="error">'.$error.'</div>';
?>
<div class="logincontainer">
    <center>
	<div class="loginlogo">
    	<img class="imglogo" src="themes/<?php echo $theme; ?>/images/logologin.png" />
	</div>
	<form name="login" method="post" action="login.php">
	<div class="loginform">
	<table>
		<tr>
		<td>Username:</td>
		<td><input type="text" name="username" style="width:150px;" /></td>
		</tr>
		<tr>
		<td>Password:</td>
		<td><input type="password" name="password" style="width:150px;" /></td>
		</tr>
	</table>
	</div>
	</center>
	<div style="padding:5px 0 5px;">
	<input type="submit" value="Log In" onClick="return doSubmit()"/>
	</div>
	</form>
	<center>
	<div class="logininfo">
	<div id="placeholder" style="font-size:.7em;color:#000;"><?php echo $logintext; ?></div>
	<div id="subloader" style="display:none;"><img src="themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="50" /></div>
  	<div id="loginerror" style="display:none;"></div>
	</div>
	</center>
</div>
</body>