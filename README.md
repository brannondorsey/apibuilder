#PHP API Builder
Easily transform MySQL tables into web accessible JSON APIs with this mini library for PHP.

[Getting Started](#getting-started) | [Customizing your API](#customizing-your-api) | [Making Requests](#making-requests) | [Using the Data](#using-the-data) | [Submitting your Data](#submitting-data-to-your-database) | [API Parameter Reference](#api-parameter-reference)


##Getting Started
This PHP API Builder is used to build simple http JSON APIs from MySQL databases. With it you (or anyone if you choose to make the API pubic) can access data on the web through an easy-to-setup `api.php` page. Using the API Parameters provided in this mini library users can query a database through that `api.php` page using `GET` parameters included in the request's URL and return the results as an array of JSON results. A full list of available API parameters is located in the [API Parameter Reference](#api-parameter-reference) section of this documentation. 

###How it works

Setting up the API is easy! To add an API to an existing MySQL table simply place this repository's `api_builder_include/` folder and `api_template.php` file in the directory where you want your api page to be located (If you want your api accessible at yourdomain.com/api.php you should put these files in your root directory). Next update the `api_template.php` file to reflect your database info and your desired [API customization](#customizing-your-api). Then save the updated file as `api.php` or whatever you want your API page to be called.

Thats it! You can now access the data from your MySQL database using the [API Builder URL Parameters](#api-parameter-reference). Below is an basic example of how the `$api` object can be setup.

###Download

You can direct download a .zip of API Builder by clicking [here](https://github.com/brannondorsey/apibuilder/archive/master.zip). The API Builder mini lib was built and tested using PHP 5.4.4 and results when using earlier versions of PHP are unknown.

###Example

Throughout this reference an example database will be used. 
This example table, named `users`, holds information about imaginary users that belong to an organization. The `api.php` for this example is as follows:

```php
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
	  	//the API constructor takes parameters in this order: host, database, table, username, password
	  	$api = new $API("localhost", "organization", "users", "username", "secret_password");
		$api->setup($columns);
		$api->set_default_order("last_name");
		$api->set_searchable("first_name, last_name, email, city, state, bio");
		$api->set_default_search_order("last_name");
		$api->set_pretty_print(true);

	  	//sanitize the contents of $_GET to insure that 
	  	//malicious strings cannot disrupt your database
	 	$get_array = Database::clean($_GET);

	 	//output the results of the http request
	 	echo $api->get_json_from_assoc($get_array);
	}
?>
```

Use the `api_template.php` to create your own api.

##Customizing your API

The API Builder mini lib features many more complex API setups than the one demonstrated in the `api_template.php` file. Some of these features include:

- Making an API Private so that only you can access the data it provides
- Using API keys to track and limit hits-per-day usage to specific users
- And setting API defaults for number of results returned per request, default order to return results, etc…

All `API` class setup methods (excluding the constructor) begin with the word `set`. A full list of these setup methods and a brief description can be viewed below. For more information about each method view the [`class.API.inc.php`](api_builder_includes/class.API.inc.php) source.

###API Class Setup Methods

Names in __bold__ denote methods that are required to use when building an API. All other methods are optional.

- **`API::__construct($host, $database, $table, $username, $password)`** Instantiates the API object and creates a MySQLi database connection.
-  **`API::setup($columns)`** tells the API object which column values to use when outputting results objects. The `$columns` parameter is a comma-delimited list of column names that correspond to the column names in your database.
- **`API::set_default_order($column)`** sets the default column for the api to order results by if no 'order_by' parameter is specified in the request.
- `API::set_default_flow($flow)` sets the default [flow](#flow-parameter) if none is specified in the request.
- `API::set_defualt_output_number($default_output)` sets the number of JSON result objects each API request will output if no 'limit' parameter is included in the request.
- `API::set_max_output_number(int $max_output)` sets the max number of JSON result objects allowed per request.
- `API::set_pretty_print($boolean)` sets the default JSON output as human readable formatted
- `API::set_searchable($columns)` enables the [API 'search' parameter](#search-parameter) and specifies which columns can be searched. Again the `$columns` parameter is a comma-delimited list of column names that correspond to the column names in your database. Only Text columns that have been FULLTEXT indexed may be included in the columns list.
- **`API::set_default_search_order($column)`** sets the default columns for the API to order API 'search' parameter results by if the MySQL FULLTEXT Match()…Against()… statement is executed in boolean mode (required __only__ if `API::set_searchable()` has enabled columns to be searched).
- `API::set_exclude_allowed($boolean)` enables the [API 'exclude' parameter]. This method's parameter can only be `TRUE` if your database's table includes an 'id' column (or whatever unique column name is included as this method's optional parameter).
- `API::set_key_required($boolean)` makes your API require a unique key for each request. For more information on limiting and tracking API users visit the [Protecting your API](#protecting-your-api) section of this documentation.
- `API::set_hit_limit($number_hits_per_day)` sets the number of API hits per API key per day.
- `API::set_private($private_key)` makes the API private (i.e. only you can use it). For more information on this method visit the [Protecting your API](#protecting-your-api) section of this documentation.
- `API::set_no_results_message($message)` sets the error message when no results are found in a request.

If the API setup is configured incorrectly the `api.php`'s resulting JSON response object will contain a `config_error` array of messages describing the errors instead of a `data` property.

###Other Methods

Aside from the API setup methods there are a few other methods in that can be useful to know

- `API::get_json_from_assoc($assoc_array)` returns the API results as JSON from an associative array of [API Builder Parameters](#api-parameter-reference). This is how you actually print the API results to the browser.

And from the static `Database` class:

- `Database::init_connection($host, $database, $table, $username, $password)` creates a database connection. This static method is called from inside `API::__construct()` so if you have already initialized an API object you should not need to use this static method unless the database has been closed.
- `Database::close_connection()` closes the MySQLi database connection.
- `Database::execute_sql($mysql_query_string)` executes the MySQL statement provided as its parameter and returns a boolean representing it's success.
- `Database::get_all_results($mysql_query_string)` returns a 2D array of table results from the MySQL query string passed as it's parameter.
- `Database::clean($string_or_array)` encodes the parameter using `htmlspecialchars` and `mysqli_real_escape_string` and returns the cleaned string. Useful for sanitizing input before injecting it into the database.
- `Database::execute_from_assoc($assoc_array, $table_name)` inserts rows into (or updates existing rows if optional parameters are used) the database from an $assoc_array where all keys in the array are their value's column names in $table_name. The table name can be accessed via the `Database::$table` static public property.

__Note:__ All `Database` class methods are static.

For more info on or the mini library itself you can read the source code. More examples are coming soon, especially for how to update your database thought `GET` or `POST` using `Database::execute_from_assoc($string_or_array)`!

###Protecting your API

There are two ways of limiting access to your API using the setup methods. The first, and most private, is by setting up your API so that only you (and people who you give your private key to) can access it. The second is to track and limit API usage using API keys that are distributed to your users.

####Making your API Private

To make your API private include the following when setting up your `api.php` page where `$private_key` is a unique 40 character SHA1: 

```php
$private_key = "4e13b0c28e17087366ac4d67801ae0835bf9e9a1";
$api->set_private($private_key);
```

Then when you make an http request to your API just prepend your private key to the request using the [API Private Key Parameter](#private-key-parameter):

	http://fakeorganization.com/api.php?last_name=Renolds&private_key=4e13b0c28e17087366ac4d67801ae0835bf9e9a1`

Viola… your own private API.

####Limiting and Tracking Usage with API Keys

Often API owners supply users with unique API keys to track and limit requests so as not to bog down servers. This is a common practice with the Google, Twitter, and Facebook APIs. The API Builder allows API owners to easily do the same!

#####Database Setup

Because this process requires each user to have their own unique API key to access the API, a new table needs to be made to store information about the users that will be making the requests. This table should be named "users" and should include __at least__ the following columns: "id", "API_key", "API_hits", and "API_hit_date". These table and column names must be exact unless specified otherwise using `API::set_key_required()`'s optional parameters (see [API setup](#api-setup) below). This SQL table structure can be imported into your existing database from the [`users_table.sql`](users_table.sql) file.

Alternatively, it is often the case that APIs actually describe data regarding users in the first place. For instance, the Twitter and Facebook APIs deliver data about users! If your API is delivering data from a users table already, and you would like to grant __only__ those users access to your API, you may simply add the "id", "API_key", "API_hits", and "API_hit_date" columns to your existing users table instead of creating a new one.

#####API Setup

Once the new table has been created in your database (or your original table that was saving users  was amended to include the required columns) you are ready to setup you API to track and limit user's hits. If you used the default table name "users" and the column names "id", "API_key", "API_hits", and "API_hit_date" then the API setup is easy:

```php
$api->set_key_required(true);
```

If you chose to change the names of the table or any of the columns then these changes must be specified using `API::set_key_required()`'s optional parameters:

	API::set_key_required($boolean, $users_table_name=false, $key_column_name=false, $hit_count_column_name=false, $hit_date_column_name=false);

If for instance you chose not to add a new "users" table to your database because the table used for the API, "fakeorganization_users", is already storing users who you want to give access to the API (like in the case of Twitter or Facebook) then the setup would look like this.

```php
$api->set_key_required(true, "fakeorganizations_users");
```

If you also changed the "API_hits" column name to something like "number_of_hits_today" then your API key setup would be:

```php
$api->set_key_required(true, "fakeorganizations_users", false, "number_of_hits_today");
```

__Note:__ Don't forget to specify the unchanged table or column names as `false` to maintain parameter order!

For simplicity it is recommended to download and import the "users" table structure without modifying the column names.

All that is required now is for you to fill the "users" table's "API_key"s with 40 character SHA1 and distribute them to your real-life users so that they can use your API.

#####Usage and Errors

Once your API has been set to require a key (`API::set_key_required()`) only users who make http requests that contain a valid [API Key Parameter](#api-key-parameter) will be given API results. 

```php
http://fakeorganization.com/api.php?last_name=Renolds&key=1278cf264faca856baf2268e52e2761a75972ec7
```

In the above example the "users" table must include a user row where the value of the "API_key" (or your API's equivalent column) is "1278cf264faca856baf2268e52e2761a75972ec7". In the event that this is not the case, an invalid key is provided, or no key is provided, the following `error` property is returned in the response JSON object instead of a `data` array:

```json
{
    "error": "API key is invalid or was not provided"
}
```

In the event that a user has maxed out their API hits for the day (set using `API::set_hit_limit()`) the API would return the following:

```json
{
    "error": "API hit limit reached"
}
```

##Making Requests

Using your API is easy once you learn how it works. 

###Formatting a request

The API Builder queries [MySQL Databases](https://en.wikipedia.org/wiki/MySQL) and so the http requests used to return data is very similar to forming a MySQL `SELECT` query statement. If you have used MySQL before, think of using the API URL parameters as little pieces of a query. For instance, the `limit`, `order_by`, and `flow` (my nickname for MySQL `ORDER BY`'s `DESC` or `ASC`) parameters translate directly into a MySQL statement on your server.

###Example Request
     http://fakeorganization.com/api.php?search=Thompson&order_by=id&limit=50
     
The above request would return the 50 newest users who have Thompson included somewhere in their first_name, last_name, email, city, state or bio columns.

###Notable Parameters

- `search` uses a MySQL FULLTEXT search to find the most relevant results in the database to the parameter's value.
- `order_by` returns results ordered by the column name given as the parameter's value.
- `limit` specifies the number of returned results. If not included as a parameter the default value is `25` and the max value is `250`.
- `page` uses a MySQL `OFFSET` for paginating results from the database. Used most effectively when paired with `limit`.

A full list of all API Builder's parameters are specified in the [Parameter Reference](#api-parameter-reference) section of this documentation.

###Returned JSON

All data returned by your API is wrapped in a JSON object with a `data` array property. If there is an error, or no results are found, an `error` variable with a corresponding error message will be returned __instead__ of a `data` property. If your API is setup incorrectly in you `api.php` page a `config_error` array is returned. 

Inside the `data` property is an array of objects that are returned as a result of the URL [API Builder Parameters](#api-parameter-reference) outlined shortly.

```json
{
    "data": [
        {
            "id": "1035",
            "first_name": "Thomas",
            "last_name": "Robinson",
            "email": "thomasrobinson@gmail.com",
            "phone_number": "8042123478",
            "city": "Richmond",
            "state": "VA",
            "bio": "I am a teacher in the Richmond City Public School System"
        },
        {
            "id": "850",
            "first_name": "George",
            "last_name": "Gregory",
            "email": "gregg@gmail.com",
            "phone_number": "8043703986",
            "city": "Richmond",
            "state": "VA",
            "bio": "I am creative coder from Richmond"
        }
    ]
}
```

__Note:__ The `data` property is always an array of objects even if there is only one result.

##Using the Data

Because the API outputs data using JSON the results of an API http request can be loaded into a project written in almost any language. I have chosen to provide brief code examples using `PHP`, however, these code snippets outline the basics of loading and using your API data and easily apply to another language. 

```php
<?php
$city = "Richmond";
$bio = "artist";

$http_request = "http://fakeorganization.com/api.php?city=$city&bio=$bio&limit=10";
	
$json_string = file_get_contents($http_request);
$jsonObj = json_decode($json_string);
	
//loop through each user object inside of the "data" array
foreach($jsonObj->data as $user){
   //do something with each result inside of here...
   //for example, print some of their info to the browser
   echo "This user's first name is " . $user->first_name . "<br/>";
   echo "This user's last name is " . $user->last_name . "<br/>";
   echo "This user's email is " . $user->email;
   echo "<br/>";
}
?>
```

###Error Handling

Often requests to the API return no results because no results were found that met the request's criteria. For this reason it is important to know how to handle the API `error`. The JSON that is returned in this instance is `{"error": "no results found"}` or whatever you specify using the `API::set_no_results_message()`.

Handling `errors` is simple. All that you need to do is check if the `error` property exists in the resulting JSON object. If it does execute the code for when an error is present. Otherwise, continue with the program because the request returned at least one result object.

```php
<?php
$city = "Richmond";
$bio = "artist";

$http_request = "http://fakeorganization.com/api.php?city=$city&bio=$bio&limit=10";
	
$json_string = file_get_contents($http_request);
$jsonObj = json_decode($json_string);

//check for an error
if(isset($jsonObj->error)){
	//code for handling the error goes in here...
	//for example, print the error message to the browser
	echo $jsonObj->error;
	}else{
		//execute the code for when user objects are returned…
		//loop through each user object inside of the "data" array
		foreach($jsonObj->data as $user){
		   //do something with each result inside of here...
		   //for example, print some of their info to the browser
		   echo "This user's first name is " . $user->first_name . "<br/>";
		   echo "This user's last name is " . $user->last_name . "<br/>";
		   echo "This user's email is " . $user->email;
		   echo "<br/>";
		}
	}
?>
```

##Submitting Data to Your Database

Aside from using the API Builder to create a web accessible API, there are several methods that allow you to actually add/edit data in your database using `GET` or `POST` methods. If you are familiar with making` $.ajax` or `XmlHttpRequest`s with Javascript then inserting into and updating existing rows in your database is easy!

The `Database::execute_from_assoc($assoc_array, $tablename)` static method handles both of these needs. The default behavior of this method takes two parameters and is used to insert data into the database by dynamically generating a MySQL `INSERT` statement. Each time `Database::execute_from_assoc()` is called it affects __only 1 row__. The first parameter is an associative array with a required column name as each key and the value you want to insert into that column as the value. The second parameter is the name of the table to make the query. If you are executing the query on the table that you passed into `Database::init_connection()` or `API::__construct()` then you can access that table using the static `Database::$table` property. Otherwise you can specify the table name as a string.

Below is an example of how to insert a new user into the example that database we have been using throughout this documentation.

###Inserting

If the html form on the registration page looks like this:

```html
<form method="post" action="yoursubmissionpage.php">
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
```

Then the php code on the submission page, often the same page that the html form is on as in [`examples/registration_example.php`](examples/registration_example.php), would look like this:

```php
<?php
    // include the API Builder Database class
    require_once('api_builder_includes/class.Database.inc.php');
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
```

###Updating

The `Database::execute_from_assoc()`'s third optional parameters can allow the database to update existing rows as long as the id of the row to update is passed in as key => value pair in the method's first parameter. When updating, the method's 3rd parameter should be a string representing the name of the column to update.

```php
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
    if(Database::execute_from_assoc($user_cleaned, Database::$table, "phone_number")){
        echo $user['first_name'] . "'s phone number was changed to " . $user['phone_number'];
    }
```

###Other

The `Database::clean($dirty)` method takes a string or array and sanitizes it using MySQLi real escape string and htmlspecialchars. I am no security expert and it is very possible that more string sanitation is needed before inserting data into your database. 

The `Database::execute_sql($query)` method allows you to execute raw SQL statements as strings that are passed in as it's parameter. If for instance, if you wanted to change the structure of the table from the database you are connected to you could use this method. Or if you wanted to batch update a row of users you could use this method

```php
$query = "UPDATE " . Database::$table . " SET state='Virgnia' WHERE state='VA'";
if(Database::execute_sql($query)) echo "Update statement succeeded!";
```

Be careful using this method as you can make changes to the Database that could disrupt your API setup.



##API Parameter Reference

This section documents in detail all of the API Builder URL parameters currently available to use when making an http request to your API.

It uses a made up table with a setup that is specified [earlier in this documentation](#example).

###API Key Parameter

If the API has been setup to require a unique key it will need to be included with each request. If the API was [set up in this fashion](#limiting-and-tracking-usage-with-api-keys) you will not be able to access the API's data unless you have been provided with a valid key. API Builder APIs have this function disabled by default but can be ]enabled by the API owner with the `API::set_key_required()` method.

Parameter __key:__ `key`

Parameter __value:__ A unique 40 character `SHA1` key

__Example__: 

     http://fakeorganization.com/api.php?last_name=Renolds&key=8a98253d8b01d4cf8c3fe183ef0862fa69a67b2e
     
__Note:__ Failing to include a valid API key when it is required or making more than the allowed requests in a day will throw an `error` object in place of a `data` object.

###Private Key Parameter

Similar to the API Key Parameter the Private Key Parameter limits access to the API. Unlike the API Key however there is only one Private Key allowed per API. This parameter is used if the API is intended to be private (i.e. for only one user, not just limited access as with the API Key Parameter). This function is disabled by default but can be enabled by the API owner with `API::set_private()`.

Parameter __key:__ `private_key`

Parameter __value:__ A unique 40 character `SHA1` key

__Example__: 

    http://fakeorganization.com/api.php?last_name=Renolds&private_key=ac3c1017b45b299dbf99ce8470c56b063e24f935


###Column Parameters
Column parameters allow you to query data for a specific column value where the parameter key is specified to be the column name in your database. Column parameters can be stacked for more specific queries.

Parameter __keys:__ Column name (i.e. `first_name`) to perform query on.

Parameter __values:__ 
Desired lookup `string`, `int`, or `float` that corresponds to the column name in the database as specified by the parameter's key.

__Example:__

      http://fakeorganization.com/api.php?last_name=Thompson&limit=10
      
This example request would return up to 10 users who's last name are Thompson ordered alphabetically by last name.

__Notes:__ The column parameter's are overridden if a `search` parameter is included in the request. 

###Search Parameter
The `search` parameter uses a  MySQL `FULLTEXT` [Match()… Against()…](http://dev.mysql.com/doc/refman/5.5/en/fulltext-search.html#function_match) search to find the most relevant results to the searched string. 

Parameter __key:__ `search`

Parameter __value:__ Desired query `string`

__Example:__

	http://fakeorganization.com/api.php?search=design

__Notes:__ `search` results are automatically ordered by relevancy, or if relevancy is found to be arbitrary, by the value set by the API owner using the `API::set_default_search_order()` method. The `order_by` parameter cannot be used when the `search` parameter is specified. More on why below…

Default Match()…Against()… MySQL statements search databases using a 50% similarity threshold. This means that if a searched string appears in more than half of the rows in the database the search will ignore it. Because it is possible that webpages will have similar tags, I have built the API to automatically re-search `IN BOOLEAN MODE` if no results are found in the first try. If results are found in the second search they are ordered by a default column set by the API owner.

###Order By Parameter

This parameter is used with the column parameters to sort the returned users by the specified value. If `order_by` is not specified its value defaults to the value set by the API's `API::set_default_order()` in the `api.php` page. `order_by` will be ignored when the `search` parameter is specified.

Parameter __key:__ `order_by`

Parameter __value:__ Column name (i.e. `id`) to order by

__Example:__

	http://fakeorganization.com/api.php?state=VA&order_by=id&limit=15

This request returns the 15 most recent users from Virginia.

###Flow Parameter

This parameter specifies the MySQL `ASC` and `DESC` options that follow the `ORDER BY` statement. If `flow` is not specified it defaults to `DESC`.

Parameter __key:__ `flow`

Parameter __value:__ `ASC` or `DESC`

__Example:__

	http://fakeorganization.com/api.php?state=VA&order_by=id&limit=15&flow=asc
	
This request returns the 15 __least recent__ users from Virginia.		
__Notes:__ `flow`'s values are case insensitive.

###Limit Parameter

The `limit` parameter works similarly to MySQL `LIMIT`. It specifies the max number of users to be returned. The default value, if unspecified is `25`. The default max value of results that can be returned in one request is `250`. 

Parameter __key:__ `limit`

Parameter __value:__ `int` between `1-250` or between `1` and the max value specified by the API owner using `API::set_max_output_number()`.

__Example:__

	http://fakeorganization.com/api.php?state=IL&limit=5

Returns the 5 most recent users from Illinois.

###Page Parameter

The page parameter is used for results pagination. It keeps track of what set (or page) of results are returned. This is similar to the [MySQL OFFSET statement](http://dev.mysql.com/doc/refman/5.0/en/select.html). If not specified the page value will default to `1`.

Parameter __key:__ `page`

Parameter __value:__ `int` greater than `0`

__EXAMPLE:__ 

	http://fakeorganization.com/api.php?search=programmer&limit=7&page=3&order_by=id&flow=asc	
This request will return the 3rd "page" of `search` results. 

For instance, in the unlikely example that all users have the string "programming" in their bios, setting `page=1` would return webpages with id's `1-7`, setting `page=2` would yield `8-14`, etc…

__Note:__ The MySQL `OFFSET` is calculated server side by multiplying the value of `limit` by the value of `page` minus one. 

###Exact Parameter

The exact parameter is used in conjunction with the column parameters and specifies whether or not their values are queried with relative or exact accuracy. If not included in the URL request the `exact` parameter defaults to `false`.

Parameter __key:__ `exact`

Parameter __values:__ `TRUE	` and `FALSE`

__Example:__

	http://fakeorganization.com/api.php?id=10&exact=TRUE

This request will limit the returned results to the user whose id is __exactly__ 10. If the `exact` parameter was not specified, or was set to `FALSE`, the same request could also return users whose id's have 10 in them (i.e. 1310, 10488, 100 etc…). Including `exact=true` parameter in an API http request is equivalent to using a `MySQL` `LIKE` statement.
	
__Notes:__ `exact`'s values are case insensitive.

###Exclude Parameter

The exclude parameter is used in conjunction with the column parameters to exclude one or more specific result row from a query. It is disabled by default but may be enabled by the API owner.

Parameter __key:__ `exclude`

Parameter __values:__ a comma-delimited list of excluded row's `id`'s.

__Example:__

	http://fakeorganization.com/api.php?email=@gmail.com&exclude=5,137,1489&limit=50

This example will return 50 users other than numbers `5`, `137`, and `1489` who use gmail ordered by last name.

__Note:__ If the `exclude` parameter is included in an http request to an API that has disabled this function an `error` properly will be present to notify the user.

###Count Only Parameter

The `count only` parameter differs from all of the other API Builder parameters as it __does not__ return an array of result objects. Instead, it returns a single object as the first element in the `data` array. This object has only one property, `count`, where the corresponding `string` value describes the number of results returned by the rest of the url parameters. If the `count_only` parameter is not specified the default value is `FALSE`. When `count_only` is set to `TRUE` the request will __only__ evaluate and return the number of results found by the rest of the url parameters and the request __will not__ return any row data.

Parameter __key:__ `count_only`

Parameter __values:__ `	TRUE` or `FALSE`

__EXAMPLE:__

     //request
     http://fakeorganization.com/api.php?first_name=Thomas&exact=true&count_only=true
     
     //returns
     {
      "data":[
        {
        "count":"45"
        }]
     }
     
This request returns the number of users that have the first name "Thomas". The count value is returned as __a `string`__.

__Note:__ The value of `count_only` is case insensitive.

###Pretty Print Parameter

The `pretty_print` Parameter returns a response with indentations and line breaks. If `pretty_print` is set to `TRUE` the results returned by the server will be human readable (pretty printed). With most API setups Pretty Print will be enabled by default but the API owner may have this feature disabled. Either way Pretty Print can be turned on or off with this parameter. 

Parameter __key:__ `pretty_print`

Parameter __value:__ `TRUE` or `FALSE`

__Example:__

	http://fakeorganization.com/api.php?email=@gmail.com&pretty_print=false
	
__Note:__ The value of `pretty_print` is case insensitive. If large amounts of data are being transferred and human readability is unimportant it is suggested to disable pretty print so as to enable faster API requests and data parsing.

##License and Credit

The API Builder PHP Mini Library is developed and maintained by [Brannon Dorsey](http://brannondorsey.com) and is published under the [MIT License](license.txt). If you notice any bugs, have any questions, or would like to help me with development please submit an issue or pull request, write about it on the wiki, or [contact me](mailto:brannon@brannondorsey.com).