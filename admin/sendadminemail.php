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
require_once('../includes/phpmailer/class.phpmailer.php');
require_once('../includes/phpmailer/language/phpmailer.lang-en.php');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: text/xml; charset=utf-8");
function endProcess($result,$content)
	{
		 $xml = "<?xml version=\"1.0\" ?><response><result>$result</result>";
		 $xml .= "<content>$content</content></response>";		
		 echo $xml;
		 if($result == 'error')
		 	$severity = 1;
		 else
		 	$severity = 0;
         writeLog($content,$severity);
		 die();
	}
if(!isset($_POST['message']) || !isset($_POST['subject']) || !isset($_POST['apps']))
	{
		$error = "The request could not be completed, it is malformed.";
		endProcess('error',$error);
	}
$message = urldecode($_POST['message']);
$subject = $_POST['subject'];
$apps = $_POST['apps'];
$sendto = array();
$userlist = getUserList();

$apps = explode(',',$apps);
// Check if we're emailing everyone.  If not, then we need to be picky
if(in_array('all',$apps))
	{
		foreach($userlist as $user)
			{
				$sendto[] = $user['email'];
			}
	}
else
	{
		foreach($apps as $app)
			{
				$query = "SELECT user_id FROM permissions WHERE app_id = '$app'";
				$result = mysql_query($query) or endProcess('error','Could not generate user list from the database');
				while($row = mysql_fetch_row($result))
					{
						$sendto[] = $userlist[$row[0]]['email'];	
					}	
			}	
	}
$sendto = array_unique($sendto);

/* Testing */
$sendto = '';
$sendto = array('ezenisek@nmsu.edu','ezenisek@research.nmsu.edu');
/* End testing */

$footer = "";

$admin = getNVP('adminemail');

$mail = new PHPMailer();
$mail->IsHTML(true);
$mail->From = $admin;
$mail->FromName = "Application Depot Administration";
$mail->Subject = $subject;
$mail->Body = $message.$footer;
//Set up plain text email for those without HTML support
  $plaintext = str_replace('<p>',"\n",$message.$footer);
  $plaintext = str_replace('<br>',"\n",$plaintext);
  $plaintext = str_replace('<br />',"\n",strip_tags($plaintext));
  $mail->AltBody = $plaintext;

// We need a main address to send to, so that's what this is
$mail->AddAddress($admin);
 
// And since we don't want everyone knowing everyone else who got the 
// message, we use BCC.
      
foreach($sendto as $x)
   {
        if(trim($x) != 'N/A')
		$mail->AddBCC($x);
   }

if(!$mail->Send())
	{
		endProcess('error',"Sending mail failed for an unknown reason.");
	}

$count = count($sendto);
endProcess('success',"Mail Sent to $count recipients");
?>
