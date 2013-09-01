<?php

	 //include the API Builder mini lib
	 require_once("includes/class.API.inc.php");

	 //set page to output JSON
	 header("Content-Type: text/javascript; charset=utf-8");
	 
	  //If API parameters were included in the http request via $_GET...
	  if(isset($_GET) && !empty($_GET)){

	  	//specify the columns that will be output by the api as a comma-delimited list
	  	$columns = "column_name,
	  				column_name,
	  				column_name,
	  				column_name,
	  				column_name,
	  				etc...";

	  	//setup the API
	  	$api = new API("your_host", 
	  				   "your_database_name", 
	  				   "your_table_name", 
	  				   "your_database_username", 
	  				   "your_database_password");

	  	$api->setup($columns);
	  	$api->set_default_order("column_name");
	  	$api->set_searchable("column_name, column_name, etc...");
	  	$api->set_default_search_order("column_name");
	  	$api->set_pretty_print(true);

	  	//sanitize the contents of $_GET to insure that 
	  	//malicious strings cannot disrupt your database
	 	$get_array = Database::clean($_GET);

	 	//output the results of the http request
	 	echo $api->get_JSON_from_GET($get_array);
	}
?>