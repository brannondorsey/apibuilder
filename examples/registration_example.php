<?php
    // include the API Builder Database class
    require_once('../api_builder_includes/class.Database.inc.php');
    var_dump($_POST);
    //if POST is present...
    if(isset($_POST) &&
       !empty($_POST)){

        // Do any neccissary validation here. You can use something like https://github.com/ASoares/PHP-Form-Validation
        // if you are not going to validate input, which you absolutely should if users are submitting it, then at least
        // make sure the correct values are present
        if(isset($_POST['first_name']) && !empty($_POST['first_name']) &&
           isset($_POST['last_name']) && !empty($_POST['last_name']) &&
           isset($_POST['email']) && !empty($_POST['email']) &&
           isset($_POST['phone_number']) && !empty($_POST['phone_number']) &&
           isset($_POST['city']) && !empty($_POST['city']) &&
           isset($_POST['state']) && !empty($_POST['state']) &&
           isset($_POST['bio']) && !empty($_POST['bio'])){

            // Open the database connection. This is what happens inside of the API class constructor
            // but if this page is simply for submitting data to the database you can just call this method
            Database::init_connection("localhost", "organization", "users", "username", "secret_password");

            // Sanitize the array so that it can be safely inserted into the database.
            // This method uses MySQLi real escape string and htmlspecialchars encoding.
            $post_array = Database::clean($_POST);

            //submit the data to your table.
            if(Database::execute_from_assoc($post_array, Database::$table)){
                echo "The data was submitted to the database";
            }else echo "There was an error submitting the data to the database";
        }else echo "One or more of the required values is missing from the POST";
    }else echo "Nothing was added to the database because the http request has no POST values";
?>

<form method="post" action="">
    <label for="first-name">First Name</label>
    <input type="text" id="first-name" name="first_name"/>

    <label for="last-name">Last Name</label>
    <input type="text" id="last-name" name="last_name"/>

    <label for="email">E-mail</label>
    <input type="text" id="email" name="email"/>

    <label for="phone-number">Phone Number</label>
    <input type="tel" id="phone-number" name="phone_number"/>

    <label for="city">City</label>
    <input type="text" id="city" name="city"/>

    <label for="state">State</label>
    <input type="text" id="state" name="state"/>

    <label for="bio">bio</label>
    <textarea id="bio" name="bio">
    </textarea>

    <input type="submit" value="submit">
 </form>
