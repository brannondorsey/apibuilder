<?php

	 require_once("includes/class.API.inc.php");
	 header("Content-Type: text/javascript; charset=utf-8");
	 
	 $api = new API();
	 // if(isset($_GET) && !empty($_GET)){
	 // 	 Database::init_connection();
	 	 
		//  $get_array = Database::clean($_GET); //clean the $_GET array
		//  $data = $api->get_JSON_from_GET($get_array); //return user JSON objs based on API get params
		//  Database::close_connection();
	 // 	 echo $data;
	 // }else{
	 	Database::init_connection();
	 	$json_obj = new StdClass();

	 	$query = "SELECT * FROM metadata ORDER BY id DESC LIMIT 5";
	 	if($results_array = Database::get_all_results($query)){

	 		// deletes key => value pairs if the value is empty. Only works if array is nested: 
	 		// http://stackoverflow.com/questions/5750407/php-array-removing-empty-values
	 		$results_array = array_filter(array_map('array_filter', $results_array));
	 		$json_obj->data = $results_array;
	 		
	 	}else $json_obj->error = "no results found";


	 	echo json_encode($json_obj, JSON_PRETTY_PRINT);
	 
?>