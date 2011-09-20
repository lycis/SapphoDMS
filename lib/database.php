<?php
/*
 * this class handles all database actions
 */

class DatabaseConnection{
	// status can be:
	//	connected or unconnected
	private $status;
		
	// connection information
	private $db_type = '';
	private $db_host = '';
	private $db_user = '';
	private $db_name = '';
	
	// connection handle
	private $db_handle = 0;
	
	// error message
	private $error_message;
	
	// debug fields
	private $debug_level;
	private $last_query;
	
	// return codes for connect()
	const db_connect_already_connected = 1;
	const db_connect_missing_data      = 2;
	const db_connect_declined          = 3;
	const db_connect_db_notexist       = 4;
	
	// return codes for select()
	const db_select_error  = 1;
	const db_select_nodata = 2;
	
	// return codes for insert()
	const db_insert_error = 1;
	
	// return codes for update()
	const db_update_error = 1;
	
	// error codes for nextData()
	const db_next_nodata = false;
	
	// error codes for close()
	const db_close_not_connected = 1;
	const db_close_not_closed    = 2;
	
	// general error codes
	const db_error_wrong_dtype = 255;
	
	// data of the last query
	private $lastResult;
	
		
	function __construct($type, $host, $db, $user)
	{
		$status  = 'unconnected';
		if(isset($type)) $this->db_type = $type;
		if(isset($host)) $this->db_host = $host;
		if(isset($user)) $this->db_user = $user;
		if(isset($db))   $this->db_name = $db;
	}
	
	function __destruct()
	{
		if($this->status == 'connected')
			$this->close();
	}
		
	// connect to database
	function connect($password)
	{
		//if($status != 'unconnected') return db_connect_already_connected;
		if($this->db_type == '' ||
		   $this->db_host == '' ||
		   $this->db_user == '' ||
		   $this->db_name == ''  ) return self::db_connect_missing_data;
		   
		$this->db_handle = 0;
		if($this->typeIs('mysql'))
			$this->db_handle = mysql_connect($this->db_host,$this->db_user,$password);
		
		if(!$this->db_handle)
		{
			$this->error_message = $this->getSQLError();
			return self::db_connect_declined;
		}
		
		$this->status = 'connected';
		 
		if(!mysql_select_db($this->db_name))
		{
			$this->error_message = $this->getSQLError();
			return self::db_connect_db_notexist;
		}
		
		return 0;
	}
	
	// select from database
	function select($table, $fields = '*', $where = '')
	{
		//if(!is_array($fields)) return self::db_error_wrong_dtype;
		
		$query = '';
		if($this->db_type == 'mysql')
		{
			$query = 'SELECT ';
			if(is_array($fields))
			{
				for($i=0; $i < count($fields); $i++)
				{
				$f = mysql_real_escape_string($fields[$i]);
				$query .= $f;
			  
				if($i != count($fields)-1)
					$query .= ', ';
				}
			}
			else
				$query .= $fields;
			
			$query .= " FROM $table";
			
			$where = trim($where);
			if($where != '')
				$query .= " WHERE ".mysql_real_escape_string($where);
			
	    }
		$this->setLastQuery($query);
		
		$result = 0;
		if($this->typeIs('mysql'))
		{
			$result = mysql_query($query);
		}
			
		if(!$result)
		{
			$this->error_message = $this->getSQLError();
			return self::db_select_error;
		}
		
		$this->lastResult = $result;
		return 0;
	}
	
	// execute given statement on database - use at own risk, no optimization done!
	function execute($stmnt)
	{
		if($this->typeIs('mysql'))
		{
			$this->setLastQuery($stmnt);
			
			$result = 0;
			$result = mysql_query($stmnt);
			
			if(!$result)
			{
				$this->error_message = $this->getSQLError();
				return 1;
			}
			
			$this->lastResult = $result;
			return 0;
		}
	}
	
	// insert into database
	function insert($table, $fields)
	{
		if(!is_array($fields)) return self::db_insert_error;
		
		$query = '';
		
		if($this->db_type == 'mysql')
		{
			$query = 'INSERT INTO ';
			$query .= mysql_real_escape_string($table);
			
			$query .= '(';
			$fieldnames = array_keys($fields);
			for($i=0; $i<count($fieldnames); $i++){
				$query .= $fieldnames[$i];
				
				if($i != count($fieldnames)-1)
					$query .= ', ';
			}
			
			$query .= ') VALUES(';
			$values = array_values($fields);
			for($i=0; $i<count($values); $i++){
				$query .= $values[$i];
				
				if($i != count($values)-1)
					$query .= ', ';
			}
			$query .= ")";
		}
		
		$this->setLastQuery($query);
		$result = 0;
		
		if($this->db_type = 'mysql')
			$result = mysql_query($query);
		
		if(!$result)
		{
			$this->error_message = $this->getSQLError();
			return self::db_insert_error;
		}
		
		$this->lastResult = $result;
		return 0;
	}
	
	// update a dataset
	function update($table, $data, $where = '')
	{
		if(!is_array($data)) return self::db_update_error;
		$query = '';
		
		if($this->db_type = 'mysql')
		{
			$query .= 'UPDATE ';
			$query .= mysql_real_escape_string($table);
			
			$query .= ' SET ';
			$keys = array_keys($data);
			for($i=0; $i<count($keys); $i++)
			{
				$query .= mysql_real_escape_string($keys[$i]).' = '.
				          mysql_real_escape_string($data[$keys[$i]]);
				if($i != count($keys)-1)
					$query .= ', ';
			}
			
			$where = trim($where);
			if($where != '')
				$query .= ' WHERE '.mysql_real_escape_string($where);
		}
		
		$this->setLastQuery($query);
		$result = 0;
		
		if($this->db_type = 'mysql')
			$result = mysql_query($query);
		
		if(!$result)
		{
			$this->error_message = $this->getSQLError();
			return self::db_insert_error;
		}
		
		$this->lastResult = $result;
		return 0;
	}
	
	// get next data row of the last query
	function nextData(){
		if(!mysql_num_rows($this->lastResult)) return self::db_next_nodata;
		
		if($this->debug_level > 1)
			echo "DB: next data -> ";
		
		$data = 0;
		if($this->typeIs('mysql'))
			$data = mysql_fetch_assoc($this->lastResult);
			
		if($this->debug_level > 1)
			print_r($data);
			
		if(!$data)
		{
			$this->lastError = $this->getSQLError();
			return self::db_next_nodata;
		}
		
		return $data;
	}
	
	// debug functions
	function setLastQuery($query)
	{
		$this->last_query = $query;
		if($this->debug_level > 0)
			echo "DB: last query was '".$this->last_query."'<br/>";
	}
	
	// get last error
	function lastError()
	{
		return $this->error_message;
	}
	
	// last error in sql connection
	function getSQLError(){
		$error = '';
		if($this->typeIs('mysql'))
			$error = mysql_error();
		return $error;
	}
	
	// is the connection of type...?
	function typeIs($compare)
	{
		if($this->db_type == $compare) return true;
		return false;
	}
	
	// set level for debug messages
	function setDebug($debug)
	{
		$this->debug_level = $debug;
	}
	
	// close an former established connection
	function close()
	{
		if($this->status != 'connected')
			return self::db_close_not_connected;
			
		if($this->typeIs('mysql'))
			if(!mysql_close($this->db_handle))
			{
				$this->error_message = 'Connection remains unclosed: '.mysql_error();
				return self::db_close_not_closed;
			}
		
		$this->status = 'unconnected';
		return 0;
	}
	
	// get rows of result
	function rowCount()
	{
		if(!$this->lastResult) return 0;
		if($this->typeIs('mysql'))
			return mysql_num_rows($this->lastResult);
		return 0;
	}
	
	// get the result handle of the last query
	function getLastResult()
	{
		return $this->lastResult;
	}
}
?>