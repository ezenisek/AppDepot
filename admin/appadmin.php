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
$cattree = createCatTree();
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
    var conn;
    var add;
    var act;
    var del;


     function isEmpty(mytext) {
		var re = /^\s{1,}$/g; //match any white space including space, tab, form-feed, etc.
			if ((mytext.value.length==0) || (mytext.value=='') || ((mytext.value.search(re)) > -1)) {
			return true;
			}
			else {
			return false;
			}
		}

    function doDelete(appid)
	 {
	 	if(!confirm("You are about to delete this application.  All information, including user permissions, will also be removed.  This cannot be undone. \n\nContinue?"))
	 		return false;
	 	if(window.XMLHttpRequest) {
			del = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			del = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var url = 'appupdate.php';
		var params = "do=delete&appid="+appid;

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
	  					window.location = 'appadmin.php';
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


    function doAdd()
		{
		if(!checkConn() || !checkForm()) return false;
	  	if(window.XMLHttpRequest) {
			add = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			add = new ActiveXObject("Microsoft.XMLHTTP");
		}
		document.getElementById('addloader').style.display = '';
		document.getElementById('adderror').style.display = 'none';

	    var sendurl = document.addapp.url.value;
	    var appname = document.addapp.app_name.value;
	    var catid = document.addapp.category_id.value;
	    var des = document.addapp.description.value;
	    var radios = document.addapp.ispublic;
	    for (var i = 0, length = radios.length; i < length; i++) {
	        if (radios[i].checked) {
	            var ispublic = radios[i].value;
	            break;
	        }
	    }
	    var author = document.addapp.author.value;
	    var conname = document.addapp.contact_name.value;
	    var conemail = document.addapp.contact_email.value;
	    var idate = '<?php echo date('Y-m-d'); ?>';
		var url = 'appupdate.php';
		var params = "do=add&app_name="+appname+"&url="+sendurl;
		params += "&category_id="+catid+"&description="+des+"&public="+ispublic;
		params += "&author="+author+"&contact_name="+conname;
		params += "&contact_email="+conemail+"&date_installed="+idate;

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
	  					window.location = 'appadmin.php';
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
			var sendurl = document.addapp.url.value;
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
    	if(isEmpty(document.addapp.url))
    	{
    		alert("You must specify a url");
    		return false;
    	}
    	return true;
    }

    function checkForm()
    {
    	if(isEmpty(document.addapp.app_name))
    	{
    		alert("You must specify an application name");
    		return false;
    	}
    	return true;
    }

    function selectCat(id)
    {
    	document.addapp.category_id.value = id;
    }

    function bouncerInfo(id,name)
    {
        var message = "The following information can be used by the application "+
        "developers to connect and verify with App Depot as outlined in the documentation\n\n"+
        "Application Depot Id (ADID) = <?php echo getNVP('adid'); ?>\n"+
        "Application ID = "+id+"\n"+
        "Application Name = "+name+"\n"+
        "AppDepot URL = http://<?php echo $_SERVER['HTTP_HOST'].getNVP('rootdir').'/'; ?>\n"+
        "Referrer = http://<?php echo $_SERVER['HTTP_HOST'].getNVP('rootdir').'/mainwindow.php'; ?>\n";
        alert(message);
    }

    </script>
</head>
<body>
<script type="text/javascript" src="../javascript/wz_tooltip.js"></script>
	<br />
	<div class="dropheader" id="appheader" onClick="toggleSlide('newappslider')">Add New Application</div>
	<div class="dropslider" id="newappslider" style="display:none;height:280px">
	<form name="addapp" action="appadmin.php" method="post">
    	<div class="tree" style="float:left;">
    	Category Tree
    	<div id="treeContainer"></div>
    	</div>
    	<div>
    	<table>
    		<tr>
	    		<td>Application Name:</td>
	    		<td><input type="text" name="app_name" size="40" /></td>
    		</tr>
    		<tr>
	    		<td>Application URL:</td>
	    		<td><input type="url" name="url" size="50" value="http://"/></td>
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
    						echo '<option value="'.$row[0].'">'.$row[1].'</option>';
    					}
    			?>
    			</select></td>
    		</tr>
    		<tr>
    			<td>Description</td>
    			<td><textarea name="description" rows="3" cols="50"></textarea></td>
    		</tr>
    		<tr>
				<td>Public:</td>
				<td>
				<input type="radio" value="1" name="ispublic" />Yes
				&nbsp;&nbsp;&nbsp;
				<input type="radio" value="0" name="ispublic" checked />No
				</td>
			</tr>
    		<tr>
	    		<td>Author:</td>
	    		<td><input type="text" name="author" size="40" /></td>
	    		<td rowspan="3"><div id="adderror" style="display:none"></div>
							<div id="addloader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40px" /></div>
			</td>
    		</tr>
    		<tr>
	    		<td>Contact Name:</td>
	    		<td><input type="text" name="contact_name" size="40" /></td>
    		</tr>
    		<tr>
	    		<td>Contact Email:</td>
	    		<td><input type="text" name="contact_email" size="40" /></td>
    		</tr>
    		<tr>
    			<td>&nbsp;</td>
    			<td><input type="button" name="addapp" value="Add Application" onclick="doAdd()" /></td>
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
		echo '<div class="listheader">Application List</div>';
		echo '<table width="100%" class="listtable">';
		echo '<tr>';
		echo '<td class="listtableheader" width="60px">Enabled</td>';
		echo '<td class="listtableheader"><a href="appadmin.php?orderby=a.app_name">App Name</a></td>';
		echo '<td class="listtableheader"><a href="appadmin.php?orderby=c.name,a.app_name">Category</a></td>';
		echo '<td class="listtableheader"><a href="appadmin.php?orderby=a.contact_name">Contact</a></td>';
		echo '<td class="listtableheader"><a href="appadmin.php?orderby=a.date_installed">Installation Date</a></td>';
		echo '<td class="listtableheader">Status</td>';
		echo '<td class="listtableheader">&nbsp</td>';
		echo '<td class="listtableheader" width="60px">&nbsp;</td><td class="listtableheader" width="60px">&nbsp;</td>';
		echo '</tr>';
		$shaded = 1;
		if(isset($_GET['orderby']))
			$orderby = $_GET['orderby'];
		else
			$orderby = 'a.app_name';

		$query = "SELECT a.app_id, a.app_name, a.contact_name, a.contact_email, a.date_installed, a.url, a.public, a.enabled, c.name FROM applications a, categories c WHERE a.category_id = c.category_id ORDER BY $orderby ASC";
		$result = mysql_query($query) or dieLog("Could not get application information from database because ".mysql_error());
		while($row = mysql_fetch_assoc($result))
			{
				$aid = $row['app_id'];
				$aname = $row['app_name'];
				$cname = $row['name'];
				$conname = $row['contact_name'];
				$conemail = $row['contact_email'];
				$date = date('Y-m-d',strtotime($row['date_installed']));
				$url = $row['url'];
				$public = $row['public'];
				$enabled = $row['enabled'];

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
				if(!$enabled)
					echo '<td><input type="checkbox" value="1" name="enabled" id="'.$aid.'" onClick="doEnable('.$aid.')"></td>';
				else
					echo '<td><input type="checkbox" value="1" name="enabled" id="'.$aid.'" checked onClick="doEnable('.$aid.')"></td>';
				echo "<td>$aname</td>";
				echo "<td>$cname</td><td><a href='mailto:$conemail'>$conname</a></td><td>$date</td>";
				if(validUrl($url))
					echo '<td><span class="success">Link Good</span></td>';
				else
					echo '<td><span class="error">Link Appears Invalid</span></td>';
				echo '<td><a href="#js" onClick="bouncerInfo('.$aid.',\''.$aname.'\')">EAC</a>';
				echo "<td><a href='appedit.php?appid=".$aid."'>Edit</a></td>";
				echo "<td><a href='#' onClick='doDelete(".$aid.")'>Delete</a></td>";
				echo '</tr>';
			}
		echo '</table>';
	?>
	</div>
<script type="text/javascript">
renderTree();
</script>
</body>
</html>