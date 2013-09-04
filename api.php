<?php

	 //include the API Builder mini lib
	 require_once("api_builder_includes/class.API.inc.php");

	 //set page to output JSON
	 header("Content-Type: text/javascript; charset=utf-8");
	 
	  //If API parameters were included in the http request via $_GET...
	  if(isset($_GET) && !empty($_GET)){

	  	//specify the columns that will be output by the api
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

	  	//setup the API
	  	$api = new API("localhost", "quartzite", "metadata", "root", "root");
	  	$api->setup($columns);
	  	$api->set_default_order("timestamp");
	  	$api->set_searchable("url, description, keywords");
	  	$api->set_default_search_order("timestamp");
	  	$api->set_exclude_allowed(true);
	  	$api->set_pretty_print(true);

	  	//sanitize the contents of $_GET to insure that 
	  	//malicious strings cannot disrupt your database
	 	$get_array = Database::clean($_GET);

	 	//output the results of the http request
	 	echo $api->get_json_from_assoc($get_array);
	}
?>