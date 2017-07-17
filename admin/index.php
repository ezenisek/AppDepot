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
	<link rel="stylesheet" type="text/css" href="../themes/<?php echo $theme; ?>/tabber.css" />
	<title>Admin Page</title>
	<script type="text/javascript" >
	var source = 'blank.php';
	function reloadFrame(frame)
	{
		frame = frame.toString();
		source = document.getElementById(frame).name;
		document.getElementById(frame).src = 'blank.php';
		document.getElementById(frame).src = source;
		
		for(var i=0;i<7;i++)
		{
			if(i != frame)
			document.getElementById(i).src = 'blank.php';
		}
	}
	function resize_iframe()
	{
		var height = document.documentElement.clientHeight;
		var offset = 81;
		//resize the iframes according to the size of the window
		document.getElementById('0').style.height = (parseInt(height)-offset)+'px';
		document.getElementById('1').style.height = (parseInt(height)-offset)+'px';
		document.getElementById('2').style.height = (parseInt(height)-offset)+'px';
		document.getElementById('3').style.height = (parseInt(height)-offset)+'px';
		document.getElementById('4').style.height = (parseInt(height)-offset)+'px';
		document.getElementById('5').style.height = (parseInt(height)-offset)+'px';
		document.getElementById('6').style.height = (parseInt(height)-offset)+'px';
	}
	var interval = setInterval('resize_iframe()',500);
	var tabberOptions = {
		'onClick': function(argsObj) {
	    var t = argsObj.tabber; /* Tabber object */
	    var id = t.id; /* ID of the main tabber DIV */
	    var i = argsObj.index; /* Which tab was clicked (0 is the first tab) */
	    var e = argsObj.event; /* Event object */	
	    reloadFrame(i);
		},
	'addLinkId': true
	};
	function updateSource(frame)
	{
		if(document.getElementById(frame).src != source)
			document.getElementById(frame).src = source;
	}
	document.write('<style type="text/css">.tabber{display:none;}<\/style>');
	</script>
	<script type="text/javascript" src="../javascript/tabber.js"></script>
</head>
<body>
<div class="tabber">
	<div class="tabbertab" title="User Administration">
	<iframe id='0' frameborder='no' width="100%" src="useradmin.php" name="useradmin.php"></iframe>
	</div>
	<div class="tabbertab" title="App Administration">
	<iframe id='1' frameborder='no' width="100%" src="blank.php" name="appadmin.php"></iframe>
	</div>
	<div class="tabbertab" title="Category Administration">
	<iframe id='2' frameborder='no' width="100%" src="blank.php" name="catadmin.php"></iframe>
	</div>
	<div class="tabbertab" title="LDAP Administration">
	<iframe id='3' frameborder='no' width="100%" src="blank.php" name="ldapadmin.php"></iframe>
	</div>
	<div class="tabbertab" title="Settings">
	<iframe id='4' frameborder='no' width="100%" src="blank.php" name="depotadmin.php"></iframe>
	</div>
	<div class="tabbertab" title="Utilities">
	<iframe id='5' frameborder='no' width="100%" src="blank.php" name="utilities.php"></iframe>
	</div>
	<div class="tabbertab" title="Reports">
	<iframe id='6' frameborder='no' width="100%" src="blank.php" name="reports.php"></iframe>
	</div>
</div>
<script type="text/javascript">resize_iframe();</script>
</body>
</html>