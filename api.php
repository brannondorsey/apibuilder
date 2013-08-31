<?php

	 require_once("includes/class.API.inc.php");
	 header("Content-Type: text/javascript; charset=utf-8");

	 
	  if(isset($_GET) && !empty($_GET)){

	  	$api = new API("localhost", "quartzite", "metadata", "root", "root");
	 	$json_obj = new StdClass();

	 	$get_array = Database::clean($_GET);
	 	echo $api->get_JSON_from_GET($get_array);
	}
?>