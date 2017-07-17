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
    
    /********************************************************************
     * Application Depot Logging File
     * This file takes arguments in the url and writes a log based on them
     * Results are returned as xml
     * *****************************************************************/
     
     // Required arguments:
     // entry - url encoded log entry
     // application - url encoded application name
     
     // Optional arguments:
     // severity - 0 = notice, 1 = minor, 2 = major, 3 = severe (die) 
    require_once('includes/startup.php');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
	header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
	header("Cache-Control: no-cache, must-revalidate" ); 
	header("Pragma: no-cache" );
	header("Content-Type: text/xml; charset=utf-8");
	 $error = 0;
	 
	 // Get required arguments
	 if(!isset($_REQUEST['entry']) || !isset($_REQUEST['application']))
	 	{
	 	    $error = "Log program called without valid arguments";
	 		writeLog($error);
	 	}
	 else
	 	{
	 		$entry = urldecode($_REQUEST['entry']);
	 		$application = urldecode($_REQUEST['application']);
	 		if(isset($_REQUEST['severity']))
	 			$severity = $_REQUEST['severity'];
	 		else
	 			$severity = 0;
	 		if(!writeLog($entry,$severity,$application))
	 			{
	 				$error = "Could not write log";
	 				writeLog($error);
	 			}
	 	}
	 if($error)
	 	{
	 		$result = 'error';
	 		$content = $error;
	 	}
	 else
	 	{
	 		$result = 'success';
	 		$content = urlencode($entry);
	 	}
	 	
	 $xml = "<?xml version=\"1.0\" ?><response><result>$result</result>";
	 $xml .= "<content>$content</content></response>";		
	 echo $xml; 		
?>
