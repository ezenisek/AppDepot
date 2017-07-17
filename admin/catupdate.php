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
	
// Make sure our do is set so we know what we're doing
if(!isset($_POST['do']))
	{
		$error = "The request could not be completed, it is malformed.";
		endProcess('error',$error);
	}
$do = $_POST['do'];

if($do == 'add' || $do == 'edit')
	{
		$vars = array(
			'name' => '',
            'description' => '',
            'parent_id' => '',
            'sortorder' => '' );
        $keylist = '';
        $vallist = '';
        $editlist = '';
		foreach($vars as $key => $var)
			{
				if(!isset($_POST[$key]))
					{
						$error = "The request could not be completed, it is malformed.";
						endProcess('error',$error);	
					}
				$vars[$key] = scrubInput($_POST[$key]);
				$keylist .= $key.',';
				$vallist .= "'".$vars[$key]."',";
				$editlist .= "$key = '".$vars[$key]."',";
			}
		$keylist = substr($keylist,0,-1);
		$vallist = substr($vallist,0,-1);
		$editlist = substr($editlist,0,-1);
		if($do == 'add')
			{
				$query = "INSERT INTO categories ($keylist) VALUES ($vallist)";
				mysql_query($query) or dieLog("Could not add new category to database because ".mysql_error());
				
				// Check that it's been added
				$query = "SELECT category_id FROM categories WHERE name = '".$vars['name']."'";
				$result = mysql_query($query) or dieLog("Could not check new category in database because ".mysql_error());
				if(!mysql_num_rows($result))
					{
						$error = "Could not add the new category to the database for an unknown reason.";
						endProcess('error',$error);	
					}
				endProcess('success',"Category ".$vars['name']." added");
			}
		else
			{
				if(!isset($_POST['category_id']))
				{
					$error = "The request could not be completed, it is malformed.";
					endProcess('error',$error);	
				}
				$id = $_POST['category_id'];
				$query = "UPDATE categories SET $editlist WHERE category_id = '$id'";
				mysql_query($query) or dieLog("Could not edit category $id in database because ".mysql_error());
				endProcess('success',"Category ".$vars['name']." edited");
			}	
	}

if($do == 'delete')
	{
		if(!isset($_POST['category_id']))
			{
				$error = "The request could not be completed, it is malformed.";
				endProcess('error',$error);	
			}
		
		// Check if this Category has any children categories or applications in the database.  If it does, we
		// send back an error message. 
		$catid = $_POST['category_id'];
		$query = "SELECT category_id FROM categories WHERE parent_id = '$catid'";
		$result = mysql_query($query) or dieLog("Could not get sub categories from the database because ".mysql_error());
		if(mysql_num_rows($result))
			{
				$error = "The category you're trying to delete has sub categories. Move or delete them first.";
				endProcess('error',$error);
			}
		$query = "SELECT app_id FROM applications WHERE category_id = '$catid'";
		$result = mysql_query($query) or dieLog("Could not get sub applications from the database because ".mysql_error());
		if(mysql_num_rows($result))
			{
				$error = "The category you're trying to delete has sub applications. Move or delete them first.";
				endProcess('error',$error);
			}
		
		// Now we can delete this source
		$query = "DELETE FROM categories WHERE category_id = '$catid'";
		$result = mysql_query($query) or dieLog("Could not delete category from the database because ".mysql_error());
		
		endProcess('success','Category Deleted');
	}
?>
