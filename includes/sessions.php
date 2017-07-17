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
     * This file re-writes PHP's session handling to send session information
     * to the database rather than to the server's hard drive. It must be
     * included in any App Depot file that uses sessions.
     * *****************************************************************/
     
 class SessionManager 
 {
   var $life_time;

   function SessionManager() 
   {
      // Read the maxlifetime setting from PHP
      $this->life_time = (getNVP('sessiontimeout')*60);

      // Register this object as the session handler
      session_set_save_handler( 
        array( &$this, "open" ), 
        array( &$this, "close" ),
        array( &$this, "read" ),
        array( &$this, "write"),
        array( &$this, "destroy"),
        array( &$this, "gc" )
      );
   }

   function open( $save_path, $session_name ) 
   {
      global $sess_save_path;
      $sess_save_path = $save_path;
      // Don't need to do anything. Just return TRUE.
      return true;
   }

   function close() 
   {
   	  $this->gc();
      return true;
   }

   function read( $id ) 
   {
      // Set empty result
      $data = '';

      // Fetch session data from the selected database
      $time = time();
      $newid = mysql_real_escape_string($id);
      $query = "SELECT session_data FROM sessions WHERE session_id = '$newid' " .
      		"AND expires > $time";

      $result = mysql_query($query);                           
      if(mysql_num_rows($result));
		{
        $row = mysql_fetch_assoc($result);
        $data = $row['session_data'];
      	}

      return $data;
   }

   function write( $id, $data ) 
   {
      // Build query                
      $time = time() + $this->life_time;
      $newid = mysql_real_escape_string($id);
      $newdata = mysql_real_escape_string($data);
	  $query = "REPLACE sessions (session_id, session_data, expires) VALUES" .
	           " ('$newid','$newdata','$time')";
      mysql_query($query);
      return TRUE;
   }

   function destroy( $id ) 
   {
      // Build query
      $newid = mysql_real_escape_string($id);
      $query = "DELETE FROM sessions WHERE session_id = '$newid'";
      mysql_query($query);
      $this->gc();
      return TRUE;
   }

   function gc() 
   {
      // Garbage Collection                   
      // Build DELETE query.  Delete all records who have passed the expiration time
      $query = "DELTE FROM sessions WHERE expires < UNIX_TIMESTAMP()";
      mysql_query($query);
      // Always return TRUE
      return true;
   }
}
?>
