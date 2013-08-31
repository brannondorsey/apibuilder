<?php
require_once("class.Database.inc.php");

class API {

	public $columns_to_provide;

	protected $API_key_required = false;
	protected $default_output_limit = 25;
	protected $max_output_limit = 250;
	protected $hits_per_day = 1000;
	protected $default_order_by = "ORDER BY timestamp ";
	protected $default_flow = "DESC ";
	protected $search_in_boolean_mode = false; //used inside of form query for FULLTEXT searches
	protected $search_has_been_repeated = false; //used to keep track if search has been repeated
	protected $search = "";
	protected $no_results_message = "no results found";
	protected $full_text_columns;
	protected $API_key;
	
	public function __construct($host, $db, $table, $username, $password){
		Database::init_connection($host, $db, $table, $username, $password);
		$this->columns_to_provide = 
			"id, timestamp, filename, title, domain, url, referrer, ip, forward_from, author, owner, description, keywords, copywrite";
		$this->full_text_columns = "url, description, keywords";
	}

	//Returns a valid JSON string from $_GET values. Array must be sanitized before using this function.

	public function get_JSON_from_GET(&$get_array){

		$json_obj = new StdClass();
		$pretty_print = false;

		if(isset($get_array['pretty_print']) && 
		   strtolower($get_array['pretty_print']) == "true"){
			$pretty_print = true;
		}

		$query = $this->form_query($get_array);
		if($this->check_API_key()){
			//$this->JSON_string = $this->query_results_as_array_of_JSON_objs($query, $object_parent_name, true);

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

			//only attempt to increment the api hit count if this method is called from a PUBLIC API request
			if($this->API_key_required){
				$query = "SELECT API_hit_date FROM " . Database::$table . " WHERE API_key = '" . $this->API_key . "' LIMIT 1";
				$result = Database::get_all_results($query);
				//increments the hit count and/or hit date OR sets the error message if the key has reached its hit limit for the day
				if($this->update_API_hits($this->API_key, $result[0]['API_hit_date']) === false){
				 $json_obj->error = "API hit limit reached";
		   		}
			 }
		}
		else $json_obj->error = "API key is invalid or was not provided";
		//if there was a search and it returned no results
		if($this->search != "" &&
			!$this->search_has_been_repeated &&
			strstr($this->JSON_string, $this->no_results_message) != false){
				$this->search_in_boolean_mode = true; //set search in boolean mode to true
				$this->search_has_been_repeated = true; //note that the search will now have been repeated
			 	//$this->JSON_string = $this->get_JSON_from_GET($get_array, $object_parent_name); //recurse the function (thus re-searching)
			 	return $this->get_JSON_from_GET($get_array); //recurse the function (thus re-searching)
			}
		return ($pretty_print) ? json_encode($json_obj, JSON_PRETTY_PRINT) : json_encode($json_obj);
	}


	// public function query_results_as_array_of_JSON_objs($query, $object_parent_name=NULL, $b_wrap_as_obj=false){
	// 	$JSON_output_string = "";
	// 	//if there were results output them as a JSON data obj
	// 	//echo "the parent name is " . $object_parent_name . " and the boolean is " . $b_wrap_as_obj;
	// 	if($results_array = Database::get_all_results($query)){
	// 			//if the objects being output should be wrapped in an object specified by the parameters of this function
	// 			if($object_parent_name != NULL && $b_wrap_as_obj){
	// 			 	$JSON_output_string = '{"' . $object_parent_name . '":[';
	// 			 }
	// 			$JSON_output_string .= $this->output_objects($results_array);
	// 			//see above
	// 			if($object_parent_name != NULL && $b_wrap_as_obj){
	// 				$JSON_output_string .= ']}';
	// 			}
	// 		}
	// 	//if no results were found return a JSON error obj
	// 	else $JSON_output_string = $this->get_error($this->no_results_message);
	// 	return iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($JSON_output_string)); //make sure all database results are UTF-
	// }

	// //outputs JSON error object with error message argument
	// public function get_error($error_message){
	// 	return "{\"error\": \"$error_message\"}";
	// }

	//outputs JSON object from 1D or 2D MySQL results array
	protected function output_objects($mysql_results_array){
		$JSON_output_string = "";
		$count_only_key = "count"; //change default COUNT(*) key name
		if(isset($mysql_results_array[0])){
			$i = 0;
			foreach ($mysql_results_array as $user_row) {
				$JSON_output_string .= "{";
				foreach($user_row as $key => $value){
					if($value != ""){
						if($key == "COUNT(*)") $key = $count_only_key;
						$JSON_output_string .= '"' . $key . '"' . ':';
						$JSON_output_string .= '"' . $value . '"';
						$JSON_output_string .= ',';
					}
				}
				$JSON_output_string = trim($JSON_output_string, ",");
				$JSON_output_string .= "}";
				if ($i != sizeof($mysql_results_array) -1) $JSON_output_string .= ',';
				$i++;
			}
		}
		else{
			$user_row = $mysql_results_array;
			$JSON_output_string .= "{";
				foreach($user_row as $key => $value){
					if($value != ""){
						if($key == "COUNT(*)") $key = $count_only_key;
						$JSON_output_string .= '"' . $key . '"' . ':';
						$JSON_output_string .= '"' . $value . '"';
						$JSON_output_string .= ',';
					}
				}
				$JSON_output_string = rtrim($JSON_output_string, ",");
				$JSON_output_string .= "}";
		}
		return $JSON_output_string;
	}

	//builds a dynamic MySQL query statement from a $_GET array. Array must be sanitized before using this function.
	protected function form_query(&$get_array){

		$column_parameters = array();
		$columns_to_provide_array = explode(', ', $this->columns_to_provide);
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
			if($this->is_column_parameter($parameter, $columns_to_provide_array)){ 
				$column_parameters[$parameter] = $value;
			}
			else if($parameter == 'search') $this->search = $value;
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
			else if($parameter == 'exclude') $exclude = explode(",", $value);
			else if($parameter == 'key') $this->API_key = $value; 
		}

		$match_against_statement = 'MATCH (' . $this->full_text_columns . ') AGAINST (\'' . $this->search . '\' IN BOOLEAN MODE) ';
		if($count_only) $query = "SELECT COUNT(*)";
		else $query = "SELECT " . $this->columns_to_provide;
		if($this->search != ""){
			//if the search is not supposed to be in boolean mode remove IN BOOLEAN MODE from $match_against_statment
			if(!$this->search_in_boolean_mode) $match_against_statement = str_replace("IN BOOLEAN MODE", "", $match_against_statement);
		 	$query .= ", " . $match_against_statement . "AS score";
		}
		$query .= " FROM "  . Database::$table ." ";

		//if search was a parameter overide column paramters and use MATCH...AGAINST
		if($this->search != ""){
			$this->append_prepend($this->search, "'");
			$query .= "WHERE $match_against_statement ORDER BY ";
			//order by score if it is the first FULLTEXT (natural mode) search and order by likes if it is the fallback boolean mode search
			$query .= ( $this->search_has_been_repeated ? "timestamp DESC " : "score DESC ");  
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
				if(!empty($exclude)){
					foreach($exclude as $excluded_id)
					$query .= "AND id !='" . $excluded_id . "' ";

				}
			}
		
			//add ORDER BY statement
			$order_by_string;
			if($order_by != "" &&
			$this->is_column_parameter($order_by, $columns_to_provide_array)){
				$order_by_string = "ORDER BY $order_by ";
			}
			else $order_by_string = $this->default_order_by;
			$query .= $order_by_string;

			//add FLOW statement
			$flow_string;
			$flow = strtoupper($flow);
			if($flow != "" &&
			$flow == 'ASC' ||
			$flow == 'DESC'){
				$flow_string = "$flow ";
			}
			else $flow_string = $this->default_flow;
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
		return true; //bypass API key
		$API_key_query = "SELECT id FROM " . Database::$table . " WHERE API_key='" . $this->API_key ."' LIMIT 1";
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
		$query = "SELECT API_hits FROM " . Database::$table . " WHERE API_key='" . $API_key . "' LIMIT 1";
		$results = Database::get_all_results($query);
		$API_hits = $results['API_hits'];
		return (intval($API_hits) >= $this->hits_per_day) ? true : false;
	}

	//need to put call_limit_reached inside of update_API_hits or something.
	//fails to reset api calls when the call once the call limit has been reached.

	//handles the incrementing of a key's API hits.
	//returns false if user has maxed out hits for the day
	public function update_API_hits($API_key, $API_hit_date){
		//if the api has already been hit today
		if(date('Ymd') == date('Ymd', strtotime($API_hit_date))){
			//make sure that the key hasn't hit its limit.
			//if it has return false
			if(!$this->call_limit_reached($API_key)){
				$query = "UPDATE " . Database::$table . " SET API_hits=API_hits+1 WHERE API_key='" . $API_key . "'";
				Database::execute_sql($query);
			}else return false;
		}else{ //if the API has not been hit today set the hits to zero and the hit date to today
			$now = new DateTime();
			$query = "UPDATE " . Database::$table . " SET API_hits = 0, API_hit_date='" . $now->format(DateTime::ISO8601) . "' WHERE API_key='" . $API_key . "'";
			Database::execute_sql($query);
		}
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

}

?>