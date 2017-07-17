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
     * This file contains all the startup functions for each page of App Depot
     * *****************************************************************/
     
    require_once('settings.php');
    require_once('functions.php');
    dbConnect($dbhost,$database,$username,$password);
	require_once("sessions.php");
	$sess = new SessionManager();
	session_start();
?>
