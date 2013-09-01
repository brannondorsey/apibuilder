<?php

	 require_once("includes/class.API.inc.php");
	 header("Content-Type: text/javascript; charset=utf-8");

	 
	  if(isset($_GET) && !empty($_GET)){

	  	$api = new API("localhost", "quartzite", "metadata", "root", "root");
	  	$columns = "id, 
	  				timestamp, 
	  				filename, 
	  				title, 
	  				domain, 
	  				url, 
	  				referrer, 
	  				ip, 
	  				forward_from, 
	  				author, 
	  				owner, 
	  				description, 
	  				keywords, 
	  				copywrite";
	  				
	  	$api->setup($columns);
	  	$api->set_searchable("url, description, keywords");
	  	$api->set_pretty_print(true);
	 	$json_obj = new StdClass();

	 	$get_array = Database::clean($_GET);
	 	echo $api->get_JSON_from_GET($get_array);
	}
?>