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
if(!isset($_GET['appid']))
	{
		dieLog("Edit app called with no app id");
	}
$appid = $_GET['appid'];

// Get app information
$query = "SELECT * FROM applications WHERE app_id = '$appid'";
$result = mysql_query($query) or dieLog("Could not get app information from database (useredit) because ".mysql_error());
if(!mysql_num_rows($result))
	{
		dieLog("Requested app ($appid) does not exist in the database.");
	}
$approw = mysql_fetch_assoc($result);
$cattree = createCatTree();

// Get user information for this application
$userlist = array();
$boxheight = 50;
if(!$approw['public'])
{
	$users = getUserList();
	foreach($users as $user)
		{
			$uid = $user['user_id'];
			$query = "SELECT user_id FROM permissions WHERE user_id ='$uid' AND app_id = '$appid'";
			$result = mysql_query($query) or dieLog("Could not get permissions information from the database ".mysql_error());
			if(mysql_num_rows($result))
				{
					$userlist[$uid] = $user;
				}
		}
	if(!count($users))
		{
			$userrow = false;
			$boxheight = 200;
		}
	else
		{
			$userrow = true;
			$boxheight = 50+(count($users)*20);
			if($boxheight < 200)
				$boxheight = 200;
		}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="../themes/<?php echo $theme; ?>/style.css" />
	<script type="text/javascript" src="../javascript/motionpack.js"></script>
	<script type="text/javascript" src="../javascript/jsTree.js"></script>
	<title>App Depot Application Admin</title>
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
	var edit;
	var act;
	var del;
	var add;
	var conn;

	function isEmpty(mytext) {
		var re = /^\s{1,}$/g; //match any white space including space, tab, form-feed, etc.
			if ((mytext.value.length==0) || (mytext.value=='') || ((mytext.value.search(re)) > -1)) {
			return true;
			}
			else {
			return false;
			}
		}

	function removeUser(id)
		{
		  	if(window.XMLHttpRequest) {
				del = new XMLHttpRequest();
			} else if(window.ActiveXObject) {
				del = new ActiveXObject("Microsoft.XMLHTTP");
			}
			document.getElementById('addloader').style.display = '';
			document.getElementById('adderror').style.display = 'none';

			var url = 'appuserupdate.php';
			var user = id
			var apps = <?php echo $appid; ?>;
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
	  					window.location = 'appedit.php?appid=<?php echo $appid; ?>&open=1';
	  				}
	  		}
	  }

	function addUser()
		{
		  	if(window.XMLHttpRequest) {
				add = new XMLHttpRequest();
			} else if(window.ActiveXObject) {
				add = new ActiveXObject("Microsoft.XMLHTTP");
			}
			document.getElementById('addloader').style.display = '';
			document.getElementById('adderror').style.display = 'none';

			var url = 'appuserupdate.php';
			var users = new Array();
			for(var i=0; i< document.editapp.userlist.length;i++)
				{
					if(document.editapp.userlist[i].checked)
					users[i] = document.editapp.userlist[i].value;
				}
			var app = <?php echo $appid; ?>;
			var params = "do=add&apps="+app+"&users="+users;

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
	  					window.location = 'appedit.php?appid=<?php echo $appid; ?>&open=1';
	  				}
	  		}
	  }

	function doEdit()
		{
		if(!checkConn() || !checkForm()) return false;
	  	if(window.XMLHttpRequest) {
			edit = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			edit = new ActiveXObject("Microsoft.XMLHTTP");
		}
		document.getElementById('edloader').style.display = '';
		document.getElementById('ederror').style.display = 'none';

	    var sendurl = document.editapp.url.value;
	    var appname = document.editapp.app_name.value;
	    var catid = document.editapp.category_id.value;
	    var des = document.editapp.description.value;
	    var radios = document.editapp.ispublic;
	    for (var i = 0, length = radios.length; i < length; i++) {
	        if (radios[i].checked) {
	            var ispublic = radios[i].value;
	            break;
	        }
	    }
	    var author = document.editapp.author.value;
	    var conname = document.editapp.contact_name.value;
	    var conemail = document.editapp.contact_email.value;
	    var idate = '<?php echo $approw['date_installed']; ?>';
		var url = 'appupdate.php';
		var params = "do=edit&app_id=<?php echo $appid; ?>&app_name="+appname+"&url="+sendurl;
		params += "&category_id="+catid+"&description="+des+"&public="+ispublic;
		params += "&author="+author+"&contact_name="+conname;
		params += "&contact_email="+conemail+"&date_installed="+idate;

		// Send information to lookup script via post
		edit.open("POST",url,true);

		// Set the headers
		edit.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		edit.setRequestHeader("Content-length", params.length);
		edit.setRequestHeader("Connection", "close");

		edit.onreadystatechange = callbackEdit;
		edit.send(params);
		return false;
	  }

	function callbackEdit()
	  {
	  	if(edit.readyState == 4)
	  		{
	  			var response = edit.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			if(result == 'error')
	  				{
	  					var error = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  					document.getElementById('edloader').style.display = 'none';
	  					document.getElementById('ederror').style.display = '';
	  					document.getElementById('ederror').className = 'error';
	  					document.getElementById('ederror').innerHTML = 'Error: '+error;
	  				}
	  			else
	  				{
	  					document.getElementById('edloader').style.display = 'none';
	  					document.getElementById('ederror').style.display = '';
	  					document.getElementById('ederror').className = 'success';
	  					document.getElementById('ederror').innerHTML = 'Information Updated';
	  				}
	  		}
	  }

	function doEnable(appid)
	  {
	  	if(window.XMLHttpRequest) {
			act = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			act = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var active = document.getElementById(appid).checked;
		var url = 'appupdate.php';
		var params = "do=active&appid="+appid+"&active="+active;

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
			var sendurl = document.editapp.url.value;
			var url = 'checkurl.php';
			var params = "url="+sendurl;

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
	  					document.getElementById('error').innerHTML = 'Valid URL';
	  				}
	  		}
	  }

	function checkConn()
    {
    	if(isEmpty(document.editapp.url))
    	{
    		alert("You must specify a url");
    		return false;
    	}
    	return true;
    }

    function checkForm()
    {
    	if(isEmpty(document.editapp.app_name))
    	{
    		alert("You must specify an application name");
    		return false;
    	}
    	return true;
    }

    function selectCat(id)
    {
    	document.editapp.category_id.value = id;
    }
	</script>
</head>
<body>
<script type="text/javascript" src="../javascript/wz_tooltip.js"></script>
<br />
<div class="listheader">Application Information</div>
<div class="dropslider">
<form name="editapp" action="editapp.php" method="post">
    	<div class="tree" style="float:left;">
    	Category Tree
    	<div id="treeContainer"></div>
    	</div>
    	<div>
    	<table>
    		<tr>
	    		<td>Application Name:</td>
	    		<td><input type="text" name="app_name" size="40" value="<?php echo $approw['app_name']; ?>" /></td>
    		</tr>
    		<tr>
	    		<td>Application URL:</td>
	    		<td><input type="url" name="url" size="50" value="<?php echo $approw['url']; ?>"/></td>
	    		<td rowspan="4" width="170px"><input type="button" value="Test URL" onclick="connTest()" />
							<div id="loader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40px" /></div>
							<div id="error"></div>
				</td>
    		</tr>
    		<tr>
    			<td>Category:</td>
    			<td><select name="category_id">
    			<?php
    				$query = "SELECT category_id, name FROM categories";
    				$result = mysql_query($query) or dieLog("Could not get category list from database because ".mysql_error());
    				while($row = mysql_fetch_row($result))
    					{
    						if($row[0] == $approw['category_id'])
    							echo '<option value="'.$row[0].'" selected>'.$row[1].'</option>';
    						else
    							echo '<option value="'.$row[0].'">'.$row[1].'</option>';
    					}
    			?>
    			</select></td>
    		</tr>
    		<tr>
    			<td>Description</td>
    			<td><textarea name="description" rows="3" cols="50"><?php echo $approw['description']; ?></textarea></td>
    		</tr>
    		<tr>
				<td>Public:</td>
				<td>
				<input type="radio" value="1" name="ispublic" <?php if($approw['public']) echo 'selected'; ?> />Yes
				&nbsp;&nbsp;&nbsp;
				<input type="radio" value="0" name="ispublic" <?php if(!$approw['public']) echo 'selected'; ?> />No
				</td>
			</tr>
    		<tr>
	    		<td>Author:</td>
	    		<td><input type="text" name="author" size="40" value="<?php echo $approw['author']; ?>" /></td>
	    		<td rowspan="3"><div id="ederror" style="display:none"></div>
							<div id="edloader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40px" /></div>
			</td>
    		</tr>
    		<tr>
	    		<td>Contact Name:</td>
	    		<td><input type="text" name="contact_name" size="40" value="<?php echo $approw['contact_name']; ?>" /></td>
    		</tr>
    		<tr>
	    		<td>Contact Email:</td>
	    		<td><input type="text" name="contact_email" size="40" value="<?php echo $approw['contact_email']; ?>" /></td>
    		</tr>
    		<tr>
    			<td>&nbsp;</td>
    			<td><input type="button" name="editapp" value="Update Application" onclick="doEdit()" /></td>
    		</tr>
    	</table>
    	</div>
</div>
<br />
<div class="dropheader" id="userheader" onClick="toggleSlide('userslider')">Users with access to <?php echo $approw['app_name']; ?></div>
<div class="dropslider" id="userslider" style="display:none;height:<?php echo $boxheight+30; ?>px">
<?php
	if($approw['public'])
		{
			echo "This is a public Application, all users have access to it by default.";
		}
	else
		{
			?>
			<table><tr>
			<td>
			<input type="button" value="Update User(s)" name="adduser" onClick="addUser()" />
			</td>
			<td height="40px">
			<span id="addloader" style="width:400px;display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40px" /></span>
			<span id="adderror" style="width:350px;display:none;"></span>
			</td>
			</tr></table>
			<?php
			echo '<div style="float:left;margin-right:15px;overflow-y:auto;overflow-x:hidden;height:'.$boxheight.'px;">';
			echo '<table>';
		    foreach($users as $user)
		    	{
		    		$hasapp = false;
		    		if(array_key_exists($user['user_id'],$userlist))
		    			$hasapp = true;
		    		echo '<tr>';
		    		echo '<td><input type="checkbox" name="userlist" value="'.$user['user_id'].'"';
		    		if($hasapp)
		    			echo 'checked ';
		    		echo '> </td><td NOWRAP>'.$user['appdepot_username'].'</td><td NOWRAP> ('.$user['fullname'].')&nbsp;&nbsp;&nbsp;</td>';
		    		echo '</tr>';
		    	}
		    echo '</table>';
			echo '</div>';
				if(!$userrow)
					{
						echo '<div> This application has no users.</div>';
					}
				else
					{
						echo '<div><table width="60%" class="listtable">';
						echo '<tr>';
						echo '<td class="listtableheader">Full Name</td><td class="listtableheader">App Depot Username</td>';
						echo '<td class="listtableheader">Auth Source</td><td class="listtableheader">E-Mail</td>';
						echo '<td class="listtableheader" width="60px">&nbsp;</td>';
						$shaded = 1;
						foreach($userlist as $user)
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
								$fullname = $user['fullname'];
								$email = $user['email'];
								$auth = $user['sourcename'];
								$username = $user['appdepot_username'];
								$userid = $user['user_id'];
								echo "<td>$fullname</td>";
								echo "<td>$username</td><td>$auth</td><td><a href='mailto:$email'>$email</a></td>";
								echo "<td><a href='#$userid' onClick=\"removeUser('$userid')\">Remove</a></td>";
								echo '</tr>';
							}
						echo '</table></div>';
					}
		}
?>
</div>
</form>
<script type="text/javascript">
renderTree();
<?php if(isset($_GET['open'])) echo 'toggleSlide(\'userslider\');'; ?>
</script>
</body>
</html>

