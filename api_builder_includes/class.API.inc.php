<?php
require_once("class.Database.inc.php");

class API {

	public $columns_to_provide;
	public $columns_to_provide_array;

	//default API access 
	protected $private = false;

	//API key default properties
	protected $API_key_required          = false;
	protected $API_key_column_name       = "API_key";
	protected $API_hit_count_column_name = "API_hits";
	protected $API_hit_date_column_name  = "API_hit_date";
	protected $hits_per_day              = 1000;

	//limit default properties
	protected $default_output_limit = 25;
	protected $max_output_limit     = 250;

	//order and flow default properties
	protected $default_order_by;
	protected $default_flow = "DESC";

	//search default properties
	protected $search_allowed = false;
	protected $default_search_order_by;

	//default pretty print property
	protected $pretty_print = true;

	//default exclude properties
	protected $exclude_allowed = false;
	protected $exclude_column = "id";

	//default no results message
	protected $no_results_message = "no results found";

	protected $search_in_boolean_mode   = false; //used inside of form query for FULLTEXT searches
	protected $search_has_been_repeated = false; //used to keep track if search has been repeated
	protected $private_key;
	protected $API_key;
	protected $full_text_columns;
	protected $full_text_columns_array;
	protected $search = "";
	protected $config_errors;
	
	/**
	 * Instantiates API object and creates MySQLi database connection
	 * @param string $host The hostname where the database will be running (often "localhost");
	 * @param string $db The database name
	 * @param string $table The table name the api will use
	 * @param string $username The username for the database
	 * @param string $password The password for the database
	 */
	public function __construct($host, $db, $table, $username, $password){
		$this->config_errors = array();
		if(!Database::init_connection($host, $db, $table, $username, $password)){
			$json_obj = new StdClass();
			$json_obj->config_errors[] = "database connection failed, make sure the connection info passed into API::__construct(\$host, \$db, \$table, \$username, \$password) is correct";
			die(json_encode($json_obj));
		} ;
	}

	//-------------------------------Setup Methods-------------------------------------

	/**
	 * Tells the API object which column values to use when outputing results objects
	 * @param  String $columns Comma-delimited list of column names for API to output data from
	 * @return void
	 */
	public function setup($columns){
		$this->columns_to_provide = $this->format_comma_delimited($columns);
		$this->columns_to_provide_array = explode(', ', $this->columns_to_provide);
	}

	/**
	 * Set the default column for the API to order results by if no 'order_by' parameter is specified in the request 
	 * @param string $column Name of the column for default order by
	 */
	public function set_default_order($column){
		$this->default_order_by = (string) $column;
	}

	/**
	 * Set the default columns for the API to order URL `search` parameter results by if the MySQL FULLTEXT Match...Against statement is executed in boolean mode 
	 * @param string $column
	 */
	public function set_default_search_order($column){
		$this->default_search_order_by = (string) $column;
	}

	/**
	 * Makes the API private so that only users with the SHA1 passed as the $private_key variable can access the API's data by specifying the same SHA1 as a private_key URL parameter in the http request
	 * Takes the SHA1 private key to authorize each request as parameter 
	 * @param string $private_key 40 character SHA1
	 */
	public function set_private($private_key){
		$private_key = (string) $private_key;
		if(strlen($private_key) != 40) $this->config_errors[] = "API::set_private() parameter must be a 40 character SHA1 string";
		$this->private_key = $private_key;
		$this->private = true;
	}

	/**
	 * Set the default flow if none is specified in the request. 
	 * @param string $flow Default flow to output results in
	 */
	public function set_default_flow($flow){
		if(strtoupper($flow) == "ASC" ||
		   strtoupper($flow) == "DESC")
		$this->default_flow = strtoupper($flow);
	}

	/**
	 * Set the number of api hits per API key per day
	 * @param int $number_hits_per_day Number of hits allowed each day per api key
	 */
	public function set_hit_limit($number_hits_per_day){
		$this->hits_per_day = (int) $number_hits_per_day;
	}

	/**
	 * Sets the default output limit
	 * Sets the number of JSON result objects each API request will output if no 'limit' parameter is included in the request
	 * @param int $default_output
	 * @return void
	 */
	public function set_defualt_output_number($default_output){
		$this->default_output_limit = (int) $default_output;
	}

	/**
	 * Sets the max output results allowed per request
	 * Sets the max number of JSON result objects each API request is allowed output
	 * @param int $max_output
	 * @return void
	 */
	public function set_max_output_number($max_output){
		$this->max_output_limit = (int) $max_output;
	}

	/**
	 * Sets the default JSON output as human readable formatted
	 * @param boolean $boolean
	 * @return void
	 */
	public function set_pretty_print($boolean){
		$this->pretty_print = (boolean) $boolean;
	}

	/**
	 * Makes API require a unique key for each request.
	 * @param  boolean $boolean
	 * @param  string $users_table_name Optional parameter that defines the name of the table in the database to be used to store information about the users making the requests. If none is specified "users" will be used.
	 * @param  string $key_column_name Optional parameter that defines the name of the column in the database to be used for the API key. If none is specified "API_key" will be used.
	 * @param  string $hit_count_column_name Optional parameter that defines the name of the column in the database to be used for the API hit count. If none is specified "API_hits" will be used.
	 * @param  string $hit_date_column_name Optional parameter that defines the name of the column in the database to be used for the API hit date. If none is specified "API_hit_date" will be used.
	 * @return void
	 */
	public function set_key_required($boolean, $users_table_name=false, $key_column_name=false, $hit_count_column_name=false, $hit_date_column_name=false){
		$this->API_key_required = (boolean) $boolean;
		if($users_table_name) Database::$users_table = (string) $users_table_name;
		if($key_column_name) $this->API_key_column_name = $key_column_name;
		if($hit_count_column_name) $this->API_hit_count_column_name = $hit_count_column_name;
		if($hit_date_column_name) $this->API_hit_date_column_name = $hit_date_column_name;
	}

	public function set_exclude_allowed($boolean, $column_name=false){
		$this->exclude_allowed = (boolean) $boolean;
		if($column_name) $this->exclude_column = $column_name;
	}

	/**
	 * Enables the API 'search' parameter and specifies which columns can be searched
	 * Note: $columns must be Fulltext enabled in your database's table structure
	 * @param string $columns Comma-delimited list of column names 
	 * @return void/boolean
	 */
	public function set_searchable($columns){
		$this->full_text_columns = $this->format_comma_delimited($columns);
		$this->full_text_columns_array = explode(', ', $this->full_text_columns);
		$columns_correct = true;

		//make sure that all $columns have
		//1. been setup using API::setup()
		//2. are FULLTEXT enabled in the db
		//3. have the TEXT data type in the db
		
		//1. check to make sure that each column specified has been specified using API::setup()
		foreach($this->full_text_columns_array as $full_text_column){
			//if this column name is NOT a column name specified in setup...
			if(!$this->is_column_parameter($full_text_column, $this->columns_to_provide_array)){
				$columns_correct = false;
				$this->config_errors[] = "the '$full_text_column' column was specified in API::set_searchable() but was not included in the list of columns passed into API::setup()";
				break;
			}
		}

		//2.
		$query = "SELECT GROUP_CONCAT( DISTINCT column_name SEPARATOR  ', ' ) FROM information_schema.STATISTICS WHERE table_schema = '" . Database::$db . "' AND table_name =  '"
		 . Database::$table . "' AND index_type =  'FULLTEXT'";
		
		//get the column names that are FULLTEXT from the db
		if($full_text_columns_from_db_array = $this->get_column_names_from_db($query)){
			//loop through the columns that are specified with the API::set_searchable() $column parameter
			foreach($this->full_text_columns_array as $column_name){
				//if one of the columns set with API::set_searchable() is not included in the FULLTEXT schema from the db...
				if(!in_array($column_name, $full_text_columns_from_db_array)){
					//run error
					$columns_correct = false;
					$this->config_errors[] = "the column '$column_name' is specified in API::set_searchable() but is not indexed as FULLTEXT in your database";
					break;
				}
			}
		}

		//3.
		$query = "SHOW COLUMNS FROM " . Database::$table . " FROM " . Database::$db;
		if($results = Database::get_all_results($query)){
			$text_columns = array();
			foreach ($results as $column) {
				if($column["Type"] == "text") $text_columns[] = $column['Field'];
			}
			//same as 2...
			foreach($this->full_text_columns_array as $column_name){
				if(!in_array($column_name, $text_columns)){
					$columns_correct = false;
					$this->config_errors[] = "the column '$column_name' is specified in API::set_searchable() but it does not have the required data type 'Text' in your database";
					break;
				}
			}
		}
		
		if($columns_correct){
			$this->search_allowed = true;
			return true;
		} else return false;
	}

	/**
	 * Sets the error com_message_pump() when no results are found in a request
	 * Note: It is recommended to use a lower case $message string per this API class error standard
	 * @param string $message The error value to use when no results are found
	 */
	public function set_no_results_message($message){
		$this->no_results_message = (string) $message;
	}

	//-------------------------------Other Methods-------------------------------------


	/**
	 * Returns valid JSON results from API parameters
	 * This is the method that makes the api output it's results. Array must be sanitized before using this function
	 * @param  array $get_array An assosciative array of API parameter names as keys 
	 * @return string A JSON string representing the request's results
	 */
	public function get_json_from_assoc(&$get_array){

		$json_obj = new StdClass();
		$pretty_print = $this->pretty_print;

		if(!$this->find_config_errors()){

			if(isset($get_array['pretty_print'])){
				if(strtolower($get_array['pretty_print']) == "true") $pretty_print = true;
				if(strtolower($get_array['pretty_print']) == "false") $pretty_print = false;  
			}

			//if API is public or if API is private and a correct private key was provided
			if(!$this->private ||
				$this->private &&
				isset($get_array['private_key']) &&
				$this->private_key == $get_array['private_key']){

				$query = $this->form_query($get_array);
				if($this->check_API_key()
					|| !$this->API_key_required){
					//if search was included as a parameter in the http request but it isn't allowed in the api's config...
					if(isset($get_array['search']) &&
					   !$this->search_allowed){
						$json_obj->error = "search parameter not enabled for this API";
					}
					else if(isset($get_array['exclude']) &&
						!$this->exclude_allowed){
						$json_obj->error = "exclude parameter not enabled for this API";
					}else{
						if($results_array = Database::get_all_results($query)){

					 		if(is_array($results_array)){
					 			// deletes key => value pairs if the value is empty. Only works if array is nested: 
					 			// http://stackoverflow.com/questions/5750407/php-array-removing-empty-values	
					 			$results_array = array_filter(array_map('array_filter', $results_array));

					 			foreach($results_array as $result_array){
					 				foreach($result_array as $key => $value){
					 					if($key == "COUNT(*)"){
					 						$count = $value;
					 						break;
					 					}
					 				}
					 			}
					 			if(!isset($count)) $json_obj->data = $results_array;
					 			else $json_obj->count = $count; 
					 			//COME BACK need to make count only parameter work
					 		}
				 		}else $json_obj->error = "no results found";
				 	}

					//only attempt to increment the api hit count if this method is called from a PUBLIC API request
					if($this->API_key_required){
						$query = "SELECT " . $this->API_hit_date_column_name . " FROM " . Database::$users_table . " WHERE " . $this->API_key_column_name . " = '" . $this->API_key . "' LIMIT 1";
						$result = Database::get_all_results($query);
						//increments the hit count and/or hit date OR sets the error message if the key has reached its hit limit for the day
						if($this->update_API_hits($this->API_key, $result[0][$this->API_hit_date_column_name]) === false){
						 $json_obj->error = "API hit limit reached";
				   		}
					 }
				}else $json_obj->error = "API key is invalid or was not provided";
				//if there was a search and it returned no results
				if($this->search != "" &&
					!$this->search_has_been_repeated &&
					isset($json_obj->error) &&
					strstr($json_obj->error, $this->no_results_message) == true){
						$this->search_in_boolean_mode = true; //set search in boolean mode to true
						$this->search_has_been_repeated = true; //note that the search will now have been repeated
					 	//$this->JSON_string = $this->get_json_from_assoc($get_array, $object_parent_name); //recurse the function (thus re-searching)
					 	return $this->get_json_from_assoc($get_array); //recurse the function (thus re-searching)
				}
			}else{ //API is private but private_key is invalid or was not provided
				$json_obj->error = "this API is private and the private key was invalid or not provided";
			}
		}else{ //config errors were present
			$pretty_print = true; //always output errors in pretty print for readability
			$json_obj->config_error = $this->config_errors;
		}
		return ($pretty_print && version_compare(PHP_VERSION, '5.4.0') >= 0) ? json_encode($json_obj, JSON_PRETTY_PRINT) : json_encode($json_obj);
	}

	//need to put call_limit_reached inside of update_API_hits or something.
	//fails to reset api calls when the call once the call limit has been reached.

	/**
	 * Handles the incrementing of a key's API hits.
	 * Returns false if the user's key has maxed out hits for the day
	 * @param  string $API_key
	 * @param  string $API_hit_date
	 * @return void/false
	 */
	public function update_API_hits($API_key, $API_hit_date){
		//if the api has already been hit today
		if(date('Ymd') == date('Ymd', strtotime($API_hit_date))){
			//make sure that the key hasn't hit its limit.
			//if it has return false
			if(!$this->call_limit_reached($API_key)){
				$query = "UPDATE " . Database::$users_table . " SET " . $this->API_hit_count_column_name . "=" . $this->API_hit_count_column_name . "+1 WHERE " . $this->API_key_column_name . "='" . $API_key . "'";
				Database::execute_sql($query);
			}else return false;
		}else{ //if the API has not been hit today set the hits to zero and the hit date to today
			$now = new DateTime();
			$query = "UPDATE " . Database::$users_table . " SET " . $this->API_hit_count_column_name . " = 0, " . $this->API_hit_date_column_name . "='" . $now->format(DateTime::ISO8601) . "' WHERE " . $this->API_key_column_name . "='" . $API_key . "'";
			Database::execute_sql($query);
		}
	}

	/**
	 * Adds config errors and returns boolean representing their presence
	 * @return boolean
	 */
	protected function find_config_errors(){
		if(!isset($this->default_order_by)) $this->config_errors[] = "a default order must be specified using API::set_default_order()";
		if(!isset($this->default_search_order_by) && $this->search_allowed){
			$this->config_errors[] = "a default search order must be specified using API::set_default_search_order() if search is enabled with API::set_searchable()";
		}
		if(!isset($this->columns_to_provide)) $this->config_errors[] = "output columns must be specified using API::setup()";
		if(!empty($this->config_errors)){ 
			return true; //errors exist
		}else return false; //errors don't exist
	}

	//builds a dynamic MySQL query statement from a $_GET array. Array must be sanitized before using this function.
	protected function form_query(&$get_array){

		$column_parameters = array();
		$this->search = "";
		$order_by = "";
		$flow = "";
		$limit = "";
		$page = 1;
		$exact = false;
		$count_only = false;
		$exclude = array();
		$this->API_key = "";

		//distribute $_GETs to their appropriate arrays/vars
		foreach($get_array as $parameter => $value){
			if($this->is_column_parameter($parameter, $this->columns_to_provide_array)){ 
				$column_parameters[$parameter] = $value;
			}
			else if($parameter == 'search' && $this->search_allowed) $this->search = $value;
			else if($parameter =='order_by') $order_by = $value;
			else if($parameter == 'flow') $flow = $value;
			else if($parameter == 'limit') $limit = $value;
			else if($parameter == 'page') $page = (int) $value;
			else if($parameter == 'exact' &&
				    strtolower($value) == "true") $exact = true;
			else if($parameter == 'count_only' &&
				    strtolower($value) == "true" ||
				    $parameter == 'count_only' &&
				    $value == true){
				$count_only = true;
			}
			else if($parameter == 'exclude' && $this->exclude_allowed) $exclude = explode(",", $value);
			else if($parameter == 'key') $this->API_key = $value; 
		}

		$match_against_statement = 'MATCH (' . $this->full_text_columns . ') AGAINST (\'' . $this->search . '\' IN BOOLEAN MODE) ';
		if($count_only) $query = "SELECT COUNT(*)";
		else $query = "SELECT " . $this->columns_to_provide;
		if($this->search != ""){
			//if the search is not supposed to be in boolean mode remove IN BOOLEAN MODE from $match_against_statment
			if(!$this->search_in_boolean_mode) $match_against_statement = str_replace(" IN BOOLEAN MODE", "", $match_against_statement);
		 	$query .= ", " . $match_against_statement . "AS score";
		}
		$query .= " FROM "  . Database::$table ." ";

		//if search was a parameter overide column paramters and use MATCH...AGAINST
		if($this->search != ""){
			$this->append_prepend($this->search, "'");
			$query .= "WHERE $match_against_statement ORDER BY ";
			//order by score if it is the first FULLTEXT (natural mode) search and order by likes if it is the fallback boolean mode search
			$query .= ( $this->search_has_been_repeated ? $this->default_search_order_by . " DESC " : "score DESC ");  
		}
		//if search was not specified as a parameter used use LIKE
		else{
			//add WHERE statements
			if(sizeof($column_parameters) > 0){
				$i = 0;
				$query .= "WHERE ";
				foreach ($column_parameters as $parameter => $value) {
					//if exact parameter was specified as TRUE 
					//or column parameter is id search by = not LIKE
					if($parameter == 'id' || $exact){
						$this->append_prepend($value, "'");
					 	$query .= "$parameter = $value ";
					}
					else $query .= "$parameter LIKE '%$value%' ";
					if($i != sizeof($column_parameters) -1) $query .= "AND ";
					$i++;
				}
				//if there was an exclude parameter exclude each comma seperated value
				//exclude cannot be used in a FULLTEXT search as of now
				if(!empty($exclude) && $this->exclude_allowed){
					foreach($exclude as $excluded_id)
					$query .= "AND " . $this->exclude_column . " !='" . $excluded_id . "' ";

				}
			}
		
			//add ORDER BY statement
			$order_by_string;
			if($order_by != "" &&
			$this->is_column_parameter($order_by, $this->columns_to_provide_array)){
				$order_by_string = "ORDER BY $order_by ";
			}
			else $order_by_string = "ORDER BY " . $this->default_order_by . " ";
			$query .= $order_by_string;

			//add FLOW statement
			$flow_string;
			$flow = strtoupper($flow);
			if($flow != "" &&
			$flow == 'ASC' ||
			$flow == 'DESC'){
				$flow_string = "$flow ";
			}
			else $flow_string = $this->default_flow . " ";
			$query .= $flow_string;
		}
		//only add LIMIT of it is not a COUNT query
		if(!$count_only){
			//add LIMIT statement
			$limit_string;
			if($limit != ""){
				$limit = (int) $limit;
				if((int) $limit > $this->max_output_limit) $limit = $this->max_output_limit;
				if((int) $limit < 1) $limit = 1;
				$limit_string = "LIMIT $limit";
			} 
			else{
				$limit = $this->default_output_limit;
				$limit_string = "LIMIT $limit";	
			} 
			$query .= $limit_string;
		}

		//add PAGE statement
		if($page != "" &&
			$page > 1){
			$query .= " OFFSET " . $limit * ($page -1);
		}

		//echo $query . "<br/>";
		return $query;
	}

	protected function check_API_key(){
		//return true; //bypass API key
		$API_key_query = "SELECT id FROM " . Database::$users_table . " WHERE " . $this->API_key_column_name . "='" . $this->API_key ."' LIMIT 1";
		//if the key was provided and it is the right length test it

		if($this->API_key != "" &&
			strlen($this->API_key) == 40){
			$results = Database::get_all_results($API_key_query);
			if($results &&
				count($results) > 0){
				//add insert SQL statement here to keep track of api hits 
			 return true;
			}
			else return false;
		}
		//if the api key wasnt provided or isn't the right length return before querying
		else return false;
	}

	//boolean that determines if API call limit has been reached
	protected function call_limit_reached($API_key){
		$query = "SELECT " . $this->API_hit_count_column_name . " FROM " . Database::$users_table . " WHERE " . $this->API_key_column_name . "='" . $API_key . "' LIMIT 1";
		$results = Database::get_all_results($query);
		$API_hits = $results[0][$this->API_hit_count_column_name];
		return (intval($API_hits) >= $this->hits_per_day) ? true : false;
	}

//------------------------------------------------------------------------------
//HELPERS

	//appends and prepends slashes to string for WHERE statement values
	protected function append_prepend(&$string, $char){
		$string = $char . $string . $char;
	}

	//checks if a parameter string is also the name of a SELECT statement's requested column
	protected function is_column_parameter($parameter_name, $columns_to_provide_array){
		return in_array ($parameter_name, $columns_to_provide_array);
	}

	protected function format_comma_delimited($list){
		$array = explode(",", $list);
		$string_to_return = "";
		foreach($array as $list_item){
			$string_to_return .= trim($list_item) . ", ";
		}
		return rtrim($string_to_return, ", ");
	}

	//used inside API::set_searchable().
	//returns a comma-space dilimited list of column names based on query
	protected function get_column_names_from_db($query){
		if($result = Database::get_all_results($query)){
			$result = $result[0]; 
			$key = current(array_keys($result));
			//return an array of columns that are FULLTEXT enabled from the database
			return explode(", ", $result[$key]); 
		}else return false;
	}

}

?>