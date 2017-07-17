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
require_once('../includes/fckeditor/fckeditor.php');
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
	<title>App Depot Utilities</title>
	<script type="text/javascript" src="../javascript/motionpack.js"></script>
	<script type="text/javascript">
	var lock;
	var lockdown;
	var mail;
	var maint;
	var maintdate;
	var mainttype;
    
    function doMaint(type)
	  {	
	  	mainttype = type;
	  	if(window.XMLHttpRequest) {
			maint = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			maint = new ActiveXObject("Microsoft.XMLHTTP");
		}
		document.getElementById(type+'loader').style.display = '';
		var url = 'domaintenance.php';
		var params = "type="+type+"&date="+maintdate;
		
		// Send information to lookup script via post
		maint.open("POST",url,true);
		
		// Set the headers
		maint.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		maint.setRequestHeader("Content-length", params.length);
		maint.setRequestHeader("Connection", "close");
		
		maint.onreadystatechange = callbackMaint;
		maint.send(params);
		return false;
	  }

	function callbackMaint()
	  {
	  	if(maint.readyState == 4)
	  		{
	  			var response = maint.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			var content = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  			if(result == 'error')
	  				{
	  					document.getElementById(mainttype+'loader').style.display='none';
	  					document.getElementById(mainttype+'result').style.display = '';
	  					document.getElementById(mainttype+'result').className = 'error';
	  					document.getElementById(mainttype+'result').innerHTML = 'Error: '+URLDecode(content);
	  				}
	  			else
	  				{
	  					var maintresults = resp[0].getElementsByTagName("details")[0].childNodes[0].nodeValue;
	  					document.getElementById(mainttype+'loader').style.display='none';
	  					document.getElementById(mainttype+'result').style.display = '';
	  					document.getElementById(mainttype+'result').className = 'success';
	  					document.getElementById(mainttype+'result').innerHTML = content;
	  					document.getElementById('maintresults').value = URLDecode(maintresults);
	  				}
	  		}
	  }
    
	function sendMail()
	  {
	  	if(!checkMailForm())
	  		return false;	
	  		
	  	if(window.XMLHttpRequest) {
			mail = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			mail = new ActiveXObject("Microsoft.XMLHTTP");
		}
		document.getElementById('mailloader').style.display = '';
		var url = 'sendadminemail.php';
		var message = encodeURI(FCKeditorAPI.GetInstance('message').GetXHTML(true));
		var subject = document.utilities.mailsubject.value;
		var apps = new Array();
		if(document.utilities.applist.constructor == Array)
			for(var i=0; i<document.utilities.applist.length; i++)
			{
			apps[i] = document.utilities.applist[i].value;
			}
		else
			var apps = document.utilities.applist.value;
		var params = "message="+message+"&subject="+subject+"&apps="+apps;

		// Send information to lookup script via post
		mail.open("POST",url,true);
		
		// Set the headers
		mail.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		mail.setRequestHeader("Content-length", params.length);
		mail.setRequestHeader("Connection", "close");
		
		mail.onreadystatechange = callbackMail;
		mail.send(params);
		return false;
	  }

	function callbackMail()
	  {
	  	if(mail.readyState == 4)
	  		{
	  			var response = mail.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			var content = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  			if(result == 'error')
	  				{
	  					document.getElementById('mailloader').style.display='none';
	  					document.getElementById('mailresult').style.display = '';
	  					document.getElementById('mailresult').className = 'error';
	  					document.getElementById('mailresult').innerHTML = 'Error: '+content;
	  				}
	  			else
	  				{
	  					//var recipients = resp[0].getElementsByTagName("recipients")[0].childNodes[0].nodeValue;
	  					document.getElementById('mailloader').style.display='none';
	  					document.getElementById('mailresult').style.display = '';
	  					document.getElementById('mailresult').className = 'success';
	  					document.getElementById('mailresult').innerHTML = content;
	  					//document.getElementById('maintresults').value = recipients;
	  				}
	  		}
	  }
	 
	function doLockdown()
	  {
	  	lockdown = document.utilities.lockdown.checked;
	  	if(lockdown)
	  		{
	  		if(!confirm("This will put App Depot into a lockdown state, preventing any non-admin users from logging in or using programs.  Any currently logged in non-admin users will be forcefully logged out. \n\nContinue?"))
	  			{
	  			document.utilities.lockdown.checked = false;
	  			return false;
	  			}
	  		}
	  	else
	  		{
	  		if(!confirm("This will unlock App Depot and allow non-admin users to login and use programs. \n\nContinue?"))
	  			{
	  			document.utilities.lockdown.checked = true;
	  			return false;
	  			}	
	  		}
	  	if(window.XMLHttpRequest) {
			lock = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			lock = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var url = 'dolockdown.php';
		var params = "lockdown="+lockdown;
		
		// Send information to lookup script via post
		lock.open("POST",url,true);
		
		// Set the headers
		lock.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		lock.setRequestHeader("Content-length", params.length);
		lock.setRequestHeader("Connection", "close");
		
		lock.onreadystatechange = callbackLockdown;
		lock.send(params);
		return false;
	  }
	  
	function callbackLockdown()
	  {
	  	if(lock.readyState == 4)
	  		{
	  			var response = lock.responseXML;
	  			var resp = response.getElementsByTagName("response");
	  			var result = resp[0].getElementsByTagName("result")[0].childNodes[0].nodeValue;
	  			var content = resp[0].getElementsByTagName("content")[0].childNodes[0].nodeValue;
	  			if(result == 'error')
	  				{
	  					document.getElementById('lockdown').style.display = '';
	  					document.getElementById('lockdown').className = 'error';
	  					document.getElementById('lockdown').innerHTML = 'Error: '+content;
	  					if(lockdown)
	  						{
	  						document.utilities.lockdown.checked = false;	
	  						}
	  					else
	  						{
	  						document.utilities.lockdown.checked = true;	
	  						}
	  				}
	  			else
	  				{
	  					document.getElementById('lockdown').style.display = '';
	  					document.getElementById('lockdown').className = 'success';
	  					document.getElementById('lockdown').innerHTML = content;
	  					if(lockdown)
	  						{
	  							document.getElementById('lockdown').className = 'error';	
	  						}
	  					else
	  						{
	  							document.getElementById('lockdown').className = 'success';
	  						}
	  				}
	  		}
	  }
	 
	  function checkMailForm() 
	  {
	    if(isEmpty(document.utilities.mailsubject.value))
	      {
	      alert("You must specify a mail subject");
	      return false;
	      }
	    else if(isEmpty(FCKeditorAPI.GetInstance('message').GetXHTML(true)))
	      {
	      alert("You must enter some information to email");
	      return false;
	      }
	    if(!document.utilities.applist)
			{
	    		alert("Please select at least one Application to mail to");
	    		return false;
	    	}
	    return true;
  	 }
	
	function isEmpty(mytext) {
		var re = /^\s{1,}$/g; //match any white space including space, tab, form-feed, etc.
			if ((mytext.length==0) || (mytext=='') || ((mytext.search(re)) > -1)) {
			return true;
			}
			else {
			return false;
			}
	 }
	 
	 function selectApp(select) 
	 	{ 
	 		var option = select.options[select.selectedIndex]; 
	 		var ul = select.parentNode.getElementsByTagName('ul')[0]; 
	 		var choices = ul.getElementsByTagName('input'); 
	 		for (var i = 0; i < choices.length; i++) 
	 			if (choices[i].value == option.value) 
	 				return; 
	 		var li = document.createElement('li'); 
	 		var input = document.createElement('input'); 
	 		var text = document.createTextNode(option.firstChild.data); 
	 		input.type = 'hidden'; 
	 		input.value = option.value; 
	 		input.name =  'applist';
	 		li.appendChild(input); 
	 		li.appendChild(text); 
	 		li.setAttribute('onclick', 'this.parentNode.removeChild(this);');
	 		li.setAttribute('class', 'dynamicli'); 
	 		ul.appendChild(li); 
	 	}

	 function checkdate(input)
	 {
		var returnval=false;
		if (!input.match(/^[0-9]{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])/))
			alert("Invalid Date Format. Please correct and submit again.");
		else
			{ //Detailed check for valid date ranges
			var monthfield=input.split("-")[1];
			var dayfield=input.split("-")[2];
			var yearfield=input.split("-")[0];
			var dayobj = new Date(yearfield, monthfield-1, dayfield);
			if ((dayobj.getMonth()+1!=monthfield)||(dayobj.getDate()!=dayfield)||(dayobj.getFullYear()!=yearfield))
				alert("Invalid Day, Month, or Year range detected. Please correct and submit again.");
			else
				returnval=true;
			}
		return returnval;
	 }

	 function doTruncate()
	 	{
	 		maintdate = prompt("Please enter a date in the form YYYY-MM-DD to truncate the Application Depot logs in the database.  All log entries prior to this date will be deleted.  This cannot be undone. \n\n Enter a date or click Cancel"," ");	 		
	 		if(maintdate != '' && maintdate != null)
	 			{
	 				if(checkdate(maintdate))
	 					doMaint('lt');
	 				else
	 					maintdate = '';
	 			}
	 		return false; 		
	 	}

	 function doInactiveUser()
	 	{
	 		maintdate = prompt("Please enter a date in the form YYYY-MM-DD to remove any users who have not been active since that date.  All such users will be deleted, including their permissions.  This cannot be undone. \n\n Enter a date or click Cancel"," ");	 		
	 		if(maintdate != '' && maintdate != null)
	 			{
	 				if(checkdate(maintdate))
	 					doMaint('iu');
	 				else
	 					maintdate = '';
	 			}
	 		return false;
	 	}
	 	
	 function doInactiveApp()
	 	{
	 		maintdate = prompt("Please enter a date in the form YYYY-MM-DD to remove any applications which have not been used since that date.  All such applications will be deleted, including their permissions.  This cannot be undone. \n\n Enter a date or click Cancel"," ");	 		
	 		if(maintdate != '' && maintdate != null)
	 			{
	 				if(checkdate(maintdate))
	 					doMaint('ia');
	 				else
	 					maintdate = '';
	 			}
	 		return false;
	 	}
	 	
	 function URLDecode(value)
		{
		   // Replace + with ' '
		   // Replace %xx with equivalent character
		   // Put [ERROR] in output if %xx is invalid.
		   var HEXCHARS = "0123456789ABCDEFabcdef"; 
		   var encoded = value;
		   var plaintext = "";
		   var i = 0;
		   while (i < encoded.length) {
		       var ch = encoded.charAt(i);
			   if (ch == "+") {
			       plaintext += " ";
				   i++;
			   } else if (ch == "%") {
					if (i < (encoded.length-2) 
							&& HEXCHARS.indexOf(encoded.charAt(i+1)) != -1 
							&& HEXCHARS.indexOf(encoded.charAt(i+2)) != -1 ) {
						plaintext += unescape( encoded.substr(i,3) );
						i += 3;
					} else {
						alert( 'Bad escape combination near ...' + encoded.substr(i) );
						plaintext += "%[ERROR]";
						i++;
					}
				} else {
				   plaintext += ch;
				   i++;
				}
			} // while
		   return plaintext;
		};
	 
	</script>
</head>
<body>
<script type="text/javascript" src="../javascript/wz_tooltip.js"></script>
<form name="utilities" action="utilities.php" method="post">
<br />
<div class="listheader">App Depot Utilities</div>
<div class="dropslider">
	<br />
	<div><h2><a href="#a" onClick="toggleSlide('lockdownslider')">App Depot Lockdown</a></h2></div>
	<div class="infobox" id="lockdownslider" style="display:none;height:70px">
	<div class="smalltext">Locking down App Depot prevents non-admin users from logging in.  This can allow for program updates or be done in case of a
	user security issue.  Check the box to Lockdown App Depot.  All non-admin users who are currently logged in will be logged out.</div>
	App Depot Lockdown:&nbsp;&nbsp;&nbsp;&nbsp; 
	<input type="checkbox" name="lockdown" value="1" onclick="doLockdown()" <?php if(getNVP('lockdown')) echo 'checked'; ?> />
	&nbsp;&nbsp;&nbsp;&nbsp; 
	<span id="lockdown"></span>
	</div>

	<div><h2><a href="#c" onclick="toggleSlide('mailslider')">Communication</a></h2></div>
	<div class="infobox" id="mailslider" style="display:none;height:400px">
	<div class="smalltext">Use the following form to send out email to everyone in the system 
	or to users of a specific application.  As you choose from the dropdown the applications will
	be added to the list.  Click one to remove it.</div>
	<br />
		<div style="float:left;margin-right:15px;">
		<b>Application List</b><br />
		
		<ul></ul> 
			<select onchange="selectApp(this);"> 
			<option value="null">&nbsp;</option>
			<option value="all">All Users</option>
			<?php
			 	$query = "SELECT app_id, app_name FROM applications WHERE public = 0 ORDER BY app_name ASC";
			 	$result = mysql_query($query) or dieLog("Could not get application list from database because ".mysql_error());
			 	while($row = mysql_fetch_row($result))
			 		{
			 			echo '<option value="'.$row[0].'">'.$row[1].'&nbsp;&nbsp;</option>';	
			 		}
			?>
			</select>
		</div>
		<div>
		Mail Subject: <input type="text" name="mailsubject" size="60" /><br />
		<hr width="600"/>
		<?php
	          $editor = new FCKeditor('message');
	          $editor->BasePath = '../includes/fckeditor/';
	          $editor->ToolbarSet = 'Custom';
	          $editor->Value = '';
	          $editor->Height = '280';
	          $editor->Width = '600';
	          $editor->Create();
	    ?>
	    <br />
	    <div height="35">
	    <input type="button"  style="vertical-align:top;" name="sendmail" value="Send Mail" onClick="sendMail()" />
	    <span id="mailloader" style="display:none"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="30" /></span>
	    <span id="mailresult" style="display:none"></span>
	    </div>
    	</div>
	</div>

	<div><h2><a href="#m" onClick="toggleSlide('maintslider')">Maintenance</a></h2></div>
	<div class="infobox" id="maintslider" style="display:none;height:400px">
	<div class="smalltext">Mouseover for more information. These checks will perform maintenance on the database, allowing you to clean your App Depot installation with
	the push of a button.  Performing this maintenance will automatically fix any problems encountered, so please be sure you want to perform
	the maintenance before clicking the button.  You will be asked for a date when truncating logs or culling inactive users and applications. 
	If you would simply like to see information about inactive users or programs without removing
	them, please run the appropriate report.</div>
	<br />
	<table width="800px">
	
	<tr>
		<td class="tip" onMouseOver="Tip('Checks that the database relationships between users and applications are correct')" onMouseOut="UnTip()">Database Consistency Check</td>
		<td><input type="button" name="dbcheck" value="Perform Check" onClick="doMaint('db')"/></td>
		<td width="400px">
			<span id="dbloader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="30px" /></span>
			<span id="dbresult" style="display:none;"></span>
		</td>
	</tr>
	
	<tr>
		<td class="tip" onMouseOver="Tip('Truncates the log table in the database prior to the given date')" onMouseOut="UnTip()">Database Log Tuncation</td>
		<td><input type="button" name="truncate" value="Truncate Logs" onClick="doTruncate()"/></td>
		<td>
			<span id="ltloader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="30px" /></span>
			<span id="ltresult" style="display:none;"></span>
		</td>
	</tr>
	
	<tr>
		<td class="tip" onMouseOver="Tip('Checks the database for users who have not logged in since the given date and removes them')" onMouseOut="UnTip()">Inactive User Check</td>
		<td><input type="button" name="iucheck" value="Perform Check" onClick="doInactiveUser()"/></td>
		<td>
			<span id="iuloader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="30px" /></span>
			<span id="iuresult" style="display:none;"></span>
		</td>
	</tr>
	
	<tr>
		<td class="tip" onMouseOver="Tip('Checks for users who no longer exist in any LDAP source and removes them')" onMouseOut="UnTip()">Unlinked User Check</td>
		<td><input type="button" name="uucheck" value="Perform Check" onClick="doMaint('uu')"/></td>
		<td>
			<span id="uuloader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="30px" /></span>
			<span id="uuresult" style="display:none;"></span>
		</td>
	</tr>
	
	<tr>
		<td class="tip" onMouseOver="Tip('Checks the database for any applications that have not been used since the given date, and deletes them')" onMouseOut="UnTip()">Inactive Application Check</td>
		<td><input type="button" name="iacheck" value="Perform Check" onClick="doInactiveApp()"/></td>
		<td>
			<span id="ialoader" style="display:none;"><img src="../themes/<?php echo $theme; ?>/images/ajax-loader.gif" height="30px" /></span>
			<span id="iaresult" style="display:none;"></span>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
	<td colspan="3">
		<textarea id="maintresults" rows="6" cols="70">Results will be shown here</textarea>
	</td>
	</tr>
	</table>
	</div>
</div>
</form>
</body>
</html>