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
$theme = getNVP('theme');
$adminemail = getNVP('adminemail');
$welcometext = getNVP('welcometext');
$sessionchecktime = getNVP('sessionchecktime') * 1000; 
$sessiontimeout = $sess->life_time;  // Get the session time from the session manager class in sessions.php
$sliderheight = 130;
$controlheight = 33; 
$userlevel = sessionVerify();
$username = $_SESSION['username'];
$realname = $_SESSION['realname'];
$sessmins = floor($sessiontimeout / 60);
$sesssecs = $sessiontimeout % 60;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="overflow:hidden;">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="themes/<?php echo $theme; ?>/style.css" />
	<link rel="stylesheet" type="text/css" href="themes/<?php echo $theme; ?>/ddsmoothmenu.css" />
	<title>Application Depot</title>
	<script type="text/javascript" src="javascript/jquery/js/jquery.min.js"></script>
	<script type="text/javascript" src="javascript/smoothmenu/ddsmoothmenu.js"></script>	
	<script type="text/javascript" src="javascript/timer.js"></script>
	<script type="text/javascript">
	var appname = '';
	var viewportwidth;
	var viewportheight;
	var intid;
	var slidestate = 0;  // Open
	var interval = 5;
	var notchheight = 5;
	var sliderheight = <?php echo $sliderheight; ?>; // Height, in px, of top slider
	var slidertarget = <?php echo $sliderheight; ?>; // Height, in px, of top slider at start
	var controlheight = <?php echo $controlheight; ?>; // Height, in px, of control menu
	var appframeheight = 0;  // Application frame height, in px.
	var noticeheight = 0;
	var noticetarget = 0;
	var sliderdone = false;
	var noticedone = false;
	var sesscheckfreq = <?php echo $sessionchecktime; ?>;
	var minutes = <?php echo $sessmins; ?>;
	var seconds = <?php echo $sesssecs; ?>		
 	
 	var sesscheck;    //rpc variable
 	var sessupdate;   //rpc variable
 	var appcall; //rpc variable
 	
 	function checkSession()
 	{
 		if(window.XMLHttpRequest) {
			sesscheck = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			sesscheck = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var sessid = '<?php echo session_id(); ?>';
		var url = 'checksession.php';
		var params = "sessionid="+sessid;
		
		// Send session update request to script via post
		sesscheck.open("POST",url,true);
		
		// Set the headers
		sesscheck.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		sesscheck.setRequestHeader("Content-length", params.length);
		sesscheck.setRequestHeader("Connection", "close");
		
		sesscheck.onreadystatechange = callbackSesscheck;
		sesscheck.send(params);
		return false;
 	}
 	
 	function callbackSesscheck()
	  {
	  	if(sesscheck.readyState == 4)
	  		{
	  			var response = sesscheck.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			if(result == 'error')
	  				{
	  					var error = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  					timerComplete(error);
	  				}
	  			else
	  				{
	  				    var newmins = resp[0].getElementsByTagName("minutes")[0].childNodes[0].nodeValue;
	  				    var newsecs = resp[0].getElementsByTagName("seconds")[0].childNodes[0].nodeValue;
	  				    if(newmins < 0 && newsecs < 0)
	  				    	timerComplete("You have been logged out");
	  				    else
	  				    	timer.init(newmins,newsecs,'countdown');
	  				}
	  		}
	  }
 	
 	function updateSession()
 	{
 		if(window.XMLHttpRequest) {
			sessupdate = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			sessupdate = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var sessid = '<?php echo session_id(); ?>';
		var url = 'updatesession.php';
		var params = "sessionid="+sessid+"&app=AppDepot";
		
		// Send session update request to script via post
		sessupdate.open("POST",url,true);
		
		// Set the headers
		sessupdate.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		sessupdate.setRequestHeader("Content-length", params.length);
		sessupdate.setRequestHeader("Connection", "close");
		
		sessupdate.onreadystatechange = callbackSessupdate;
		sessupdate.send(params);
		return false;
 	}
 	
 	function callbackSessupdate()
	  {
	  	if(sessupdate.readyState == 4)
	  		{
	  			var response = sessupdate.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			if(result == 'error')
	  				{
	  					var error = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  					document.getElementById('countdown').className = 'error';
	  					document.getElementById('countdown').innerHTML = 'Error';
	  				}
	  			else
	  				{
	  				    timer.init(minutes,seconds,'countdown');
	  				}
	  		}
	  }
 	
	function getHeights() {
		 // Start by getting the screen width and height.  We'll use both of these later.
	     
	     // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
		 if (typeof window.innerWidth != 'undefined')
		 {
		      viewportwidth = window.innerWidth,
		      viewportheight = window.innerHeight
		 }
	 
		// IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
	
		 else if (typeof document.documentElement != 'undefined'
		     && typeof document.documentElement.clientWidth !=
		     'undefined' && document.documentElement.clientWidth != 0)
		 {
		       viewportwidth = document.documentElement.clientWidth,
		       viewportheight = document.documentElement.clientHeight
		 }
	 
	 	// older versions of IE
	 
		 else
		 {
		       viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
		       viewportheight = document.getElementsByTagName('body')[0].clientHeight
		 }
	 }
	 function setPage()
	 {
	 	// Setup the page according to browser width and height
	 	getHeights();
	 	appframeheight = viewportheight - (sliderheight+controlheight);
	 	document.getElementById('appframe').height = appframeheight+'px';
	 	var menuwidth = document.getElementById('smoothmenu1').offsetWidth;
	 	document.getElementById('menucontainer').style.width = menuwidth+'px';
	 }
	 function doSlide()
	 {
	 	noticedone = false;
	 	sliderdone = false;
	    if(slidestate)
	    	{
	    		// Do Open
	    		intid = setInterval('slideDown()',interval);
	    		slidestate = 0;
	    	}
	    else
	    	{
	    		// Do Close
	    		document.getElementById('slider').style.overflow = 'hidden';
	    		intid = setInterval('slideUp()',interval);
	    		slidestate = 1;	
	    	}
	 }
	 function slideUp()
	 {
	 	var slider = document.getElementById('slider');
	 	var notice = document.getElementById('notice');
	 	if(parseInt(slider.style.height) > 0)
	 	{
	 		slider.style.height = (parseInt(slider.style.height) - notchheight)+'px';
	 		sliderheight = parseInt(slider.style.height) + controlheight;
	 		sliderdone = false;
	 	}
	 	else
	 	{
	 		slider.style.display = 'none';
	 		sliderdone = true;	
	 		document.getElementById('tabtext').innerHTML = 'Show Menu';
	 	}	
	    if(parseInt(notice.style.height) > 0)
	    {
	    	notice.style.height = (parseInt(notice.style.height) - notchheight)+'px';
	    	noticeheight = parseInt(notice.style.height);
	    }
	    else
	    {
	    	noticedone = true;
	    	notice.style.display = 'none';
	    }
	    if(noticedone && sliderdone)
	    	clearInterval(intid);
	 	setPage();
	 }
	 function slideDown()
	 {
	 	var slider = document.getElementById('slider');
	 	var notice = document.getElementById('notice');
	 	if(parseInt(slider.style.height) < slidertarget)
	 	{
	 		slider.style.display = '';
	 		slider.style.height = (parseInt(slider.style.height) + notchheight)+'px';
	 		sliderheight = parseInt(slider.style.height) + controlheight;
	 	}
	 	else
	 	{
	 		document.getElementById('slider').style.overflow = '';
	        document.getElementById('tabtext').innerHTML = 'Collapse Menu';
	        sliderdone = true;
	 	}	
	 	if(parseInt(notice.style.height) < noticetarget)
	    {
	    	notice.style.height = (parseInt(notice.style.height) + notchheight)+'px';
	    	notice.style.display = '';
	    	noticeheight = parseInt(notice.style.height);
	    }
	    else
	    {
	    	noticedone = true;
	    }
	    if(noticedone && sliderdone)
	    	clearInterval(intid);
	 	setPage();
	 }
	 
	 function setNotice()
	 {
	 	noticetarget = parseInt(document.getElementById('notice').offsetHeight);
	 	noticetarget = Math.round(noticetarget/5)*5; // Multiples of five
	 	document.getElementById('notice').style.height = noticetarget+'px';;	
	 }
	 
	 function currentApp(x,u)
	 {
	 	// Check that the application requested is valid and get the url,
	 	// name, and other information.  This also makes a log in the database.
	 	document.getElementById('currentapp').innerHTML = '<img src="themes/<?php echo getNVP('theme'); ?>/images/bar-loader.gif" />';
	 	if(x == 'd') //dashboard
	 		{
	 			document.getElementById('appframe').src = 'dashboard.php';
	 			appname = 'Dashboard';
	 			return true;
	 		}
	 	if(x == 'a') //admin
	 		{
	 			document.getElementById('appframe').src = 'admin/index.php';
	 			appname = 'Administration';
	 			return true;
	 		}
	 	
	 	if(window.XMLHttpRequest) {
			appcall = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			appcall = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var url = 'apprequest.php';
		var params = "appid="+x+"&userid="+u;
		
		// Send session update request to script via post
		appcall.open("POST",url,true);
		
		// Set the headers
		appcall.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		appcall.setRequestHeader("Content-length", params.length);
		appcall.setRequestHeader("Connection", "close");
		
		appcall.onreadystatechange = callbackappcall;
		appcall.send(params);
		return false;
	 		
	 }
	
	function callbackappcall()
	  {
	  	if(appcall.readyState == 4)
	  		{
	  			var response = appcall.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			if(result == 'error')
	  				{
	  					var error = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  					alert(error);
	  				}
	  			else
	  				{
	  					var url = decodeURIComponent(resp[0].getElementsByTagName("url")[0].childNodes[0].nodeValue);
	  				    appname = resp[0].getElementsByTagName("name")[0].childNodes[0].nodeValue; 				    
	 					document.getElementById('appframe').src = url;				
	  				}
	  		}
	  }
	  
	function updateAppName() {
		document.getElementById('currentapp').innerHTML = appname;
	}
	  
	function init()
	{
	    timer.init(minutes,seconds,'countdown');
	    checkinterval = setInterval('checkSession()',sesscheckfreq);
	}
	
	window.onload = init;
	
	</script>
	</head>
<body onResize="setPage()">
<div class="slider" style="height:<?php echo $sliderheight; ?>px;" id="slider">
    <div class="logocontainer">
    	<img class="imglogo" src="themes/<?php echo $theme; ?>/images/logosmall.png" />
	</div>
	<div class="welcometext"><?php echo $welcometext; ?></div>
<center>
	 <div class="menucontainer" id="menucontainer">
		 <div id="smoothmenu1" class="ddsmoothmenu">
		 <?php echo getMenu($username,$userlevel); ?>
		 </div>
	 </div>
 </center>
</div>
<div class="slidercontrol">
	<table width="100%">
	<tr>
		<td class="controlleft" NOWRAP>
		Logged in as <?php echo $realname; ?>: <span id="countdown">00:00</span> <span class="refresh"><a href="#" onclick="updateSession()">Refresh Session</a></span> - <a href="logout.php">Logout</a>
		</td>
		<td class="controlcenter">
		<span id="currentapp">Dashboard</span>
	    </td>
	    <td class="controlright">
	    Powered by AppDepot
	    </td>
    </tr>
    </table>
</div>
<div class="tab" onClick="doSlide()"><center><div class="tabtext" id="tabtext">Collapse Menu</div></center></div> 
<div class="applicationwindow">
<iframe name="appframe" id="appframe" style="" frameborder="0" height="300" width="100%" src="dashboard.php" onload="return updateAppName()">
Your browser does not support iframes.  Application Depot requires the use of iframes to continue.
</iframe>
</div>
<?php
 if($userlevel == 0)  // Admin
 	{
 		// Show admin log pane
 		$duration = getNVP('admin_showlog_history');  // Duration in days
 		if($duration)
 		{
 		$duration = 60 * 60 * 24 * $duration;
 		$begin = time() - $duration;
 		$begin = date('Y-m-d',$begin);
 		$query = "SELECT timestamp, application, entry FROM logs WHERE severity = 3 and timestamp > '$begin' order by timestamp DESC";
 		$result = mysql_query($query) or dieLog("Could not get latest errors because ".mysql_error());
 		if(mysql_num_rows($result))
 			{
 				echo '<div class="noticecontainer" id="notice">';
 				echo '<center><div class="noticebox">';
 				echo '<table width="100%">';
 				while($row = mysql_fetch_row($result))
 					{
 						echo '<tr>';
 						echo '<td>'.date('Y-m-d H:i:s',strtotime($row[0])).'</td>';
 						echo '<td>'.$row[1].'</td>';
 						echo '<td width="75%">'.$row[2].'</td>';
 						echo '</tr>';
 					}
 				echo '</table>';
 				echo '</div>';
 				echo '</center>';
 				echo '</div>';	
 				echo '<script type="text/javascript">setNotice()</script>';
 			}
 		else
 			echo '<div id="notice"></div>';	
 		}
 		else
 			echo '<div id="notice"></div>';
 	}
 	else
 		echo '<div id="notice"></div>';
?>
<div class="footer">Powered by AppDepot - Version <?php echo getNVP('version'); ?> - <a href="http://www.appdepot.org">http://www.appdepot.org</a></div>
<script type="text/javascript">
setPage();
</script>
</body>
</html>