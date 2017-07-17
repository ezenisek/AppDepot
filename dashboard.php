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
require_once('includes/ofc/php-ofc-library/open-flash-chart.php');
$userlevel = sessionVerify();
$theme = getNVP('theme') or writeLog("Could not get NVP theme",2);
$adminmessage = getNVP('adminmessage') or writeLog("Could not get NVP adminmessage",2);

if(!$userlevel)
	{
			// Prepare the charts
			//******************Start User Popularity Graph***********************
			  $end = date('Y-m-d h:i:s');
			  $top = 5;
			  $start = date('Y-m-d h:i:s',strtotime($end. '- 1 week'));
			  $query = "select user_id, appdepot_username, (select count(user_id) from app_use a where u.user_id = a.user_id AND timestamp between '$start' and '$end') as used from users u order by used DESC LIMIT $top";
			  $result = mysql_query($query) or dieLog("Could not get data from database for Dashboard User Popularity Graph because ".mysql_error());
			  
			  $title = new title('Top Five Users for the Last Week');
			  $topusers = new open_flash_chart();
			  $topusers->set_bg_colour( '#DDDDDD' );
			  $topusers->set_title($title);
			  
			  $bar = new bar();
			  $bar->set_colour('#882345');
			  $bar->set_on_show(new bar_on_show('grow-up', 2.5, 0));
			  
			  $xvals = array();
			  $yvals = array();
			  $topval = 0;
			  while($row = mysql_fetch_row($result))
			  	{
			  		$xvals[] = $row[1];
			  		$yvals[] = intval($row[2]);
			  		if($row[2] > $topval)
			  			$topval = $row[2];
			  	}
			  	
			  $bar->set_values($yvals);
			  $bar->set_key('Times Active',10);
			  $topusers->add_element($bar);
			  
			  $x_labels = new x_axis_labels();
			  $x_labels->set_labels($xvals);
			  $x_labels->rotate(5);
			  $x = new x_axis();
			  $x->set_labels($x_labels);
			  $x->set_grid_colour('#DDDDDD');
			  $topusers->set_x_axis($x);
			  
			  $y = new y_axis();
			  $y->set_range( 0, $topval, round(($topval/10)) );
			  $y->set_grid_colour('#AAAAAA');
			  $topusers->add_y_axis( $y );
			  
			  $x_legend = new x_legend( 'Users' );
			  $x_legend->set_style( '{font-size: 12px;}' );
			  $topusers->set_x_legend( $x_legend );

			  //************************End User Popularity Graph*************************	
			  
			  //******************Start App Popularity Graph***********************
			  $end = date('Y-m-d h:i:s');
			  $top = 5;
			  $start = date('Y-m-d h:i:s',strtotime($end. '- 1 week'));
			  $query = "select app_id, app_name, (select count(app_id) from app_use u where u.app_id = a.app_id AND timestamp between '$start' and '$end') as used from applications a order by used DESC LIMIT $top";
			  $result = mysql_query($query) or dieLog("Could not get data from database for Dashboard App Popularity Graph because ".mysql_error());
			  
			  $title = new title('Top Five Applications for the Last Week');
			  $topapps = new open_flash_chart();
			  $topapps->set_bg_colour( '#DDDDDD' );
			  $topapps->set_title($title);
			  
			  $bar = new bar();
			  $bar->set_colour('#882345');
			  $bar->set_on_show(new bar_on_show('grow-up', 2.5, 0));
			  
			  $xvals = array();
			  $yvals = array();
			  $topval = 0;
			  while($row = mysql_fetch_row($result))
			  	{
			  		$xvals[] = $row[1];
			  		$yvals[] = intval($row[2]);
			  		if($row[2] > $topval)
			  			$topval = $row[2];
			  	}
			  	
			  $bar->set_values($yvals);
			  $bar->set_key('Times Used',10);
			  $topapps->add_element($bar);
			  
			  $x_labels = new x_axis_labels();
			  $x_labels->set_labels($xvals);
			  $x_labels->rotate(5);
			  $x = new x_axis();
			  $x->set_labels($x_labels);
			  $x->set_grid_colour('#DDDDDD');
			  $topapps->set_x_axis($x);
			  
			  $y = new y_axis();
			  $y->set_range( 0, $topval, round(($topval/10)) );
			  $y->set_grid_colour('#AAAAAA');
			  $topapps->add_y_axis( $y );
			  
			  $x_legend = new x_legend( 'Applications' );
			  $x_legend->set_style( '{font-size: 12px;}' );
			  $topapps->set_x_legend( $x_legend );

			  //************************End User Popularity Graph*************************
			  
			  //******************Start App Depot Activity Graph*********************** 	
			  $end = date('Y-m-d H:i:s');
			  $start = date('Y-m-d 00:i:s',strtotime($end. '-1 week'));
			  $check = $start;
			  $query1 = "select DATE_FORMAT(timestamp,'%Y-%m-%d %H:%i:%s') as aDate, count(*) as aUse from app_use WHERE timestamp > '$start' group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp), HOUR(timestamp)";
			  $query2 = "select DATE_FORMAT(timestamp,'%Y-%m-%d %H:%i:%s') as aDate, count(*) as aUse from logins WHERE result = 1 AND timestamp > '$start' group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp), HOUR(timestamp)"; 
			  $query3 = "select DATE_FORMAT(timestamp,'%Y-%m-%d %H:%i:%s') as aDate, count(*) as aUse from logs WHERE timestamp > '$start' group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp), HOUR(timestamp)"; 
			  
			  $result1 = mysql_query($query1) or dieLog("Could not get data from database for AD Activity Graph (app) because ".mysql_error());
			  $result2 = mysql_query($query2) or dieLog("Could not get data from database for AD Activity Graph (login) because ".mysql_error());
			  $result3 = mysql_query($query3) or dieLog("Could not get data from database for AD Activity Graph (log) because ".mysql_error());
			  
			  $topval = 0;
			  $check = $start;
			  $adates = array();
			  $totdates = 0;
			  $yvalsapps = array();
			  $yvalslogs = array();
			  $yvalslogins = array();
			  $xvals = array();
				
			  while($row = mysql_fetch_row($result1))
			  	{
			  		$recheck = date('H D',strtotime($row[0]));
			  		$adates[$recheck]['appactivity'] = $row[1];
			  	}
			  while($row = mysql_fetch_row($result2))
			  	{
			  		$recheck = date('H D',strtotime($row[0]));
			  		$adates[$recheck]['loginactivity'] = $row[1];
			  	}
			  while($row = mysql_fetch_row($result3))
			  	{
			  		$recheck = date('H D',strtotime($row[0]));
			  		$adates[$recheck]['logactivity'] = $row[1];
			  	}

			  while($check < (date('Y-m-d 00:00:00',strtotime($end.' +1 day'))))
			  	{
			  		$recheck = date('H D',strtotime($check));
			  		$xvals[] = $recheck;
			  		
			  		if(isset($adates[$recheck]['appactivity']))
			  			{
			  				$yvalsapps[] = intval($adates[$recheck]['appactivity']);
			  				if($adates[$recheck]['appactivity'] > $topval)
			  				$topval = $adates[$recheck]['appactivity'];
			  			}
			  		else
			  			$yvalsapps[] = 0;
			  		if(isset($adates[$recheck]['loginactivity']))
			  			{
			  				$yvalslogins[] = intval($adates[$recheck]['loginactivity']);
			  				if($adates[$recheck]['loginactivity'] > $topval)
			  				$topval = $adates[$recheck]['loginactivity'];
			  			}
			  		else
			  			$yvalslogins[] = 0;
			  		if(isset($adates[$recheck]['logactivity']))
			  			{
			  				$yvalslogs[] = intval($adates[$recheck]['logactivity']);
			  				//if($adates[$recheck]['logactivity'] > $topval)
			  				//$topval = $adates[$recheck]['logactivity'];
			  			}
			  		else
			  			$yvalslogs[] = 0;
 	
			  		$check = date('Y-m-d H:i:s',strtotime($check ."+1 hour"));	
			  		$totdates++;
			  	}
			  $steps = 24;
			  
			  $allactivity = new open_flash_chart();
			  $allactivity->set_bg_colour( '#DDDDDD' );
			  $title = new title('App Depot Activity in the Last Week');
			  $allactivity->set_title($title);
			  
			  $d = new dot();
			  $d->colour('#FFFFFF');
			  $d->tooltip('Applications Requested: #val# <br> Date: #x_label#');
			  $appline = new line();
			  $appline->set_colour('#DF7A00');
			  $appline->set_on_show(new line_on_show('mid-slide', 1, 0));
			  $appline->set_width(2);
			  $appline->set_default_dot_style($d); 
			  $appline->set_values($yvalsapps);
			  $appline->set_key('Application Usage',10);
			  $allactivity->add_element($appline);
			  
			  // Removed the log portion of the graph because App Depot
			  // writes a lot of logs, and it scales the graph so you
			  // can even see logins or application use for the most part.
			  // Once OFC supports multiple scaled y-axis this can be re-included.
			  /*
			  $e = new dot();
			  $e->colour('#FFFFFF');
			  $e->tooltip('Logs Written: #val# <br> Date: #x_label#');
			  $logline = new line();
			  $logline->set_colour('#882345');
			  $logline->set_on_show(new line_on_show('mid-slide', 1, 0));
			  $logline->set_width(1);
			  $logline->set_default_dot_style($e); 
			  $logline->set_values($yvalslogs);
			  $logline->set_key('Log Activity',10);
			  $allactivity->add_element($logline);
			  */
			  
			  $f = new dot();
			  $f->colour('#FFFFFF');
			  $f->tooltip('User Logins: #val# <br> Date: #x_label#');
			  $loginline = new line();
			  $loginline->set_colour('#0042FF');
			  $loginline->set_on_show(new line_on_show('mid-slide', 1, 0));
			  $loginline->set_width(2);
			  $loginline->set_default_dot_style($f); 
			  $loginline->set_values($yvalslogins);
			  $loginline->set_key('User Login Activity',10);
			  $allactivity->add_element($loginline);
			  
			  $x_labels = new x_axis_labels();
			  $x_labels->set_labels($xvals);
			  $x_labels->set_steps($steps);
			  $x_labels->rotate(30);
			  $x = new x_axis();
			  $x->set_labels($x_labels);
			  $x->set_grid_colour('#AAAAAA');
			  $allactivity->set_x_axis($x);
			  
			  $y = new y_axis();
			  $y->set_range( 0, $topval, round(($topval/10)) );
			  $y->set_grid_colour('#AAAAAA');
			  $allactivity->add_y_axis( $y );
			  
			  $x_legend = new x_legend( 'Hour and Day' );
			  $x_legend->set_style( '{font-size: 12px;}' );
			  $allactivity->set_x_legend( $x_legend );

			  //************************End App Depot Activity Graph*************************
			  
			  //******************Start Login Comparison Graph***********************
			  
			  $end = date('Y-m-d H:i:s');
			  $start = date('Y-m-d H:i:s',strtotime($end. '-2 weeks'));
			  $check = $start;

			  $query = "select DATE_FORMAT(timestamp,'%Y-%m-%d') as aDate, count(*), result as aUse from logins WHERE timestamp > '$start' group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp), result";
			   
			  $result = mysql_query($query) or dieLog("Could not get data from database for login comparison Graph because ".mysql_error());
			  
			  $topval = 0;
			  $check = $start;
			  $adates = array();
			  $totdates = 0;
			  $xvals = array();
			  $yvals = array();	
				
			  while($row = mysql_fetch_row($result))
			  	{
			  		$adates[$row[0]][$row[2]] = $row[1];
			  	}
			
			  while($check < $end)
			  	{
			  		$pd = date('m-d',strtotime($check));
			  		$xvals[] = $pd;
			  		$thistop = 0;
			  		if(isset($adates[$check][0]))
			  			{
			  				$yvals[$pd]['f'] = intval($adates[$check][0]);	
			  				$thistop += $adates[$check][0];
			  			}
			  		else
			  			$yvals[$pd]['f'] = 0;
			  		
			  		if(isset($adates[$check][1]))
			  			{
			  				$yvals[$pd]['s'] = intval($adates[$check][1]);	
			  				$thistop += $adates[$check][1];
			  			}
			  		else
			  			$yvals[$pd]['s'] = 0;
			  			
			  		if($thistop > $topval)
			  			$topval = $thistop;
			  			
			  		$check = date('Y-m-d',strtotime($check ."+1 day"));
			  		$totdates++;
			  	}
			  $steps = 1;
			  
			  $logincompare = new open_flash_chart();
			  $logincompare->set_bg_colour( '#DDDDDD' );
			  $title = new title('Login Success/Failure in the Last Two Weeks');
			  $logincompare->set_title($title);
			  
			  $bar = new bar_stack();
			  $bar->set_colours(array('#0000FF','#FF0000'));
			  $bar->set_on_show(new line_on_show('grow-up', 1.5, 0));
			  $bar->set_keys(array(new bar_stack_key('#0000FF', 'Successful Logins',10),new bar_stack_key('#FF0000', 'Failed Login Attempts',10)));	
			  $bar->set_tooltip('#val# of #total# attempts<br>#x_label#');	
			
			  foreach($xvals as $date)
			  	{
			  		if(isset($yvals[$date]))
			  			$bar->append_stack(array($yvals[$date]['s'],$yvals[$date]['f']));	
			  	}
			
			  $x_labels = new x_axis_labels();
			  $x_labels->set_labels($xvals);
			  $x_labels->set_steps($steps);
			  $x_labels->rotate(30);
			  $x = new x_axis();
			  $x->set_labels($x_labels);
			  $x->set_grid_colour('#AAAAAA');
			  $logincompare->set_x_axis($x);
			  
			  $y = new y_axis();
			  $y->set_range( 0, $topval, round(($topval/10)) );
			  $y->set_grid_colour('#AAAAAA');
			  $logincompare->add_y_axis( $y );
			  
			  $x_legend = new x_legend( 'Date' );
			  $x_legend->set_style( '{font-size: 12px;}' );
			  $logincompare->set_x_legend( $x_legend );
			
			  $tooltip = new tooltip();
			  $tooltip->set_hover();
			  
			  $logincompare->add_element($bar);
			  $logincompare->set_tooltip($tooltip);

			  //************************End Login Comparison Graph*************************
			  
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="themes/<?php echo $theme; ?>/style.css" />
	<link rel="stylesheet" type="text/css" href="javascript/jquery/css/ui-darkness/jquery-ui-1.7.2.custom.css" />
	<script type="text/javascript" src="includes/ofc/js/json/json2.js"></script>
	<script type="text/javascript" src="javascript/jquery/js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="javascript/jquery/js/jquery-ui-1.7.2.custom.min.js"></script>
	<script type="text/javascript" src="includes/ofc/js/swfobject.js"></script>
	<title>Application Depot</title>
	<script type="text/javascript" >
	<?php if(!$userlevel) { ?>
	// Embed the flash charts, one for each chart
	swfobject.embedSWF(
		  "includes/ofc/open-flash-chart.swf", "topusers",
		  "100%", "100%", "9.0.0", "expressInstall.swf",
		  {"get-data":"get_topusers", "id":"topusers"} );
	
	swfobject.embedSWF(
		  "includes/ofc/open-flash-chart.swf", "topapps",
		  "100%", "100%", "9.0.0", "expressInstall.swf",
		  {"get-data":"get_topapps", "id":"topapps"} );
	
	swfobject.embedSWF(
		  "includes/ofc/open-flash-chart.swf", "allactivity",
		  "100%", "100%", "9.0.0", "expressInstall.swf",
		  {"get-data":"get_allactivity", "id":"allactivity"} );

	swfobject.embedSWF(
		  "includes/ofc/open-flash-chart.swf", "logincompare",
		  "100%", "100%", "9.0.0", "expressInstall.swf",
		  {"get-data":"get_logincompare", "id":"logincompare"} );
	
	function ofc_ready()
	{
		 //alert('ofc_ready');
	}
	  
	function get_topapps()
	{
		return JSON.stringify(topapps);
	}
	 
	function get_topusers()
	{
		return JSON.stringify(topusers);
	}
	
	function get_allactivity()
	{
		return JSON.stringify(allactivity);
	}
	 
	function get_logincompare()
	{
		return JSON.stringify(logincompare);
	}
	 
	var topapps = <?php echo $topapps->toPrettyString(); ?>;
	var topusers = <?php echo $topusers->toPrettyString(); ?>;
	var allactivity = <?php echo $allactivity->toPrettyString(); ?>;
	var logincompare = <?php echo $logincompare->toPrettyString(); ?>;
	
	$(function(){
	$("#resizeusers").resizable();
	});
	$(function(){
	$("#resizeapps").resizable();
	});
	$(function(){
	$("#resizeactivity").resizable();
	});
	$(function(){
	$("#resizelogincompare").resizable();
	});
	<?php } ?>
	</script>
</head>
<body>
<div class="dashboardbody">
	<div class="dashboardoverlay">
	<br />
	<?php 
		if($userlevel)
		{
			echo "<div class='adminmessage'>$adminmessage</div>";
		}
		else
		{
			// Show admin dashboard stuff	
			// Get number of users
			$query = "SELECT COUNT(user_id) FROM users";
			$result = mysql_query($query) or dieLog("Could not get user count for dashboard because ".mysql_error());
			$row = mysql_fetch_row($result);
			$usercount = $row[0];
			
			// Get number of applications
			$query = "SELECT COUNT(app_id) FROM applications";
			$result = mysql_query($query) or dieLog("Could not get app count for dashboard because ".mysql_error());
			$row = mysql_fetch_row($result);
			$appcount = $row[0];
			
			// Get number of failed logins for the last week
			$checktime = date('Y-m-d H:i:s',strtotime('-1 week'));
			$query = "SELECT (SELECT COUNT(login_id) FROM logins WHERE result = 0 AND timestamp > '$checktime'), (SELECT COUNT(login_id) FROM logins WHERE result = 1 AND timestamp > '$checktime')";
			$result = mysql_query($query) or dieLog("Could not get failed login count for dashboard because ".mysql_error());
			$row = mysql_fetch_row($result);
			$failedlogincount = $row[0];
			$logincount = $row[1];
			
			// Get Logged in Users
			$loggedin = array();
			$users = getUserList();
			$query = "SELECT appdepot_username, user_id FROM users";
			$result = mysql_query($query) or dieLog("Could not get user information for dashboard because ".mysql_error());
			while($row = mysql_fetch_row($result))
				{
					if(userLoggedIn($row[0]))
						{
							$loggedin[$row[1]]['username'] = $row[0];
							$loggedin[$row[1]]['fullname'] = $users[$row[1]]['fullname'];
							$loggedin[$row[1]]['email'] = $users[$row[1]]['email'];
						}
				}
			?>
			<center>
			<table width="90%">
				<tr class="listheader">
					<td colspan="2">General App Depot Statistics</td>
				</tr>
				<tr>
			        <td colspan="2">
					<table class="smalltext">
						<tr>
							<td class="">Total Number of Users:</td>
							<td class=""><?php echo $usercount; ?></td>
							<td>|</td>
							<td class="">Total Number of Applications:</td>
							<td class=""><?php echo $appcount; ?></td>
						    <td>|</td>
							<td class="">Successful Logins Last Week:</td>
							<td class=""><?php echo $logincount; ?></td>
							<td>|</td>
							<td class="">Failed Login attempts Last Week:</td>
							<td class=""><?php echo $failedlogincount; ?></td>
						</tr>
					</table>
					</td>				
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr class="listheader">
					<td colspan="2">Activity Monitors</td>
				</tr>
			<tr>
				<td width="50%">
				<div id="resizeactivity" style="width:100%; height:150px; padding: 5px; background-color:#EEE;">
				<div id="allactivity"></div>
				</div>
				</td>
				<td width="50%">
				<div id="resizelogincompare" style="width:100%; height:150px; padding: 5px; background-color:#EEE;">
				<div id="logincompare"></div>
				</div>
				</td>
			</tr>
			<tr>
				<td width="50%">
				<div id="resizeusers" style="width:100%; height:150px; padding: 5px; background-color:#EEE;">
				<div id="topusers"></div>
				</div>
				</td>
				<td width="50%">
				<div id="resizeapps" style="width:100%; height:150px; padding: 5px; background-color:#EEE;">
				<div id="topapps"></div>
				</div>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td>
				<table class="listtable">
					<tr class="listheader">
						<td colspan="3">Users Currently Logged In</td>
					</tr>
					<tr>
						<td class="listtableheader">Username</td>
						<td class="listtableheader">Full Name</td>
						<td class="listtableheader">E Mail</td>
					</tr>
					<?php
					foreach($loggedin as $user)
						{
							echo '<tr>';
							echo '<td>'.$user['username'].'</td>';
							echo '<td>'.$user['fullname'].'</td>';
							echo '<td><a href="mailto:'.$user['email'].'">'.$user['email'].'</a></td>';
							echo '</tr>';				
						}
					?>
				</table>
				</td>
			</tr>
			</table>
			</center>
			<?php
		}
	?>
	</div>
</div>
</body>
</html>