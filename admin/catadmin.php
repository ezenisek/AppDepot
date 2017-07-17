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
	<link rel="stylesheet" type="text/css" href="../themes/<?php echo $theme; ?>/jsTree.css" />
	<script type="text/javascript" src="../javascript/motionpack.js"></script>
	<script type="text/javascript" src="../javascript/jsTree.js"></script> 
	<title>App Depot Category Admin</title>
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
	var add;
	var del;
	var selected = 0;
	function isEmpty(mytext) {
		var re = /^\s{1,}$/g; //match any white space including space, tab, form-feed, etc.
			if ((mytext.value.length==0) || (mytext.value=='') || ((mytext.value.search(re)) > -1)) {
			return true;
			}
			else {
			return false;
			}
		}
	function isNumeric(strString)
   		{
   			var strValidChars = "0123456789.-";
   			var strChar;
   			var blnResult = true;
			if (strString.length == 0) return false;
			//  test strString consists of valid characters listed above
   			for (i = 0; i < strString.length && blnResult == true; i++)
      		{
      			strChar = strString.charAt(i);
      			if (strValidChars.indexOf(strChar) == -1)
         		{
         		blnResult = false;
         		}
      		}
   			return blnResult;
   		}	
	
	function selectCat(id)
		{
			selected = id;
			document.newcat.catparent.value = id;
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
		var name = document.newcat.catname.value;
		var desc = document.newcat.catdescription.value;
		var parent = document.newcat.catparent.value;
		var sortorder = document.newcat.sortorder.value;
		var url = 'catupdate.php';
		var params = "do=add&name="+name+"&description="+desc+"&parent_id="+parent+"&sortorder="+sortorder;
		
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
	  					window.location = 'catadmin.php';
	  				}
	  		}
	  }
	
	function doDelete(catid)
	 {
	 	if(!confirm("You are about to delete this category.  Please ensure that there are no sub categories or applications under it. This cannot be undone. \n\nContinue?"))
	 		return false;
	 	if(window.XMLHttpRequest) {
			del = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			del = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var url = 'catupdate.php';
		var params = "do=delete&category_id="+catid;
		
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
	  					window.location = 'catadmin.php';
	  				}
	  		}
	  }	
	
	function formAddCheck() 
	  {
	    if(isEmpty(document.newcat.catname))
	      {
	      alert("You must specify a Category Name");
	      return false;
	      }
	    if(isEmpty(document.newcat.sortorder))
	      {
	      alert("You must choose a Sort Order");
	      return false;
	      }
	    if(!isNumeric(document.newcat.sortorder.value))
	      {
	      alert("Sort Order must be a numeric value");
	      return false;
	      }
	    return true;
  	 }
	
	function doEdit(sel)
	 {
	 	window.location = 'catedit.php?category_id='+sel;
	 }
	
	</script>
</head>
<body>
	<script type="text/javascript" src="../javascript/wz_tooltip.js"></script> 
	<br />
	<div class="dropheader" id="catheader" onClick="toggleSlide('newcatslider')">Add New Category</div>
	<div class="dropslider" id="newcatslider" style="display:none;height:180px">
	<form name="newcat" method="post" action="catadmin.php">
	<table>
		<tr>
			<td>New Category Name:</td>
			<td colspan="2"><input type="text" name="catname" size="40" /></td>
		</tr>
		<tr>
			<td>Category Description:</td>
			<td colspan="2"><textarea name="catdescription" rows="3" cols="40"></textarea></td>
		</tr>
		<tr>
			<td>Category Parent</td>
			<td><select name="catparent">
				<option value="0">Top Level</option>
				<?php 
					$query = "SELECT category_id, name FROM categories ORDER BY parent_id ASC, sortorder ASC, name ASC";
					$result = mysql_query($query) or dieLog("Could not get category list from database because ".mysql_error());
					while($row = mysql_fetch_row($result))
						{
							echo '<option value="'.$row[0].'">'.$row[1].'</option>';	
						}
				?>
				</select></td>
				<td rowspan="3"><span id="addloader" style="width:400px;display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="40px" /></span>
  				<span id="adderror" style="width:350px;display:none;"></span></td>
		</tr>
		<tr>
			<td>Sort Order:</td>
			<td><input type="text" name="sortorder" size="4" value="0" /></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="button" name="newcat" value="Add Category" onClick="doAdd()" /></td>
		</tr>
	</table>	
	</form>
	</div>
	<br />
	<hr />
	<div id="generror"></div>
	<div class="listheader">Category Structure</div>	
	<div class="tree" style="float:left;">
	<div id="treeContainer"></div>
	</div>
	<div style="padding:10px;">
	<a href="#1" onclick="doEdit(selected)">Edit Selected Category</a>
	<br />
	<br />
	<a href="#2" onclick="doDelete(selected)">Delete Selected Category</a>
	</div>
<script type="text/javascript">
renderTree();
</script>
</body>
</html>