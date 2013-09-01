#PHP API Builder
Easily transform MySQL tables into web accessible json APIs with this mini library for PHP.

##Getting Started
This PHP API Builder is used to build simple [REST APIs](http://en.wikipedia.org/wiki/Representational_state_transfer) from MySQL databases. With it you (or anyone if you choose to make the API pubic) can access and update data on the web through an easy-to-setup `api.php` page. Using the API parameters provided in this mini lib users can query a database through that `api.php` page using http `GET` parameters and return the results as an array of `JSON` results. A full list of available API parameters is located [below](#api-parameter-reference). 

###How it works

Setting up the API is easy! To add an API to an existing MySQL table simply place this repository's `include/` folder and `api.php` file in the directory where you want your api page to be located (If you want your api accessible at yourdomain.com/api.php you should put these files in your root directory). Then update the `api_template.php` file to reflect your database info and api setup. Then save the updated file as `api.php` or whatever you want your API page to be.

Thats it! Below is an basic example of how the `$api` object can be setup. For more info and options check out the [API Builder documentation](COME BACK).

###Example

This example illustrates how a [Quartzite API](https://github.com/brannondorsey/quartzite) could be setup. Use the `api_template.php` to create your own api.

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
	  	//the API constructor takes parameters in this order: host, database, table, username, password
	  	$api = new API("localhost", "quartzite", "metadata", "root", "root");
	  	$api->setup($columns);
	  	$api->set_default_order_by("timestamp");
	  	$api->set_searchable("url, description, keywords");
	  	$api->set_pretty_print(true);

	  	//sanitize the contents of $_GET to insure that 
	  	//malicious strings cannot disrupt your database
	 	$get_array = Database::clean($_GET);

	 	//output the results of the http request
	 	echo $api->get_JSON_from_GET($get_array);
	}
?>
```

###Download

You can direct download a .zip of API Builder by clicking [here](COME BACK).

##Customizing your API


##API Parameter Reference