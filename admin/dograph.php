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
require_once('../includes/ofc/php-ofc-library/open-flash-chart.php');

if(!isset($_REQUEST['choice']))
  {
    $message = "The report generator could not perfrom the requested action.";
    include("../includes/error.php");
    exit();
  }

if(!isset($_REQUEST['width']))
	$gwidth = 900;
else
	$gwidth = $_REQUEST['gwidth'];
	
if(!isset($_REQUEST['height']))
	$gheight = 400;
else
	$gheight = $_REQUEST['gheight'];
 
//$username = $_SESSION['username']; 
$choice = $_REQUEST['choice'];

// Set up the chart
$chart = new open_flash_chart();
$chart->set_bg_colour( '#DDDDDD' );

switch($choice) {
  //********************************************
  //    BEGIN SWITCH STATEMENT FOR GRAPH CHIOCE
  //   	BEGIN GRAPH REPORTS
  //********************************************
  
  //******************Start App Depot Activity Graph***********************
  case 'adactivity':
  $start = $_POST['adactivitystart'];
  $end = $_POST['adactivityend'];
  $listby = $_POST['adactivitylistby'];
  if($listby == 'day')
  	{
  		$query1 = "select DATE_FORMAT(timestamp,'%Y-%m-%d') as aDate, count(*) as aUse from app_use group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp)";
  		$query2 = "select DATE_FORMAT(timestamp,'%Y-%m-%d') as aDate, count(*) as aUse from logins WHERE result = 1 group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp)"; 
  		$query3 = "select DATE_FORMAT(timestamp,'%Y-%m-%d') as aDate, count(*) as aUse from logs group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp)"; 
  		
  	}
  if($listby == 'hour')
  	{
  		$query1 = "select DATE_FORMAT(timestamp,'%Y-%m-%d %H:%00:%00') as aDate, count(*) as aUse from app_use group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp), HOUR(timestamp)";
  		$query2 = "select DATE_FORMAT(timestamp,'%Y-%m-%d %H:%00:%00') as aDate, count(*) as aUse from logins WHERE result = 1 group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp), HOUR(timestamp)"; 
  		$query3 = "select DATE_FORMAT(timestamp,'%Y-%m-%d %H:%00:%00') as aDate, count(*) as aUse from logs group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp), HOUR(timestamp)"; 
  	}
  if($listby == 'month')
  	{
  		$query1 = "select DATE_FORMAT(timestamp,'%Y-%m-01') as aDate, count(*) as aUse from app_use group by YEAR(timestamp), MONTH(timestamp)";
  		$query2 = "select DATE_FORMAT(timestamp,'%Y-%m-01') as aDate, count(*) as aUse from logins WHERE result = 1 group by YEAR(timestamp), MONTH(timestamp)"; 
  		$query3 = "select DATE_FORMAT(timestamp,'%Y-%m-01') as aDate, count(*) as aUse from logs group by YEAR(timestamp), MONTH(timestamp)"; 
  	}
  	   
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
  		$adates[$row[0]]['appactivity'] = $row[1];
  	}
  while($row = mysql_fetch_row($result2))
  	{
  		$adates[$row[0]]['loginactivity'] = $row[1];
  	}
  while($row = mysql_fetch_row($result3))
  	{
  		$adates[$row[0]]['logactivity'] = $row[1];
  	}


  while($check < $end)
  	{
  		$xvals[] = $check;
  		if(isset($adates[$check]['appactivity']))
  			{
  				$yvalsapps[] = intval($adates[$check]['appactivity']);
  				if($adates[$check]['appactivity'] > $topval)
  				$topval = $adates[$check]['appactivity'];
  			}
  		else
  			$yvalsapps[] = 0;
  		if(isset($adates[$check]['loginactivity']))
  			{
  				$yvalslogins[] = intval($adates[$check]['loginactivity']);
  				if($adates[$check]['loginactivity'] > $topval)
  				$topval = $adates[$check]['loginactivity'];
  			}
  		else
  			$yvalslogins[] = 0;
  		if(isset($adates[$check]['logactivity']))
  			{
  				$yvalslogs[] = intval($adates[$check]['logactivity']);
  				if($adates[$check]['logactivity'] > $topval)
  				$topval = $adates[$check]['logactivity'];
  			}
  		else
  			$yvalslogs[] = 0;
  			
  		if($listby == 'day')
  			$check = date('Y-m-d',strtotime($check ."+1 day"));
  		if($listby == 'hour')
  			$check = date('Y-m-d H:00:00',strtotime($check ."+1 hour"));
  		if($listby == 'month')
  			$check = date('Y-m-01',strtotime($check ."+1 month"));
  		$totdates++;
  	}
  $steps = ceil($totdates / 20);
  
  $title = new title('App Depot Activity by '.ucwords($listby).' Between '.$start.' and '.$end);
  $chart->set_title($title);
  
  $d = new dot();
  $d->colour('#FFFFFF');
  $d->tooltip('Applications Requested: #val# <br> Date: #x_label#'); 
  $appline = new line();
  $appline->set_colour('#DF7A00');
  $appline->set_on_show(new line_on_show('mid-slide', 1, 0));
  $appline->set_width(2);
  $appline->set_default_dot_style($d); 
  $appline->set_values($yvalsapps);
  $appline->set_key('Application Usage Activity',12);
  $chart->add_element($appline);
  
  $e = new dot();
  $e->colour('#FFFFFF');
  $e->tooltip('Logs written: #val# <br> Date: #x_label#');
  $logline = new line();
  $logline->set_colour('#882345');
  $logline->set_on_show(new line_on_show('mid-slide', 1, 0));
  $logline->set_width(1);
  $logline->set_default_dot_style($e); 
  $logline->set_values($yvalslogs);
  $logline->set_key('Log Activity',12);
  $chart->add_element($logline);
  
  $f = new dot();
  $f->colour('#FFFFFF');
  $f->tooltip('User Logins: #val# <br> Date: #x_label#');
  $loginline = new line();
  $loginline->set_colour('#0042FF');
  $loginline->set_on_show(new line_on_show('mid-slide', 1, 0));
  $loginline->set_width(2);
  $loginline->set_default_dot_style($f); 
  $loginline->set_values($yvalslogins);
  $loginline->set_key('User Login Activity',12);
  $chart->add_element($loginline);
  
  $x_labels = new x_axis_labels();
  $x_labels->set_labels($xvals);
  $x_labels->set_steps($steps);
  $x_labels->rotate(30);
  $x = new x_axis();
  $x->set_labels($x_labels);
  $x->set_grid_colour('#AAAAAA');
  $chart->set_x_axis($x);
  
  $y = new y_axis();
  $y->set_range( 0, $topval, round(($topval/10)) );
  $y->set_grid_colour('#AAAAAA');
  $chart->add_y_axis( $y );
  
  $x_legend = new x_legend( 'Date' );
  $x_legend->set_style( '{font-size: 12px;}' );
  $chart->set_x_legend( $x_legend );

  writeLog("App Depot Activity Graph Run by $username"); 
  break;
  //************************End App Depot Activity Graph*************************
  
  //******************Start Login Comparison Graph***********************
  case 'logincompare':
  $start = $_POST['logincomparestart'];
  $end = $_POST['logincompareend'];
  $listby = $_POST['logincomparelistby'];
  
  if($listby == 'day')
  	$query = "select DATE_FORMAT(timestamp,'%Y-%m-%d') as aDate, count(*), result as aUse from logins WHERE timestamp > '$start' group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp), result";
  if($listby == 'hour')
  	$query = "select DATE_FORMAT(timestamp,'%Y-%m-%d %H:%00:%00') as aDate, count(*), result as aUse from logins timestamp > '$start' group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp), HOUR(timestamp), result";
  if($listby == 'month')
  	$query = "select DATE_FORMAT(timestamp,'%Y-%m-01') as aDate, count(*), result as aUse from logins WHERE timestamp > '$start' group by YEAR(timestamp), MONTH(timestamp), result";
   
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
  		$xvals[] = $check;
  		$thistop = 0;
  		if(isset($adates[$check][0]))
  			{
  				$yvals[$check]['f'] = intval($adates[$check][0]);	
  				$thistop += $adates[$check][0];
  			}
  		else
  			$yvals[$check]['f'] = 0;
  		
  		if(isset($adates[$check][1]))
  			{
  				$yvals[$check]['s'] = intval($adates[$check][1]);	
  				$thistop += $adates[$check][1];
  			}
  		else
  			$yvals[$check]['s'] = 0;
  			
  		if($thistop > $topval)
  			$topval = $thistop;
  			
  		if($listby == 'day')
  			$check = date('Y-m-d',strtotime($check ."+1 day"));
  		if($listby == 'hour')
  			$check = date('Y-m-d H:00:00',strtotime($check ."+1 hour"));
  		if($listby == 'month')
  			$check = date('Y-m-01',strtotime($check ."+1 month"));
  		$totdates++;
  	}
  $steps = ceil($totdates / 20);
  
  $title = new title('Login Success/Failure by '.ucwords($listby));
  $chart->set_title($title);
  
  $bar = new bar_stack();
  $bar->set_colours(array('#0000FF','#FF0000'));
  $bar->set_on_show(new line_on_show('grow-up', 1.5, 0));
  $bar->set_keys(array(new bar_stack_key('#0000FF', 'Successful Logins',12),new bar_stack_key('#FF0000', 'Failed Login Attempts',12)));	
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
  $chart->set_x_axis($x);
  
  $y = new y_axis();
  $y->set_range( 0, $topval, round(($topval/10)) );
  $y->set_grid_colour('#AAAAAA');
  $chart->add_y_axis( $y );
  
  $x_legend = new x_legend( 'Date' );
  $x_legend->set_style( '{font-size: 12px;}' );
  $chart->set_x_legend( $x_legend );

  $tooltip = new tooltip();
  $tooltip->set_hover();
  
  $chart->add_element($bar);
  $chart->set_tooltip($tooltip);
  
  writeLog("Login Comparison Graph Run by $username"); 
  break;  
  //************************End Login Comparison Graph*************************
  
  //******************Start Log Growth Graph***********************
  case 'loggrowth':

  $query = "select DATE_FORMAT(timestamp,'%Y-%m-%d') as aDate, count(*) as aUse from logs group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp)"; 
  $query2 = "select MAX(timestamp) as high, MIN(timestamp) as low from logs";
  	  
  $result = mysql_query($query) or dieLog("Could not get data from database for log growth Graph because ".mysql_error());
  $result2 = mysql_query($query2) or dieLog("Could not get data from database for log growth (2) Graph because ".mysql_error());
  
  $topval = 0;
  $adates = array();
  $totdates = 0;
  $yvals = array();
  $xvals = array();
	
  while($row = mysql_fetch_row($result))
  	{
  		$adates[$row[0]] = $row[1];
  	}
  $row = mysql_fetch_row($result2);
  $start = date('Y-m-d',strtotime($row[1]));
  $end = date('Y-m-d',strtotime($row[0]));
  $check = $start;

  while($check < $end)
  	{
  		$xvals[] = $check;
  		if(isset($adates[$check]))
  			{
  				$yvals[] = intval($adates[$check])+$topval;
  				$topval += intval($adates[$check]);
  			}
  		else
  			$yvals[] = $topval;
  			
  		$check = date('Y-m-d',strtotime($check ."+1 day"));	
  		$totdates++;
  	}
  $steps = ceil($totdates / 20);
  
  $title = new title('App Depot Log Growth');
  $chart->set_title($title);
  
  $d = new dot();
  $d->colour('#FFFFFF');
  $d->tooltip('Total Entries: #val# <br> Date: #x_label#');
  
  $logline = new area();
  $logline->set_colour('#882345');
  $logline->set_on_show(new line_on_show('mid-slide', 1, 0));
  $logline->set_width(1);
  $logline->set_fill_colour('#882345');
  $logline->set_fill_alpha( 0.7 );
  $logline->set_default_dot_style($d); 
  $logline->set_values($yvals);
  $logline->set_key('Log Activity',12);
  $chart->add_element($logline);
  
  $x_labels = new x_axis_labels();
  $x_labels->set_labels($xvals);
  $x_labels->set_steps($steps);
  $x_labels->rotate(30);
  $x = new x_axis();
  $x->set_labels($x_labels);
  $x->set_grid_colour('#AAAAAA');
  $chart->set_x_axis($x);
  
  $y = new y_axis();
  $y->set_range( 0, $topval, round(($topval/10)) );
  $y->set_grid_colour('#AAAAAA');
  $chart->add_y_axis( $y );
  
  $x_legend = new x_legend( 'Date' );
  $x_legend->set_style( '{font-size: 12px;}' );
  $chart->set_x_legend( $x_legend );

  writeLog("Log Growth Graph Run by $username"); 
  break;
  //************************End Log Growth Graph*************************
  
  //******************Start Specific Application Activity Graph***********************
  case 'specappactivity':
  $start = $_POST['specappactivitystart'];
  $end = $_POST['specappactivityend'];
  $listby = $_POST['specappactivitylistby'];
  $app = $_POST['specappactivityapp'];
  if($listby == 'day')
  	$query = "select DATE_FORMAT(timestamp,'%Y-%m-%d') as aDate, count(*) as aUse from app_use WHERE app_id = '$app' group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp)";
  if($listby == 'hour')
  	$query = "select DATE_FORMAT(timestamp,'%Y-%m-%d %H:%00:%00') as aDate, count(*) as aUse from app_use WHERE app_id = '$app' group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp), HOUR(timestamp)";
  if($listby == 'month')
  	$query = "select DATE_FORMAT(timestamp,'%Y-%m-01') as aDate, count(*) as aUse from app_use WHERE app_id = '$app' group by YEAR(timestamp), MONTH(timestamp)";
   
  $result = mysql_query($query) or dieLog("Could not get data from database for Spec App Activity Graph because ".mysql_error());
  
  $topval = 0;
  $check = $start;
  $adates = array();
  $totdates = 0;
  $xvals = array();
  $yvals = array();
	
  while($row = mysql_fetch_row($result))
  	{
  		$adates[$row[0]] = $row[1];
  	}

  while($check < $end)
  	{
  		$xvals[] = $check;
  		if(isset($adates[$check]))
  			{
  				$yvals[] = intval($adates[$check]);
  				if($adates[$check] > $topval)
  				$topval = $adates[$check];
  			}
  		else
  			$yvals[] = 0;
  		if($listby == 'day')
  			$check = date('Y-m-d',strtotime($check ."+1 day"));
  		if($listby == 'hour')
  			$check = date('Y-m-d H:00:00',strtotime($check ."+1 hour"));
  		if($listby == 'month')
  			$check = date('Y-m-01',strtotime($check ."+1 month"));
  		$totdates++;
  	}
  $steps = ceil($totdates / 20);
  
  $title = new title(getAppname($app).' Activity by '.ucwords($listby));
  $chart->set_title($title);
  
  $d = new dot();
  $d->colour('#FFFFFF');
  $d->tooltip('Activity: #val# <br> Date: #x_label#');
  $area = new area();
  $area->set_colour('#000000');
  $area->set_fill_colour('#882345');
  $area->set_fill_alpha( 0.7 );
  $area->set_on_show(new line_on_show('mid-slide', 1, 0));
  $area->set_width(2);
  $area->set_default_dot_style($d);
  
  $area->set_values($yvals);
  $area->set_key(getAppname($app).' Activity Between '.$start.' and '.$end,12);
  $chart->add_element($area);
  
  $x_labels = new x_axis_labels();
  $x_labels->set_labels($xvals);
  $x_labels->set_steps($steps);
  $x_labels->rotate(30);
  $x = new x_axis();
  $x->set_labels($x_labels);
  $x->set_grid_colour('#AAAAAA');
  $chart->set_x_axis($x);
  
  $y = new y_axis();
  $y->set_range( 0, $topval, round(($topval/10)) );
  $y->set_grid_colour('#AAAAAA');
  $chart->add_y_axis( $y );
  
  $x_legend = new x_legend( 'Date' );
  $x_legend->set_style( '{font-size: 12px;}' );
  $chart->set_x_legend( $x_legend );

  writeLog(getAppname($app)." Activity Graph Run by $username"); 
  break;  
  //************************End Specific Application Activity Graph*************************
  
  //******************Start Specific User Activity Graph***********************
  case 'specuseractivity':
  $start = $_POST['specuseractivitystart'];
  $end = $_POST['specuseractivityend'];
  $listby = $_POST['specuseractivitylistby'];
  $user = $_POST['specuseractivityuser'];
  if($listby == 'day')
  	$query = "select DATE_FORMAT(timestamp,'%Y-%m-%d') as aDate, count(*) as aUse from app_use WHERE user_id = '$user' group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp)";
  if($listby == 'hour')
  	$query = "select DATE_FORMAT(timestamp,'%Y-%m-%d %H:%00:%00') as aDate, count(*) as aUse from app_use WHERE app_id = '$user' group by YEAR(timestamp), MONTH(timestamp), DAY(timestamp), HOUR(timestamp)";
  if($listby == 'month')
  	$query = "select DATE_FORMAT(timestamp,'%Y-%m-01') as aDate, count(*) as aUse from app_use WHERE app_id = '$user' group by YEAR(timestamp), MONTH(timestamp)";
   
  $result = mysql_query($query) or dieLog("Could not get data from database for Spec User Activity Graph because ".mysql_error());
  
  $topval = 0;
  $check = $start;
  $adates = array();
  $totdates = 0;
  $xvals = array();
  $yvals = array();
	
  while($row = mysql_fetch_row($result))
  	{
  		$adates[$row[0]] = $row[1];
  	}

  while($check < $end)
  	{
  		$xvals[] = $check;
  		if(isset($adates[$check]))
  			{
  				$yvals[] = intval($adates[$check]);
  				if($adates[$check] > $topval)
  				$topval = $adates[$check];
  			}
  		else
  			$yvals[] = 0;
  		if($listby == 'day')
  			$check = date('Y-m-d',strtotime($check ."+1 day"));
  		if($listby == 'hour')
  			$check = date('Y-m-d H:00:00',strtotime($check ."+1 hour"));
  		if($listby == 'month')
  			$check = date('Y-m-01',strtotime($check ."+1 month"));
  		$totdates++;
  	}
  $steps = ceil($totdates / 20);
  
  $title = new title(getUsername($user).' Activity by '.ucwords($listby));
  $chart->set_title($title);
  
  $d = new dot();
  $d->colour('#FFFFFF');
  $d->tooltip('Activity: #val# <br> Date: #x_label#');
  $area = new area();
  $area->set_colour('#000000');
  $area->set_fill_colour('#882345');
  $area->set_fill_alpha( 0.7 );
  $area->set_on_show(new line_on_show('mid-slide', 1, 0));
  $area->set_width(2);
  $area->set_default_dot_style($d);
  
  $area->set_values($yvals);
  $area->set_key(getUsername($user).' Activity Between '.$start.' and '.$end,12);
  $chart->add_element($area);
  
  $x_labels = new x_axis_labels();
  $x_labels->set_labels($xvals);
  $x_labels->set_steps($steps);
  $x_labels->rotate(30);
  $x = new x_axis();
  $x->set_labels($x_labels);
  $x->set_grid_colour('#AAAAAA');
  $chart->set_x_axis($x);
  
  $y = new y_axis();
  $y->set_range( 0, $topval, round(($topval/10)) );
  $y->set_grid_colour('#AAAAAA');
  $chart->add_y_axis( $y );
  
  $x_legend = new x_legend( 'Date' );
  $x_legend->set_style( '{font-size: 12px;}' );
  $chart->set_x_legend( $x_legend );

  writeLog(getUsername($user)." Activity Graph Run by $username"); 
  break;  
  //************************End Specific User Activity Graph*************************
  
  //******************Start App Popularity Graph***********************
  case 'apppopularity':
  $top = $_POST['apppopularitytop'];
  $start = $_POST['apppopularitystart'];
  $end = $_POST['apppopularityend'];
  $query = "select app_id, app_name, (select count(app_id) from app_use u where u.app_id = a.app_id AND timestamp between '$start' and '$end') as used from applications a order by used DESC LIMIT $top";
  $result = mysql_query($query) or dieLog("Could not get data from database for App Popularity Report because ".mysql_error());
  
  $title = new title('Application Popularity');
  $chart->set_title($title);
  
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
  $bar->set_key('Times Used Between '.$start.' and '.$end,12);
  $chart->add_element($bar);
  
  $x_labels = new x_axis_labels();
  $x_labels->set_labels($xvals);
  $x_labels->rotate(30);
  $x = new x_axis();
  $x->set_labels($x_labels);
  $x->set_grid_colour('#DDDDDD');
  $chart->set_x_axis($x);
  
  $y = new y_axis();
  $y->set_range( 0, $topval, round(($topval/10)) );
  $y->set_grid_colour('#AAAAAA');
  $chart->add_y_axis( $y );
  
  $x_legend = new x_legend( 'Applications' );
  $x_legend->set_style( '{font-size: 12px;}' );
  $chart->set_x_legend( $x_legend );

  writeLog("App Popularity Graph Run by $username"); 
  break;  
  //************************End App Popularity Graph*************************
  
  //******************Start User Popularity Graph***********************
  case 'userpopularity':
  $top = $_POST['userpopularitytop'];
  $start = $_POST['userpopularitystart'];
  $end = $_POST['userpopularityend'];
  $query = "select user_id, appdepot_username, (select count(user_id) from app_use a where u.user_id = a.user_id AND timestamp between '$start' and '$end') as used from users u order by used DESC LIMIT $top";
  $result = mysql_query($query) or dieLog("Could not get data from database for User Popularity Report because ".mysql_error());
  
  $title = new title('User Activity');
  $chart->set_title($title);
  
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
  $bar->set_key('Times Active Between '.$start.' and '.$end,12);
  $chart->add_element($bar);
  
  $x_labels = new x_axis_labels();
  $x_labels->set_labels($xvals);
  $x_labels->rotate(30);
  $x = new x_axis();
  $x->set_labels($x_labels);
  $x->set_grid_colour('#DDDDDD');
  $chart->set_x_axis($x);
  
  $y = new y_axis();
  $y->set_range( 0, $topval, round(($topval/10)) );
  $y->set_grid_colour('#AAAAAA');
  $chart->add_y_axis( $y );
  
  $x_legend = new x_legend( 'Users' );
  $x_legend->set_style( '{font-size: 12px;}' );
  $chart->set_x_legend( $x_legend );

  writeLog("User Popularity Graph Run by $username"); 
  break;  
  //************************End User Popularity Graph*************************
  
  //******************************************
  // 	END SWITCH STATEMENT FOR GRAPH CHOICE
  //******************************************	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>App Depot Graph</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../javascript/jquery/css/ui-darkness/jquery-ui-1.7.2.custom.css" />
<script type="text/javascript" src="../includes/ofc/js/json/json2.js"></script>
<script type="text/javascript" src="../javascript/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../javascript/jquery/js/jquery-ui-1.7.2.custom.min.js"></script>
<script type="text/javascript" src="../includes/ofc/js/swfobject.js"></script>
<script type="text/javascript">
swfobject.embedSWF("../includes/ofc/open-flash-chart.swf", "AD_Graph", "100%", "100%", "9.0.0");

$(function(){
$("#resize").resizable();
});

function ofc_ready()
{
    //alert('ofc_ready');
}

function open_flash_chart_data()
{
    return JSON.stringify(data);
}

function findSWF(movieName) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[movieName];
  } else {
    return document[movieName];
  }
}
    
var data = <?php echo $chart->toPrettyString(); ?>;

function doConvert() 
{ 
    var imageData = document.getElementById('AD_Graph').get_img_binary();
    document.getElementById('image_data').value = imageData;
    document.getpic.submit();
}

</script>
</head>
<body>
<center>
<div id="resize" style="width:<?php echo $gwidth; ?>px; height:<?php echo $gheight; ?>px; padding: 10px; background-color:#EEE;">
<div id="AD_Graph"></div>
</div>
<br />
<form name="getpic" action="graphimage.php?do=show" method="post"> 
<input type="hidden" name="image_data" id="image_data" />
<input type="button" name="print" value="Download Graph as Image" onclick="return doConvert();" />
</form>
</body>
</center>
</html>