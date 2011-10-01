<?php
/**
 * \class SapphoDatabaseConnection
 * \brief This class handles connections to different database types. You'll probably want to use this one.
 *        
 *        This class was developed during the Sappho project (a Document Management System).
 *        It is able to manage a database connection to MySQL and postgreSQL databases
 *        without the user worrying about the correct syntax of the different statments.
 *
 *        It is the main class of the SDBC and will be used to create connections to database
 *        and handle all kind of requests you want to send to the database.
 *
 *
 * \author Daniel Eder
 * \version 0.1
 * \date 2011-09-28
 * \copyright GNU Public License Version 3
 */

// table structures
require_once("sappho_tabstruct.php");

// we use the syntax optimizer
require_once("sappho_synopt.php");
 
class SapphoDatabaseConnection{
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
	
	// last statement returned (mysqli only)
	private $db_stmnt = 0;
	
	// error message
	private $error_message;
	
	// debug fields
	private $debug_level;
	private $last_query;
	
	// syntax optimizer
	private $synopt = null;
	
	// transaction running?
	private $transaction_state = false;
	
	// return codes for connect()
	const db_connect_already_connected = 1; /**< returned by #connect(...) if the connector is already connected to a database */
	const db_connect_missing_data      = 2; /**< returned by #connect(...) if not all necessary connection data is given */
	const db_connect_declined          = 3; /**< returned by #connect(...) if the connection was declined */
	const db_connect_db_notexist       = 4; /**< returned by #connect(...) if the database does not exist */
	
	// return codes for select()
	const db_select_error  = 1; /**< returned by #select(...) if an error occured */
	const db_select_nodata = 2; /**< returned by #select(...) if the statement resulted in no data */
	
	// return codes for insert()
	const db_insert_error = 1; /**< returned by #insert(...) if an error occured */
	
	// return codes for update()
	const db_update_error = 1; /**< returned by #update(...) if an error occured */
	
	// error codes for nextData()
	const db_next_nodata = false; /**< returned by #nextData() if there is no next data record */
	
	// error codes for close()
	const db_close_not_connected = 1; /**< returned by #close() if the connector was never connected */
	const db_close_not_closed    = 2; /**< returned by #close() if the closing was not successfull */
	
	// error codes for delete();
	const db_delete_error = 1; /**< returned by #delete() if an error occured */
	
	// general error codes
	const db_error_wrong_dtype = 255; /**< a general return code if a wrong datatype was passed to a function */
	const db_error_catalog     = 256; /**< an error occured during while trying to catalog a table */
	
	// database types
	const db_type_mysql     = 'mysql'; /**< database type MySQL */
	const db_type_postgre   = 'postgresql'; /**< database type postgreSQL */
	
	// data of the last query
	private $lastResult;
	
	// table descriptions array
	private $tablestruct;
	
	
	/**
	* \brief Constructor of the class.
	*
	*        A new database connector instance is created. The
	*        connection <strong>is not</strong> started automatically!
	*
	* \param $type the type of the database (use any db_type_*)
	* \param $host databast host
	* \param $db name of target database
	* \param $user username to access the database
	*/
	function __construct($type, $host, $db, $user)
	{
		$this->status  = 'unconnected';
		if(isset($type)) $this->db_type = $type;
		if(isset($host)) $this->db_host = $host;
		if(isset($user)) $this->db_user = $user;
		if(isset($db))   $this->db_name = $db;
		
		if(isset($this->db_type))
			$this->synopt = new SapphoSyntaxOptimizer($this->db_type);
		
		$this->tablestruct = array();
	}
	
	/**
	 * \brief Deconstructor.
	 *
	 *        If the sdbc was connected to a database the connection is terminated.
	 */
	function __destruct()
	{
		if($this->status == 'connected')
			$this->close();
	}
		
	/**
	 * \brief Start connection to the database
	 *        
	 *        The connection to the given database is established.
	 *
	 * \param $password the password of the user used to connect
	 *
	 * \return <table>
	 *           <tr><td>0</td><td>connection established</td></tr>
	 *           <tr><td>#db_connect_already_connected</td><td>this connector is already connected to a database</td></tr>
	 *           <tr><td>#db_connect_missing_data</td><td>not all connection data is provided</td>
	 *           <tr><td>#db_connect_declined</td><td>the connection was declined by the remote host</td></tr>
	 *           <tr><td>#db_connect_db_notexist</td><td>the given database does not exist</td></tr>
	 *         </table>
	 */
	function connect($password)
	{
		if($this->status != 'unconnected') return self::db_connect_already_connected;
		if($this->db_type == '' ||
		   $this->db_host == '' ||
		   $this->db_user == '' ||
		   $this->db_name == ''  ) return self::db_connect_missing_data;
		   
		$this->db_handle = 0;
		if($this->typeIs(self::db_type_mysql))
			$this->db_handle = new mysqli($this->db_host,$this->db_user,$password);
		else if($this->typeIs(self::db_type_postgre))
		    $this->db_handle = pg_connect("host=".$this->db_host." dbname=".$this->db_name.
			                              " user=".$this->db_user." password=$password");
		
		if(!$this->db_handle)
		{
			$this->error_message = $this->getSQLError();
			return self::db_connect_declined;
		}
		
		if($this->typeIs(self::db_type_mysql) && 
		   !$this->db_handle->select_db($this->db_name))
		{
			$this->error_message = $this->getSQLError();
			return self::db_connect_db_notexist;
		}
		
		$this->status = 'connected';
		
		return 0;
	}
	
	// check if table is cataloged, if not do so!
	private function checkTable($table)
	{
		if(!in_array($table, $this->tablestruct))
		{
			if($this->debug_level > 3)
				echo "SDBC: table '$table' unknown invoke cataloging<br/>";
			if(!$this->catalog_table($table))
			{
				$this->error_message = "Could not catalog table $table - ".$this->lastError();
				return false;
			}
		}
		return true;
	}
	
	/**
	 * \brief Issue a select command to the database
	 *
	 *        This method selects data from a given table. You may specify
	 *        limiting clauses (mostly known as WHERE clauses) or lock the
	 *        selected data records.
	 *
	 * \param $table the table you wish to access
	 * \param $fields an array with fieldnames for multiple fields or only one string for one field (or *)
	 * \param $where a where clause
	 * \param $lock set to true if you wish to lock the selected records
	 *
	 * \return <table>
	 *           <tr><td>0</td><td>the statement was issued without any error</tr>
	 *           <tr><td>#db_select_error</td><td>an error occured</td></tr>
	 *         </table>
	 */
	function select($table, $fields = '*', $where = '', $lock=false)
	{
		if(!$this->checkTable($table))
			return self::db_error_catalog;
		
		$query = '';
		if($this->db_type == self::db_type_mysql)
		{
			$query = 'SELECT ';
			if(is_array($fields))
			{
				for($i=0; $i < count($fields); $i++)
				{
				$f = $this->escape_keywords($this->db_handle->real_escape_string($fields[$i]));
				$query .= $f;
			  
				if($i != count($fields)-1)
					$query .= ', ';
				}
			}
			else
				$query .= $fields;
			
			$query .= " FROM ".$this->escape_keywords($this->db_handle->real_escape_string($table));
			
			if($lock)
			  $query .= ' FOR UPDATE';
			
			$where = trim($where);
			if($where != '')
				$query .= " WHERE ".$where;
			
	    }
		else if($this->db_type == self::db_type_postgre)
		{
			$query = 'SELECT ';
			if(is_array($fields))
			{
				for($i=0; $i < count($fields); $i++)
				{
				$f = pg_escape_string($fields[$i]);
				$query .= $f;
			  
				if($i != count($fields)-1)
					$query .= ', ';
				}
			}
			else
				$query .= $this->escape_keywords(pg_escape_string($fields));
			
			$query .= " FROM ".$this->escape_keywords(pg_escape_string($table));
			
			if($lock)
			  $query .= ' FOR UPDATE';
			
			$where = trim($where);
			if($where != '')
				$query .= " WHERE ".$this->escape_keywords($where);
		}
		$this->setLastQuery($query);
		
		$result = 0;
		if($this->typeIs(self::db_type_mysql))
			$result = $this->db_handle->query($query);
		else if($this->typeIs(self::db_type_postgre))
			$result = pg_query($query);
			
		if(!$result)
		{
			$this->error_message = $this->getSQLError();
			return self::db_select_error;
		}
		
		$this->lastResult = $result;
		return 0;
	}
		
	/**
	 * \brief Executes a statement without further checks.
	 *
	 *        The given statement is executed on the database without any further checking.
	 *        You have to make sure that the synstax is correct for the used database by yourself.
	 *        Also mind that a table that is accessed via this method will not be cataloged
	 *        automatically.
	 *        
	 * \param $stmnt the statement you want to execute
	 *
	 * \return 0 on success, 1 on failure
	 */
	function execute($stmnt)
	{
		$this->setLastQuery($stmnt);
		$result = 0;
		
		if($this->typeIs(self::db_type_mysql))
			$result = $this->db_handle->query($stmnt);
		else if($this->typeIs(self::db_type_postgre))
			$result = pg_query($stmnt);
		
		if(!$result)
		{
			$this->error_message = $this->getSQLError();
			return 1;
		}
			
		$this->lastResult = $result;
		return 0;
	}
	
	/**
	 * \brief Insert a new record into a table.
	 *
	 * This method is used to insert new data into an existing table of the database.
	 *
	 * \param $table the table you wish to insert into
	 * \param $fields an array with the association of fieldnames (keys) to the values
	 *
	 * \returns <table>
	 *            <tr><td>0</td><td>success</td></tr>
	 *            <tr><td>#db_error_wrong_dtype</td><td>the parameter $field was no valid array</td></tr>
	 *            <tr><td>#db_insert_error</td><td>an error occured</td></tr>
	 *          </table>
	 */
	function insert($table, $fields)
	{
		if(!is_array($fields)) return self::db_error_wrong_dtype;
		
		if(!$this->checkTable($table))
			return self::db_error_catalog;
			
		$struct = $this->tablestruct[$table];
		
		$query = '';
		
		if($this->db_type == self::db_type_mysql)
		{
			$query = 'INSERT INTO ';
			$query .= $this->escape_keywords($this->db_handle->real_escape_string($table));
			
			$query .= '(';
			$fieldnames = array_keys($fields);
			$fieldtypes = array();
			for($i=0; $i<count($fieldnames); $i++){
				$query .= $this->escape_keywords($this->db_handle->real_escape_string($fieldnames[$i]));
				
				if($i != count($fieldnames)-1)
					$query .= ', ';
					
				array_push($fieldtypes, $struct->getType($fieldnames[$i]));
			}
			
			$query .= ') VALUES(';
			$values = $this->synopt->formatFields(array_values($fields), $fieldtypes);
			for($i=0; $i<count($values); $i++){
				$query .= $values[$i];
				
				if($i != count($values)-1)
					$query .= ', ';
			}
			$query .= ")";
		}
		else if($this->db_type == self::db_type_postgre)
		{
			$query = 'INSERT INTO ';
			$query .= $this->escape_keywords(pg_escape_string($table));
			
			$query .= '(';
			$fieldnames = array_keys($fields);
			$fieldtypes = array();
			for($i=0; $i<count($fieldnames); $i++){
				$query .= $this->escape_keywords(pg_escape_string($fieldnames[$i]));
				
				if($i != count($fieldnames)-1)
					$query .= ', ';
					
				array_push($fieldtypes, $struct->getType($fieldnames[$i]));
			}
			
			$query .= ') VALUES(';
			$values = $this->synopt->formatFields(array_values($fields), $fieldtypes);
			for($i=0; $i<count($values); $i++){
				$query .= $values[$i];
				
				if($i != count($values)-1)
					$query .= ', ';
			}
			$query .= ")";
		}
		
		$this->setLastQuery($query);
		$result = 0;
		
		if($this->db_type == self::db_type_mysql)
			$result = $this->db_handle->query($query);
		else if($this->db_type == self::db_type_postgre)
			$result = pg_query($query);
		
		if(!$result)
		{
			$this->error_message = $this->getSQLError();
			return self::db_insert_error;
		}
		
		$this->lastResult = $result;
		return 0;
	}
	
	/**
	 * \brief Update an already existing dataset.
	 *
	 *  Use this function to change already existing data in a table of the database.
	 *
	 * \param $table the table to be upated
	 * \param $data an array that associates the fields to the values
	 * \param $where a where clause to limit the updated datasets
	 *
	 * \returns <table>
	 *           <tr><td>0</td><td>the statement was issued without any error</tr>
	 *           <tr><td>#db_update_error</td><td>an error occured</td></tr>
	 *         </table>
	 */
	function update($table, $data, $where = '')
	{
		if(!is_array($data)) return self::db_update_error;
		
		if(!$this->checkTable($table))
			return self::db_error_catalog;
			
		$struct = $this->tablestruct[$table];
		
		$query = '';
		
		if($this->db_type == self::db_type_mysql)
		{
			$query .= 'UPDATE ';
			$query .= $this->escape_keywords($this->db_handle->real_escape_string($table));
			
			$query .= ' SET ';
			$keys = array_keys($data);
			for($i=0; $i<count($keys); $i++)
			{
				$query .= $this->escape_keywords($this->db_handle->real_escape_string($keys[$i])).' = '.
				          $this->synopt->formatField($data[$keys[$i]], $struct->getType($keys[$i]));
				if($i != count($keys)-1)
					$query .= ', ';
			}
			
			$where = trim($where);
			if($where != '')
				$query .= ' WHERE '.$where;
		}
		else if($this->db_type = self::db_type_postgre)
		{
			$query .= 'UPDATE ';
			$query .= $this->escape_keywords(pg_escape_string($table));
			
			$query .= ' SET ';
			$keys = array_keys($data);
			for($i=0; $i<count($keys); $i++)
			{
				$query .= $this->escape_keywords(pg_escape_string($keys[$i])).' = '.
				          $this->synopt->formatField($data[$keys[$i]], $struct->getType($keys[$i]));
				if($i != count($keys)-1)
					$query .= ', ';
			}
			
			$where = trim($where);
			if($where != '')
				$query .= ' WHERE '.$where;
		}
		
		$this->setLastQuery($query);
		$result = 0;
		
		if($this->db_type == self::db_type_mysql)
			$result = $this->db_handle->query($query);
		else if($this->db_type == self::db_type_postgre)
			$result = pg_query($query);
		
		if(!$result)
		{
			$this->error_message = $this->getSQLError();
			return self::db_insert_error;
		}
		
		$this->lastResult = $result;
		return 0;
	}
	
	/**
	 * \brief Removes a data record from a table.
	 *
	 *  By using this function a statement that deletes a record from the
	 *  database is sent to the database.
	 *
	 * \param $table the target table
	 * \param $where a were clause
	 *
	 * \returns <table>
	 *           <tr><td>0</td><td>the statement was completed without any error</tr>
	 *           <tr><td>#db_delete_error</td><td>an error occured</td></tr>
	 *         </table>	 */
	function delete($table, $where='')
	{
		if(!checkTable($table))
			return self::db_error_catalog;
		
		$query = 'DELETE FROM';
		if($this->db_type == self::db_type_mysql)
			$table = $this->db_handle->real_escape_string($table);
		else if($this->db_type == self::db_type_postgre)
			$table = pg_escape_string($table);
		$query .= $this->escape_keywords($table);
		
		$where = trim($where);
		if($where != '')
		{
			$query .= 'WHERE ';
			$query .= $where;
		}
		setLastQuery($query);
		
		$result = false;
		if($this->db_type == self::db_type_mysql)
			$result = $this->db_handle->query($query);
		else if($this->db_type == self::db_type_postgre)
			$result = pg_query($query);
		
		if(!$result)
		{
			$this->error_message = $this->getSQLError();
			return self::db_delete_error;
		}
		
		return 0;
	}
	
	/**
	 * \brief Starts a new mass-insert of data.
	 *
	 * \warning This method is not implemented to full extent and will cause errors!
	 *
	 *  You might use this method if you intend to insert a lot of new data records to
	 *  the database. It uses optimized statements to speed up the database requests. After
	 *  you defined a statement with this method, you have to use #addBulkData() to
	 *  define which data you wish to insert.
	 *
	 *  Eventually if all data is added you will have to call #submitBulk() to execute the
	 *  insert statement.
	 *
	 * \param $table the table you wish to insert into
     * \param $fields an array containing the <i>names of the fields</i>
     *
     * \returns \c 0 on success, \c 1 if an error occured	 
	 */
	function bulkInsert($table, $fields)
	{
		if(!is_array($fields)) return self::db_error_wrong_dtype;
		
		if(!$this->checkTable($table))
			return self::db_error_catalog;
		
		$query = 'INSERT INTO ';
		if($this->typeIs(self::db_type_mysql))
			$table = $this->escape_keywords($this->db_handle->real_escape_string($table));
		else if($this->typeIs(self::db_type_postgre))
			$table = $this->escape_keywords(pg_escape_string($table));
			
		$query .= $table;
		$query .= '(';
		for($i=0; $i<count($fields); $i++)
		{
			$val = '';
			if($this->typeIs(self::db_type_mysql))
				$val = $this->escape_keywords($this->db_handle->real_escape_string($fields[i]));
			else
				$val = $this->escape_keywords(pg_escape_string($fields[i]));
			
			$query .= $val;
			
			if($i != count($fields)-1)
				$query .= ', ';
		}
		
		$query .= ') VALUES(';
		for($i=0; $i<count($fields); $i++)
		{
			$query .= '?';
			if($i != count($fields)-1)
				$query .= ', ';
		}
		
		$query .= ')';
		
		$this->setLastQuery($query);
		$this->prep_stmnt_name = "bulk_insert_".time();
		if($this->typeIs(self::db_type_mysql))
			$this->db_stmnt = $this->db_handle->prepare($query);
		else if($this-typeIs(self::db_type_postgre))
			$this->db_stmnt = pg_prepare($this->prep_stmnt_name, $query);
			
		if(!$this->db_stmnt)
		{
			$this->error_message = "Could not create bulk insert: ".$this->getSQLError();
			return 1;
		}
		
		return 0;
	}
	
	/**
	 * \brief Get the next data set of the last result set.
	 *
	 *  This statement returns an associated array with the table data that was queried
	 *  by the last select statement that was executed.
	 *
	 * \returns An associated array or #db_next_nodata
	 */
	function nextData(){
		if(($this->typeIs(self::db_type_mysql) &&
		    !$this->lastResult->num_rows) 
			||
           ($this->typeIs(self::db_type_postgre) &&
		    !pg_num_rows($this->lastResult)))
		{
			$this->error_message = "SDBC - last result set is empty";
			return self::db_next_nodata;
		}
		
		
		if($this->debug_level > 1)
			echo "SDBC: next data -> ";
		
		$data = 0;
		if($this->typeIs(self::db_type_mysql))
			$data = $this->lastResult->fetch_assoc();
	    else if($this->typeIs(self::db_type_postgre))
			$data = pg_fetch_assoc($this->lastResult);
			
		if($this->debug_level > 1)
		{
			print_r($data);
			echo "<br/>";
		}
		
		if(!$data)
		{
			$this->error_message = $this->getSQLError();
			
			if(trim($this->lastError()) == '')
				$this->error_message = "SDBC - no data left";
				
			return self::db_next_nodata;
		}
		
		return $data;
	}
	
	// debug functions
	private function setLastQuery($query)
	{
		$this->last_query = $query;
		if($this->debug_level > 0)
			echo "SDBC: last query was '".$this->last_query."'<br/>";
	}
	
	/**
	 * \brief The error message of the last occured error.
	 *
	 * \returns Error message of the last occured error.
	 */
	function lastError()
	{
		return $this->error_message;
	}
	
	// last error in sql connection
	private function getSQLError(){
		$error = '';
		if($this->typeIs(self::db_type_mysql))
			$error = $this->db_handle->error;
		if($this->typeIs(self::db_type_postgre))
		    $error = pg_last_error();
		return $error;
	}
	
	/**
	 * \brief Check if the connection is of the given type.
	 *
	 * \param $compare the database type you wish to check
	 * \returns true if the database types equals the given one.
	 */
	function typeIs($compare)
	{
		if($this->db_type == $compare) return true;
		return false;
	}
	
	/**
	 * \brief Set debug level.
	 *
	 * This method sets the debug level. When debug level is set some additional information
	 * that helps finden errors in your code will be displayed. The level determines how verbose
	 * this information will be. A higher level always displayes information that is provided
	 * by lower levels.
	 *
	 * Currently these debug levels are implemented:
	 * <table>
	 *  <tr><th>level</th><th>information</th></tr>
	 *  <tr><td>0</td><td>no debug information</td></tr>
	 *  <tr><td>1</td><td>executed statements</td></tr>
	 *  <tr><td>2</td><td>data retrieved by #nextData()</td></tr>
	 *  <tr><td>4</td><td>table catalog data</td></tr>
	 *  <tr><td>9</td><td>also set debug flag in all submodules</td></tr>
	 * </table>
	 *
	 * \param $debug the debug level
	 */
	function setDebug($debug)
	{
		$this->debug_level = $debug;
		if($debug > 8)
		{
			$this->synopt->setDebug(1);
		}
		else if($debug < 1)
		{
			$this->synopt->setDebug(0);
		}
	}
	
	/**
	 * \brief Close the database connection.
	 *
	 *  By using this function you close the SDBC connection. This means that you can not submit any
	 *  requests after this statement. You may, however, use #connect() to reestablish the database
	 *  connection.
	 *
	 * \b Mind \b this: The use of this function is \a optional. It is automatically called by
	 *  #__destruct() when the instance is destructed.
	 *
	 * \returns <table>
	 *           <tr><td>0</td><td>connection was closed</tr>
	 *           <tr><td>#db_close_not_connected</td><td>you tried to close an unconnected SDBC</td></tr>
	 *           <tr><td>#db_close_not_closed</td><td>the SDBC was not able to close the connection</td></tr>
	 *          </table>
	 */
	function close()
	{
		if($this->status != 'connected')
			return self::db_close_not_connected;
			
		if($this->typeIs(self::db_type_mysql))
			if(!$this->db_handle->close())
			{
				$this->error_message = 'Connection remains unclosed: '.$this->db_handle->error();
				return self::db_close_not_closed;
			}
		else if($this->typeIs(self::db_type_postgre))
			if(!(pg_close($this->db_handle)))
			{
				$this->error_message = 'Connection remains unclosed: '.$this->db_handle->error();
				return self::db_close_not_closed;
			}
		
		$this->status = 'unconnected';
		return 0;
	}
	
	/**
	 * \brief The amount of rows queried by the last executed statement.
	 *
	 * \returns amount of rows held in the last result set
	 */
	function rowCount()
	{
		if(!$this->lastResult) return 0;
		if($this->typeIs(self::db_type_mysql))
			return $this->lastResult->num_rows;
		if($this->typeIs(self::db_type_postgre))
			return pg_num_rows($this->lastResult);
		return 0;
	}
	
	/**
	 * \brief The last result set.
	 *
	 * \returns the result set of the last query
	 */
	function getLastResult()
	{
		return $this->lastResult;
	}
	
	// escape DBS keywords
	private function escape_keywords($string)
	{
		return $this->synopt->escape_reserved_words($string);
	}
	
	/**
	 * \brief Analyzes and catalogues the structure of a table.
	 *
	 *  Usually this method is called by any other data operation method.
	 *  It catalogues a table and analyzes it's data structure. This is
	 *  necessary for the automatic datatype recognition and syntax
	 *  optimization.
	 *
	 *  Because this method is called once for each table on it's first
	 *  use, the first operation on a table might be a bit slower than the
	 *  rest.
	 *
	 *  You may call this function manually to recatalog a table. This will
	 *  be necessary if you changed it's data structure while using the
	 *  same SDBC.
	 *
	 * \param $table the name of the table to cagalogue
	 * \param $recat set to true if you are recataloging a table
	 *
	 * \returns \c true if the table was catalogued or \c false on any
	 *          error
	 */
	function catalog_table($table, $recat=false)
	{
		// structure is already cataloged
		if(!$recat && in_array($table, array_keys($this->tablestruct)))
			return true;
			
		if($this->debug_level)
			echo "SDBC: Analyzing table $table<br/>";
		
		if($this->typeIs(self::db_type_mysql))
			return $this->catalog_mysql_table($table);
		else if($this->typeIs(self::db_type_postgre))
			return $this->catalog_postgre_table($table);
		return false;
	}
	
	// catalog a mysql table
	private function catalog_mysql_table($table)
	{
		// if this no mysql db we don't do anything
		if(!$this->typeIs(self::db_type_mysql)) return false;
		
		$query  = 'DESCRIBE ';
		$query .= $this->escape_keywords($this->db_handle->real_escape_string($table));
		
		if($this->debug_level > 3)
			echo "SDBC: catalog query = $query<br/>";
		
		$result = $this->db_handle->query($query);
		if(!$result)
		{
			$this->error_message = 'Could not catalog table '.$table.': '.$this->getSQLError();
			return false;
		}
		
		$struct = new SapphoTableStructure($table);
		while(($data = $result->fetch_row()))
		{
			if($this->debug_level > 3)
			{
				echo "SDBC: Analyzing attribute: ";
				print_r($data);
				echo "<br/>";
			}
			
			$pos    = strpos($data[1], "(");
			$type   = substr($data[1], 0, $pos);
			$pos2   = strpos($data[1], ")");
			$length = substr($data[1], $pos+1, $pos2-$pos-1);
			
			$struct->addColumn($data[0], $type, $length);
			if($this->debug_level > 3)
				echo $data[0]." -> type = ".$struct->getType($data[0]).
				     "; length = ".$struct->getLength($data[0])."<br/>";
		}
		
		$this->tablestruct[$table] = $struct;
		return true;
	}
	
	// catalog a postgre table
	private function catalog_postgre_table($table)
	{
		// we only operate for postgresql
		if(!$this->typeIs(self::db_type_postgre)) return false;
		
		$query  = 'SELECT * FROM INFORMATION_SCHEMA.columns WHERE table_name = ';
		$query .= $this->synopt->formatString(pg_escape_string($table));
		
		if($this->debug_level > 3)
			echo "SDBC: catalog query = $query<br/>";
			
		$result = pg_query($query);
		if(!$result)
		{
			$this->error_message = 'Could not catalog table '.$table.': '.$this->getSQLError();
			return false;
		}
		
		$struct = new SapphoTableStructure($table);
		while(($data = pg_fetch_assoc($result)))
		{
			if($this->debug_level > 3)
			{
				echo "SDBC: Analyzing attribute: ";
				echo $data["column_name"]." => ";
				echo $data["data_type"];
				echo "<br/>";
			}
			
			// length is not stored for postgre - it's not used anyway...
			$struct->addColumn($data["column_name"], $data["data_type"], -1);
		}
		
		$this->tablestruct[$table] = $struct;
		return true;
	}
	
	/**
	 * \brief Start a new transaction
	 * 
	 *  Invokes a new transaction in the database system.
	 *
	 * \returns \c true if successful or \c false on any error
	 */
	function startTransaction()
	{
		if($this->transaction_state)
		{
			$this->error_message = "Transaction already running.";
			return false;
		}
		
		$result = 0;
		if($this->db_type == self::db_type_mysql)
			$result = $this->db_handle->query("START TRANSACTION");
		else if($this->db_type == self::db_type_postgre)
			$result = pg_query("START TRANSACTION");
		
		if(!$result)
		{
			$this->error_messge = $this->getSQLError();
			return false;
		}
		
		$this->transaction_state = true;
		return true;
	}
	
	/**
	 * \brief Commit a running transaction.
	 *
	 *  A running transaction will be commited.
	 *
	 * \returns \c true if successful or \c false on any error
	 */
	function commitTransaction()
	{
		if(!$this->transaction_state)
		{
			$this->error_message = "No transaction running.";
			return false;
		}
			
		$result = 0;
		if($this->db_type == self::db_type_mysql)
			$result = $this->db_handle->query("COMMIT");
		else if($this->db_type == self::db_type_postgre)
			$result = pg_query("COMMIT");
		
		if(!$result)
		{
			$this->error_messge = $this->getSQLError();
			return false;
		}
		return true;
	}
	
	/**
	 * \brief Undos a running transaction.
	 *
	 *  A running transaction will be rolled back.
	 *
	 * \returns \c true if successful or \c false on any error
	 */
	function rollbackTransaction()
	{
		if(!$this->transaction_state)
		{
			$this->error_message = "No transaction running.";
			return false;
		}
			
		$result = 0;
		if($this->db_type == self::db_type_mysql)
			$result = $this->db_handle->query("ROLLBACK");
		else if($this->db_type == self::db_type_postgre)
			$result = pg_query("ROLLBACK");
		
		if(!$result)
		{
			$this->error_messge = $this->getSQLError();
			return false;
		}
		return true;
	}
}
?>