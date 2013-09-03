<?php

	 //include the API Builder mini lib
	 require_once("api_builder_includes/class.API.inc.php");

	 //set page to output JSON
	 header("Content-Type: text/javascript; charset=utf-8");
	 
	  //If API parameters were included in the http request via $_GET...
	  if(isset($_GET) && !empty($_GET)){

	  	//specify the columns that will be output by the api
	  	$columns = "id, 
                    first_name,
                    last_name,
                    email,
                    phone_number,
                    city,
                    state,
                    bio";

	  	//setup the API
	  	$api = new API("localhost", "fakeorganization", "clients", "root", "root");
	  	$api->setup($columns);
	  	$api->set_key_required(true);
	  	$api->set_default_order("last_name");
	  	$api->set_searchable("bio");
	  	$api->set_default_search_order("id");
	  	$api->set_pretty_print(true);

	  	//sanitize the contents of $_GET to insure that 
	  	//malicious strings cannot disrupt your database
	 	$get_array = Database::clean($_GET);

	 	//output the results of the http request
	 	echo $api->get_json_from_assoc($get_array);
	}
?>
