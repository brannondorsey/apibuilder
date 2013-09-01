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

##Customizing your API


##API Parameter Reference

This section documents in detail all of the API Builder URL parameters currently available to use when making an http request to your api.

For this reference an example database will be used. This example table named `users` holds information about imaginary users that belong to an organization. The setup for this imaginary api is as follows:

```php
$api = new $API("localhost", "organization", "users", "username", "secret_password");
$api->setup("id, first_name, last_name, email, phone_number, city, state, bio");
$api->set_default_order_by("last_name");
$api->set_searchable("first_name, last_name, email, city, state, bio");
$api->set_pretty_print(true);

``` 

###Column Parameters
Column parameters allow you to query data for a specific value where the parameter key is specified to be the column in your database. Column parameters can be stacked for more specific queries.

Parameter __keys:__ Column name (i.e. `first_name`) to perform query on.

Parameter __values:__ 
Desired lookup `string`, `int`, or `float` that corresponds to the column name in the database as specified by the parameter's key.

__Example:__

      http://fakeorganization.com/api.php?last_name=thompson&limit=10
      
This example piggybacks off of the [example request](#example-request) used in the [Getting Started](#getting-started) section of this documentation. This request would yield more accurate results if you were looking for webpages where the domain is wired.com. The previous method using the `search` parameter would have given any results that include wired.com somewhere in the website's metadata.

If you were looking for the 10 most recent wired.com visits that came from facebook the http request would look like this:

     http://yourdomain.com/subfolder/server/src/api.php?domain=wired.com&referrer=facebook.com&limit=10

__Notes:__ The column parameter's are overridden if a `search` parameter is specified. 

###Search Parameter
The `search` parameter uses a  MySQL `FULLTEXT` [Match()… Against()…](http://dev.mysql.com/doc/refman/5.5/en/fulltext-search.html#function_match) search to find the most relevant results to the searched string. 

Parameter __key:__ `search`

Parameter __value:__ desired query `string`

__Example:__

	http://yourdomain.com/subfolder/server/src/api.php?search=design

__Notes:__ `search` results are automatically ordered by relevancy, or if relevancy is found to be arbitrary, by `timestamp`. The `order_by` parameter cannot be used when the `search` parameter is specified. More on why below…

Default Match()…Against()… MySQL statements search databases using a 50% similarity threshold. This means that if a searched string appears in more than half of the rows in the database the search will ignore it. Because it is possible that webpages will have similar tags, I have built the api to automatically re-search `IN BOOLEAN MODE` if no results are found in the first try. If results are found in the second search they are ordered by `timestamp`.

###Exact Parameter

The exact parameter is used in conjunction with the column parameters and specifies whether or not their values are queried with relative or exact accuracy. If not included in the url request the `exact` parameter defaults to `false`.

Parameter __key:__ `exact`

Parameter __values:__ `TRUE	` and `FALSE`

__Example:__

	http://yourdomain.com/subfolder/server/src/api.php?length_visited=1000&exact=TRUE&limit=5

This request will limit the returned results to webages whose length_visited is __only__ 1 second. If the `exact` parameter was not specified, or was set to `FALSE`, the same request could also return webpages whose length_visited have a trailing 1 second (i.e. 11 seconds, 131 seconds, etc…). including `exact=true` parameter in an api http request is equivalent to a `MySQL` `LIKE` statement.
	
__Notes:__ `exact`'s values are case insensitive.

###Exclude Parameter

The exclude parameter is used in conjunction with the column parameters to exclude one or more specific webpage's from a query.


Parameter __key:__ `exclude`

Parameter __values:__ a comma-delimited list of excluded webpage's `id`'s

__Example:__

	http://yourdomain.com/subfolder/server/src/api.php?domain=brannondorsey.com&exclude=5,137,1489&limit=50

This example will return the first 50 users other than numbers `5`, `137`, and `1489` whose domain includes brannondorsey.com. 

###Order By Parameter

This parameter is used with the column parameters to sort the returned users by the specified value. If `order_by` is not specified its value defaults to `timestamp`. Order by cannot be used when the `search` parameter is specified.

Parameter __key:__ `order_by`

Parameter __value:__ Column name (i.e. `length_visited`) to order by

__Example:__

	http://yourdomain.com/subfolder/server/src/api.php?domain=buzzfeed.com&order_by=length_visited&limit=15

This request returns the 15 longest visited buzzfeed webpages.

###Flow Parameter

This parameter specifies the MySQL `ASC` and `DESC` options that follow the `ORDER BY` statement. If `flow` is not specified it defaults to `DESC`.

Parameter __key:__ `flow`

Parameter __value:__ `ASC` or `DESC`

__Example:__

	http://yourdomain.com/subfolder/server/src/api.php?keywords=virtual%20reality&order_by=title&flow=ASC
	
This request specifies that the results should be ordered in an `ASC` fashion. It would return webpages whose keywords include "virtual reality" in a reverse alphabetical order by title.
		
__Notes:__ `flow`'s values are case insensitive.

###Limit Parameter

The `limit` parameter works similarly to MySQL `LIMIT`. It specifies the max number of users to be returned. The default value, if unspecified is `25`. The max value of results that can be returned in one request is `250`.

Parameter __key:__ `limit`

Parameter __value:__ `int` between `1-250`

__Example:__

	http://yourdomain.com/subfolder/server/src/api.php?referrer=amazon.com&limit=5

Returns the 5 most recent webpages referred from amazon.com.

	
###Page Parameter

The page parameter is used to keep track of what set (or page) of results are returned. This is similar to the [MySQL OFFSET statement](http://dev.mysql.com/doc/refman/5.0/en/select.html). If not specified the page value will default to `1`.

Parameter __key:__ `page`

Parameter __value:__ `int` greater than `0`

__EXAMPLE:__ 

	http://yourdomain.com/subfolder/server/src/api.php?search=zombie&limit=7&page=3&order_by=length_visited&flow=asc	
This request will return the 3rd "page" of `search` results. 

For instance, in the absurd example that all webpages had "zombie" as a keyword, setting `page=1` would return webpages with id's `1-7`, setting `page=2` would yield `8-14`, etc…

__Note:__ The MySQL `OFFSET` is calculated server side by multiplying the value of `limit` by the value of `page` minus one. 

###Count Only Parameter

The `count only` parameter differs from all of the other Indexd API parameters as it __does not__ return an array of user objects. Instead, it returns a single object as the first element in the `data` array. This object has only one property, `count`, where the corresponding `int` value describes the number of results returned by the rest of the url parameters. If the `count_only` parameter is not specified the default value is `FALSE`. When `count_only` is set to `TRUE` the request will __only__ evaluate and return the number of results found by the rest of the url parameters and the request will not return any user data.

Parameter __key:__ `count_only`

Parameter __values:__ `	TRUE` or `FALSE`

__EXAMPLE:__

     //request
     http://yourdomain.com/subfolder/server/src/api.php?domain=google.com&count_only=true
     
     //returns
     {
      "data":[
        {
        "count":"701"
        }]
     }
     
This request returns the number of webpages that have "google.com" as the domain. The count value is returned as __a `string`__.

__Note:__ The value of `count_only` is case insensitive.


##Troubleshooting

Permissions on server are correct?

##License and Credit

The Quartzite project is developed and maintained by [Brannon Dorsey](http://brannondorsey.com) and is published under the [MIT License](license.txt). If you notice any bugs, have any questions, or would like to help me with development please submit an issue or pull request, write about it on our wiki, or [contact me](mailto:brannon@brannondorsey.com).