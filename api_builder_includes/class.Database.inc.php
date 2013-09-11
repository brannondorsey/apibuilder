<?php

class Database {

	public static $root_dir_link = "http://localhost:8888/api_builder";
	public static $private_key;

	//MySQL database info
	public static $db;
	public static $table;
	protected static $host;	
	public static $users_table = "users";
	protected static $user;
	protected static $password;

	protected static $mysqli;

	//initialize the database connection
	public static function init_connection($host, $db, $table, $username, $password){
		self::$host = $host;
		self::$db = $db;
		self::$table = $table;
		self::$user = $username;
		self::$password = $password;
		self::$mysqli = new mysqli(self::$host, self::$user, self::$password, self::$db);
		return self::$mysqli->ping();
	}

	/**
	 * closes the database connection
	 * @return void
	 */
	public static function close_connection(){
		self::$mysqli->close();
	}
	
	//execute sql query statement. Used for INSERT and UPDATE mostly. Returns false if query fails
	public static function execute_sql($query) {
		if(self::$mysqli->query($query)) return true;
		return false;
	}

	//handles dynamic formation of INSERT and UPDATE queries from $_POST and executes them
	//post array should be cleaned before using this function
	public static function execute_from_assoc($post_array, $table_name, $set_statement=NULL){
		if($set_statement == NULL){
			$query = "INSERT INTO " . $table_name . " ("; 
			foreach($post_array as $key => $value){
				$query .= " `" . $key . "`,";
			}
			$query = rtrim($query, ",");
			$query .= ") VALUES (";
			foreach($post_array as $key => $value){
				//if($key == 'lat' || $key == 'lon' || $value == 0) $query .= " " . $value . ",";
				$query .= " '" . $value . "',";
			}
			$query = rtrim($query, ",");
			$query .= ");";
		}
		//if statement type is UPDATE, the id of the row to update was specified in the $post_array,
		//and what to update (set) was specified
		else if($set_statement != NULL &&
			 isset($post_array['id']) &&
			 !empty($post_array['id'])){
			$set_statement = trim($set_statement);
			$query = "UPDATE " . $table_name . " SET " . $set_statement . " = '" . $post_array[$set_statement]
			. "' WHERE id ='" . $post_array['id'] . "' LIMIT 1";
		}
		else{
			echo "incorrect parameters passed to InsertUpdate::execute_from_assoc()";
		 	return false;
		}
		return self::execute_sql($query);
	}
	
	//returns array of one result row if one result was found or 2D array of all returned rows if multiple were found
	public static function get_all_results($query) {
		$result_to_return = array(); //maybe this shouldnt be like this...
		if ($result = self::$mysqli->query($query)) {
				$i=0;
				while ($row = $result->fetch_assoc()) {
					$result_to_return[$i] = $row;
					$i++;	
				}
			if (count($result_to_return) >= 1) {
				return $result_to_return;
			} 
			else return false; //there were no results found
		}else echo " MYSQL QUERY FAILED";
	}

	//returns string or assosciative array of strings
	//mainly for $_POST and $_GET
	public static function clean($string){
		if(isset($string) && !empty($string)){
			$new_string_array;
			//if the string is actually an assoc array
			if(is_array($string)){
				foreach($string as $string_array_key => $string_array_value){
					$string_array_value = self::clean_string($string_array_value);
					$new_string_array[$string_array_key] = $string_array_value;
				}
				$string = $new_string_array;
			}
			//else just clean it
			else $string = self::clean_string($string);
			return $string;
		}
		else return false; //nothing valid was passed as an argument
	}

//------------------------------------------------------------------------------
//HELPERS

	//series of cleans to be perfomed on one string
	/**
	 * [clean_string description]
	 * @param  string $string
	 * @return string
	 */
	protected static function clean_string($string){
		$string = htmlspecialchars($string);
		$string = self::$mysqli->real_escape_string($string);
		return $string;
	}
}

?>