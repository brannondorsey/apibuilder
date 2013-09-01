#PHP API Builder
Easily transform MySQL tables into web accessible json APIs with this mini library for PHP.

##Getting Started
This PHP API Builder is used to build simple [REST APIs](http://en.wikipedia.org/wiki/Representational_state_transfer) from MySQL databases. With it you (or anyone if you choose to make the API pubic) can access and update data on the web through an easy-to-setup `api.php` page. Using the API parameters provided in this mini lib users can query a database through that `api.php` page using http `GET` parameters and return the results as an array of `JSON` results. A full list of available API parameters is located [below](#api-parameter-reference). 

###How it works

Setting up the API is easy! To add an API to an existing MySQL table simply place this repository's `include/` folder and `api.php` file in the directory where you want your api page to be located (If you want your api accessible at yourdomain.com/api.php you should put these files in your root directory). Next update the `api_template.php` file to reflect your database info and [API customization](#customizing-your-api). Then save the updated file as `api.php` or whatever you want your API page to be called.

Thats it! You can now access your MySQL database using the [API Builder URL Parameters](#api-parameter-reference) Below is an basic example of how the `$api` object can be setup. For more info and options check out the [API Builder documentation](COME BACK).

###Download

You can direct download a .zip of API Builder by clicking [here](COME BACK).

###Example

Throughout this reference an example database will be used. 
This example table named `users` holds information about imaginary users that belong to an organization. The `api.php` for this example is as follows:

```php
<?php

	 //include the API Builder mini lib
	 require_once("includes/class.API.inc.php");

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
	 	echo $api->get_JSON_from_GET($get_array);
	}
?>
```

Use the `api_template.php` to create your own api.

##Customizing your API


##Using your API

Using your API is easy once you learn how it works. 

####Formatting a request

The API Builder queries [MySQL](https://en.wikipedia.org/wiki/MySQL) Databases and so the http requests used to return data is very similar to forming a MySQL `SELECT` query statement. If you have used MySQL before, think of using the api URL parameters as little pieces of a query. For instance, the `limit`, `order_by`, and `flow` (my nickname for MySQL `ORDER BY`'s `DESC` or `ASC`) parameters translate directly into a MySQL statement on your server.

####<a id="example-request"></a>Example Request
     http://fakeorganization.com/api.php?search=thompson&order_by=id&limit=50
     
The above request would return the 50 newest users who have thompson included somewhere in their first_name, last_name, email, city, state or bio columns.

####Notable Parameters

- `search` uses a MySQL FULLTEXT search to find the most relevant results in the database to the parameter's value.
- `order_by` returns results ordered by the column name given as the parameter's value.
- `limit` specifies the number of returned results. If not included as a parameter the default value is `25`. Max value is `250`.
- `page` uses a MySQL `OFFSET` to return the contents of a hypothetical "page" of results in the database. Used most effectively when paired with `limit`.

A full list of all API Builder's parameters are specified in the [Parameter Reference](#api-parameter-reference) section of this Documentation.


###Returned JSON

All data returned by your API is wrapped in a `json` object with a `data` array. If there is an error, or no results are found, an `error` variable with a corresponding error message will be returned __instead__ of a `data` property. If your API is setup incorrectly in you `api.php` page a `config_error` array is returned. 

Inside the `data` property is an array of objects that are returned as a result of the URL parameters that will be outlined shortly.

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

Because the API outputs data using `JSON` the results of an API http request can be loaded into a project written in almost any language. I have chosen to provide brief code examples using `PHP`, however, these code snippets outline the basics of loading and using your your data and easily apply to another language. 


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

Often requests to the api return no results because no results were found that met the request's criteria. For this reason it is important to know how to handle the the api `error`. The `JSON` that is returned in this instance is `{"error": "no results found"}`.

Handling `errors` is simple. All that you need to do is check if the `error` property exists in the resulting `JSON` object. If it does execute the code for when an error is present. Otherwise, continue with the program because the request returned at least one result object.

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

##API Parameter Reference

This section documents in detail all of the API Builder URL parameters currently available to use when making an http request to your API.

It uses a made up table with a setup that is specifed [earlier in this documentation](COME BACK). 

###Column Parameters
Column parameters allow you to query data for a specific value where the parameter key is specified to be the column in your database. Column parameters can be stacked for more specific queries.

Parameter __keys:__ Column name (i.e. `first_name`) to perform query on.

Parameter __values:__ 
Desired lookup `string`, `int`, or `float` that corresponds to the column name in the database as specified by the parameter's key.

__Example:__

      http://fakeorganization.com/api.php?last_name=thompson&limit=10
      
This example request would return up to 10 users who's last name are thompson ordered alphabetically by last name.

__Notes:__ The column parameter's are overridden if a `search` parameter is specified. 

###Search Parameter
The `search` parameter uses a  MySQL `FULLTEXT` [Match()… Against()…](http://dev.mysql.com/doc/refman/5.5/en/fulltext-search.html#function_match) search to find the most relevant results to the searched string. 

Parameter __key:__ `search`

Parameter __value:__ desired query `string`

__Example:__

	http://fakeorganization.com/api.php?search=design

__Notes:__ `search` results are automatically ordered by relevancy, or if relevancy is found to be arbitrary, by `timestamp`. The `order_by` parameter cannot be used when the `search` parameter is specified. More on why below…

Default Match()…Against()… MySQL statements search databases using a 50% similarity threshold. This means that if a searched string appears in more than half of the rows in the database the search will ignore it. Because it is possible that webpages will have similar tags, I have built the api to automatically re-search `IN BOOLEAN MODE` if no results are found in the first try. If results are found in the second search they are ordered by `timestamp`.

###Exact Parameter

The exact parameter is used in conjunction with the column parameters and specifies whether or not their values are queried with relative or exact accuracy. If not included in the URL request the `exact` parameter defaults to `false`.

Parameter __key:__ `exact`

Parameter __values:__ `TRUE	` and `FALSE`

__Example:__

	http://fakeorganization.com/api.php?id=10&exact=TRUE

This request will limit the returned results to the users whose id is __exactly__ 10. If the `exact` parameter was not specified, or was set to `FALSE`, the same request could also return users whose id's have 10 in them (i.e. 1310, 10488, 100 etc…). including `exact=true` parameter in an api http request is equivalent to a `MySQL` `LIKE` statement.
	
__Notes:__ `exact`'s values are case insensitive.

###Exclude Parameter

The exclude parameter is used in conjunction with the column parameters to exclude one or more specific webpage's from a query.

Parameter __key:__ `exclude`

Parameter __values:__ a comma-delimited list of excluded users `id`'s

__Example:__

	http://fakeorganization.com/api.php?email=@gmail.com&exclude=5,137,1489&limit=50

This example will return 50 users other than numbers `5`, `137`, and `1489` who use gmail ordered by last name. 

###Order By Parameter

This parameter is used with the column parameters to sort the returned users by the specified value. If `order_by` is not specified its value defaults to the value set by the API's `API::set_default_order()` in the `api.php` page. Order by will be ignored when the `search` parameter is specified.

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

Parameter __value:__ `int` between `1-250` or between `1` and the max value specified by the `api.php` page's `API::set_max_output_number()`.

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

For instance, in the unlikely example that all users had "programming" in their bios, setting `page=1` would return webpages with id's `1-7`, setting `page=2` would yield `8-14`, etc…

__Note:__ The MySQL `OFFSET` is calculated server side by multiplying the value of `limit` by the value of `page` minus one. 

###Count Only Parameter

The `count only` parameter differs from all of the other API Builder parameters as it __does not__ return an array of result objects. Instead, it returns a single object as the first element in the `data` array. This object has only one property, `count`, where the corresponding `string` value describes the number of results returned by the rest of the url parameters. If the `count_only` parameter is not specified the default value is `FALSE`. When `count_only` is set to `TRUE` the request will __only__ evaluate and return the number of results found by the rest of the url parameters and the request __will not__ return any user data.

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


##License and Credit

The API Builder PHP Mini Library is developed and maintained by [Brannon Dorsey](http://brannondorsey.com) and is published under the [MIT License](license.txt). If you notice any bugs, have any questions, or would like to help me with development please submit an issue or pull request, write about it on the wiki, or [contact me](mailto:brannon@brannondorsey.com).