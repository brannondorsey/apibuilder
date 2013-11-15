<?php
	require_once '../api_builder_includes/class.Database.inc.php';

	Database::init_connection("localhost", "organization", "users_data", "username", "secret_password");
	
    // Array containing the row to change. The only required values are the id and the column being changed.
    // All other key => value pairs are ignored but are present here because often rows are updated in batch
    // after being returned in 2D array fashion from Database::get_all_results();
    $user = array("id" => 2,
                  "first_name" => "Salvester",
                  "last_name" => "Rinehart",
                  "email" => "salrinehard@gmail.com",
                  "phone_number" => "8042557684",
                  "city" => "Richmond",
                  "state" => "VA",
                  "bio" => "Total badass.");

    //sanitize user input
    $user_cleaned = Database::clean($user);

    //the 3rd parameter specifies this is an update statement by selecting which column from in the row to update
    $result = Database::execute_from_assoc($user_cleaned, Database::$table, "phone_number");
    if($result>0) {
        echo $user['first_name'] . "'s phone number was changed to " . $user['phone_number'];
    } else if($result==0) {
    	echo "No rows affected";
    } else {
    	echo "Error: ".$result;
    }

?>