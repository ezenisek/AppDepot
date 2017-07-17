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
if(!isset($_GET['userid']))
	{
		dieLog("Edit user called with no user id");	
	}
$userid = $_GET['userid'];
$levels = array(
	0 => 'Admin',
	//1 => '',
	//2 => '',
	//3 => '',
	//4 => '',
	5 => 'User');

// Get user information
$query = "SELECT * FROM users WHERE user_id = '$userid'";
$result = mysql_query($query) or dieLog("Could not get user information from database (useredit) because ".mysql_error());
if(!mysql_num_rows($result))
	{
		dieLog("Requested user ($userid) does not exist in the database.");  
	}
$userrow = mysql_fetch_assoc($result);

// Get Application Information for this user
$query = "SELECT * FROM applications WHERE app_id in (SELECT app_id FROM permissions WHERE user_id = '$userid')";
$appresult = mysql_query($query) or dieLog("Could not get user information from database (useredit) because ".mysql_error());
if(!mysql_num_rows($appresult))
	{
		$approw = false;
	}
else
	{
		$approw = true;
	}

// Get LDAP Source Information
$ldapsources = getAuthSources();
$sourcename = $ldapsources[$userrow['ldap_server']]['sourcename'];
$fullnamefield = $ldapsources[$userrow['ldap_server']]['ldap_fullname_field'];
$emailfield = $ldapsources[$userrow['ldap_server']]['ldap_email_field'];

// Get LDAP info for this user
$conn = LDAPConnect($userrow['ldap_server']);
$ldapinfo = LDAPUserVerify($userrow['ldap_username'],$conn,$userrow['ldap_server']);
if(isset($ldapinfo[0][$fullnamefield]))
	$userrow['fullname'] = $ldapinfo[0][$fullnamefield][0];
else
	$userrow['fullname'] = 'N/A';
if(isset($ldapinfo[0][$emailfield]))
	$userrow['email'] = $ldapinfo[0][$emailfield][0];
else
	$userrow['email'] = 'N/A';

$cattree = createCatTree(true);
$boxheight = 50+(10*count($approw));
if($boxheight < 200)
	$boxheight = 200;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="../themes/<?php echo $theme; ?>/style.css" />
	<script type="text/javascript" src="../javascript/motionpack.js"></script>
	<script type="text/javascript" src="../javascript/jsTree.js"></script> 
	<title>App Depot User Edit Admin Page</title>
	<script type="text/javascript">
	 // Tree Stuff
	newNodeCount = 0;
	en_nodeContextMenu = [];
	jst_context_menu = en_nodeContextMenu;
	function _foo(){}
	jst_container = "document.getElementById('treeContainer')";
    jst_image_folder = "../themes/<?php echo $theme; ?>/tree_images";
    jst_image_folder_user = "../themes/<?php echo $theme; ?>/tree_images/";
	arrNodes = <?php echo $cattree; ?>;
	
	// Other Stuff
	var sub;
	var act;
	var add;
	var del;
	var selected;
	
	
	function removeApp(id)
		{
		  	if(window.XMLHttpRequest) {
				del = new XMLHttpRequest();
			} else if(window.ActiveXObject) {
				del = new ActiveXObject("Microsoft.XMLHTTP");
			}
			document.getElementById('addloader').style.display = '';
			document.getElementById('adderror').style.display = 'none';
		    
			var url = 'appuserupdate.php';
			var apps = id
			var user = <?php echo $userid; ?>;
			var params = "do=delete&apps="+apps+"&users="+user;
					
			// Send information to lookup script via post
			del.open("POST",url,true);
			
			// Set the headers
			del.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			del.setRequestHeader("Content-length", params.length);
			del.setRequestHeader("Connection", "close");
			
			del.onreadystatechange = callbackDel;
			del.send(params);
			return false;
	  }
	  
	function callbackDel()
	  {
	  	if(del.readyState == 4)
	  		{
	  			var response = del.responseXML;
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
	  					window.location = 'useredit.php?userid=<?php echo $userid; ?>&open=1';
	  				}
	  		}
	  }
	
	function addApp()
		{
		  	if(window.XMLHttpRequest) {
				add = new XMLHttpRequest();
			} else if(window.ActiveXObject) {
				add = new ActiveXObject("Microsoft.XMLHTTP");
			}
			document.getElementById('addloader').style.display = '';
			document.getElementById('adderror').style.display = 'none';
		    
			var url = 'appuserupdate.php';
			var apps = new Array();
			for(var i=0; i< document.useredit.applist.length;i++)
				{
					if(document.useredit.applist[i].checked)
					apps[i] = document.useredit.applist[i].value;
				}
			var user = <?php echo $userid; ?>;
			var params = "do=add&apps="+apps+"&users="+user;
					
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
	  					window.location = 'useredit.php?userid=<?php echo $userid; ?>&open=1';
	  				}
	  		}
	  }
	
	function doActive()
	  {
	  	if(window.XMLHttpRequest) {
			act = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			act = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var active = document.useredit.enabled.checked;
		var url = 'userupdate.php';
		var params = "do=active&userid=<?php echo $userrow['user_id']; ?>&active="+active;
		
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
	  					document.getElementById('error').style.display = '';
	  					document.getElementById('error').className = 'error';
	  					document.getElementById('error').innerHTML = 'Error: '+error;
	  				}
	  			else
	  				{
	  					document.getElementById('error').style.display = '';
	  					document.getElementById('error').className = 'success';
	  					document.getElementById('error').innerHTML = 'Enabled Status Updated';
	  				}
	  		}
	  }
	  
	function doUpdate()
	  {
	  	if(!formCheck())
	  		return false;
	  	if(window.XMLHttpRequest) {
			sub = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			sub = new ActiveXObject("Microsoft.XMLHTTP");
		}
		document.getElementById('error').style.display = 'none';
		document.getElementById('subloader').style.display = '';
		var adusername = document.useredit.appdepot_username.value;
		var ldapusername = document.useredit.ldap_username.value;
		var auth = document.useredit.auth.value;
		var level = document.useredit.user_level.value;
		var url = 'userupdate.php';
		var params = "do=edit&userid=<?php echo $userrow['user_id']; ?>&adusername="+adusername+"&ldapusername="+ldapusername+"&auth="+auth+"&level="+level;
		
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
	  					document.getElementById('error').style.display = '';
	  					document.getElementById('error').className = 'error';
	  					document.getElementById('error').innerHTML = 'Error: '+error;
	  				}
	  			else
	  				{
	  				    document.getElementById('error').style.display = '';
	  				    document.getElementById('subloader').style.display = 'none';
	  					document.getElementById('error').className = 'success';
	  					document.getElementById('error').innerHTML = 'Information Updated';
	  				}
	  		}
	  }
	function formCheck() 
	  {
	    if(isEmpty(document.useredit.appdepot_username))
	      {
	      alert("You must specify a username");
	      return false;
	      }
	    else if(isEmpty(document.useredit.ldap_username))
	      {
	      alert("You must specify a password");
	      return false;
	      }
	    else
	    return true;
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
	function selectApp(id)
    {
    	selected = id;	
    	document.getElementById(id).checked = true;
    }
	</script>
	</head>
<body>
<script type="text/javascript" src="../javascript/wz_tooltip.js"></script>
<form name="useredit" action="useredit.php" method="post">
<br />
<div class="listheader">User Information</div>
<div class="dropslider">
	<table>
	<tr>
		<td>App Depot Username:</td>
		<td><input type="text" size="30" name="appdepot_username" value="<?php echo $userrow['appdepot_username']; ?>" /></td>
		<td>Account Enabled: <input type="checkbox" value="1" name="enabled" 
			<?php if($userrow['enabled']) echo 'checked '; if($userrow['user_level']) echo 'onClick="doActive()" '; else echo 'disabled '; ?> /></td>
	</tr>
	<tr>
		<td>Authentication Source:</td>
		<td><select name="auth">
		<?php
			foreach($ldapsources as $key => $source)
				{
					if($source['ldap_id'] == $userrow['ldap_server'])
						echo '<option value="'.$source['ldap_id'].'" selected>'.$source['sourcename'].' </option>';
					else
						echo '<option value="'.$source['ldap_id'].'">'.$source['sourcename'].' </option>';		
				}
		?>
		</select>
		<?php
			if(empty($userrow['fullname']))
				{
					echo '<span class="error"> Could not get additional information from '.$ldapsources[$userrow['ldap_server']]['sourcename'].' LDAP</span>';
					$userrow['fullname'] = "Unknown";
				}
		?>
		</td>
	</tr>
	<tr>
		<td>LDAP Username:</td>
		<td><input type="text" size="30" name="ldap_username" value="<?php echo $userrow['ldap_username']; ?>" /></td>
	</tr>
	<tr>
		<td>Full Name:</td>
		<td><?php echo $userrow['fullname']; ?></td>
		<td>Email:</td>
		<td><a href="mailto:<?php echo $userrow['email']; ?>"><?php echo $userrow['email']; ?></a></td>
	<tr>
		<td>User Level: </td>
		<td><select name="user_level">
			<?php
				foreach($levels as $key => $level)
					{
						if($key == $userrow['user_level'])
							echo '<option value="'.$key.'" selected>'.$level.' </option>';
						else
							echo '<option value="'.$key.'">'.$level.' </option>';
					}
		    ?>
		  </select>
		</td>
	</tr>
	<tr>
		<td><input type="button" name="update" value="Update" onClick="doUpdate()" /></td>
		<td><a href="useradmin.php">Go Back</a></td>
		<td colspan="2" rowspan="2"><span id="error"></span>
		<span id="subloader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40px" /></span>
		</td>
	</tr>
	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	</table> 
</div>
<br />
<div class="dropheader" id="appheader" onClick="toggleSlide('appslider')"><?php echo $userrow['fullname']; ?>'s Applications</div>
<div class="dropslider" id="appslider" style="display:none;height:<?php echo $boxheight+50; ?>px">
<div class="tree" style="float:left;margin-right:15px;height:<?php echo $boxheight+30; ?>px;">
<table><tr>
<td>
<input type="button" value="Update Application(s)" name="addapp" onClick="addApp(selected)" />
</td>
<td height="40px">
<span id="addloader" style="width:400px;display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40px" /></span>
<span id="adderror" style="width:350px;display:none;"></span>
</td>
</tr></table>
<table>
	<tr>
	<td>
	<div id="treeContainer"></div>
	</div>
	</td>
	<td NOWRAP>
	<?php
		$query = "SELECT app_id, app_name FROM applications WHERE public = 0 ORDER BY app_name ASC";
		$result = mysql_query($query) or dieLog("Could not get application list from database because ".mysql_error());
		while($row = mysql_fetch_row($result))
			{		
				$query = "SELECT user_id FROM permissions WHERE app_id = '".$row[0]."' AND user_id = '$userid'";
				$chkresult = mysql_query($query) or dieLog("Could not get permissions from the database because ".mysql_error());
				if(mysql_num_rows($chkresult))		
					echo '<input type="checkbox" name="applist" id="'.$row[0].'" value="'.$row[0].'" checked> '.$row[1].'<br />';	
				else
					echo '<input type="checkbox" name="applist" id="'.$row[0].'" value="'.$row[0].'"> '.$row[1].'<br />';
			}
	?>
	</td>
	</tr>
</table>
</div>
	<div>
	<?php
		if(!$approw)
			{
				echo " This user has no application permissions.";	
			}
		else
			{
				echo '<table class="listtable" width="50%">';
				echo '<tr>';
				echo '<td class="listtableheader">App Name</td><td class="listtableheader">Category</td>';
				echo '<td class="listtableheader">Status</td>';
				echo '<td class="listtableheader" width="60px">&nbsp;</td>';
				echo '</tr>';
				$shader = 0;
				while($approw = mysql_fetch_assoc($appresult))
					{
						if($shader)
							{
								echo '<tr class="shaderow">';
								$shader = 0;	
							}
						else
							{
								echo '<tr>';
								$shader = 1;
							}
						$query = "SELECT name FROM categories WHERE category_id = '".$approw['category_id']."'";
						$result = mysql_query($query) or dieLog("Could not get category name from database because ".mysql_error());
						$row = mysql_fetch_row($result);
						$catname = $row[0];
						echo '<td NOWRAP>'.$approw['app_name'].'</td>';
                        echo '<td NOWRAP>'.$catname.'</td>';
                        if(validUrl($approw['url']))
							echo '<td><span class="success">Link Good</span></td>';
						else
							echo '<td><span class="error">Link Appears Invalid</span></td>';
						echo '<td><a href="#" onClick="removeApp('.$approw['app_id'].')">Remove</a></td>';
						echo '</tr>';					
					}
				echo '</table>';
			}
	?>
	</div>
</div>
</form>
<script type="text/javascript">
renderTree();
<?php if(isset($_GET['open'])) echo 'toggleSlide(\'appslider\');'; ?>
</script>
</body>
</html>
