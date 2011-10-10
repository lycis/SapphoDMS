<?php
/**
 * \class SapphoQueryOptions
 * \brief This class is used to set any statement options like WHERE clauses or ORDER BY etc.
 *        
 *        This class represents any kind of options that aplly to a statement. This may range from
 *        WHERE clauses to ORDER BY columns or subselects and joins.
 *
 *        \warning Currently only simple where clauses are implemented!
 *
 * \author Daniel Eder
 * \version 0.1
 * \date 2011-10-06
 * \copyright GNU Public License Version 3
 */

// we need the dbc for database types
require_once('sappho_dbc.php');

// we also need our own syntax optimizer
 
class SapphoQueryOptions{

	// database type of the options
	private $db_type;
	
	// where clause
	private $where;
	
	// syntax optimizer
	private $synopt;
	
	// parent connection
	private $sdbc;
	
	// table cache
	private $tablecache;
	
	// match operations
	const EQUALS  = 'eq';
	const LOWER   = 'lo';
	const GREATER = 'gr';
	
	const WHERE_INITIAL = 'init';
	const WHERE_AND     = 'and';
	const WHERE_OR      = 'or';
	const WHERE_SUB_AND = 'sand';
	const WHERE_SUB_OR  = 'sor';
	
	/**
	 * \brief Creates a new instance.
	 *
	 * \param $type database type as set in SapphoDatabaseConnection
	 */
	function __construct($connection){
		$this->db_type    = $connection->getType();
		$this->where      = array();
		$this->sdbc       = $connection;
		$this->synopt     = &$connection->getSyntaxOptimizer();
		$this->tablecache = &$connection->getTableCache();
	}
	
	/**
	 * \brief Executed on destruction of the object.
	 *
	 * Currently does nothing.
	 */
	function __destruct(){
	}
	
	/**
	 * \brief Add a \c WHERE clause to the query.
	 *
	 * This method initiates a \c WHERE clause. It's parameters set the first condition
	 * of the clause. Any further \c WHERE parameters can be set with the #add() and
	 * #or() functions.
	 *
	 * \param $field the field that has to be tested
	 * \param $operation which condition has to match
	 * \param $value the value the field hast to match
	 *
	 * \returns modified object
	 */
	function where($field, $operation, $value)
	{
		// set initial condition
		$this->where[0] = array(self::WHERE_INITIAL, $field, $operation, $value);
		
		return $this;
	}
	
	/**
	 * \brief Add an \c AND condition to the \c WHERE clause.
	 *
	 * The use of this method extends the defined \c WHERE clause with a \c AND clause.
	 * So before calling this method you need to initialize a \c WHERE clause with #where().
	 *
	 * You may either give all three parameters to define a simple condition like
	 * <tt>AND column = 'value'</tt> or the like.
	 *
	 * If the first parameter is another SapphoQueryOptions object a new subcondition like
	 * <tt>AND (column = 'value' OR column_b = 1 ...)</tt> will be added. So this is a
	 * convinient way to bild nested \c WHERE conditions.
	 * \param $field the column that has to match
	 * \param $operation a match operation
	 * \param $value the value the column has to match
	 * 
	 * \returns modified object
	 */
	function andWhere($field, $operation='', $value='')
	{
		if(count($this->where) < 1) return false;
		
		$data = array();
		
		if(is_a($field, "SapphoQueryOptions"))
			$data = array(self::WHERE_SUB_AND, $field);
		else
			$data = array(self::WHERE_AND, $field, $operation, $value);
		array_push($this->where, $data);
		return $this;
	}
	
	/**
	 * \brief Add an \c OR condition to the \c WHERE clause.
	 *
	 * The use of this method extends the defined \c WHERE clause with a \c OR clause.
	 * So before calling this method you need to initialize a \c WHERE clause with #where().
	 *
	 * You may either give all three parameters to define a simple condition like
	 * <tt>OR column = 'value'</tt> or the like.
	 *
	 * If the first parameter is another SapphoQueryOptions object a new subcondition like
	 * <tt>OR (column = 'value' OR column_b = 1 ...)</tt> will be added. So this is a
	 * convinient way to bild nested \c WHERE conditions.
	 * 
	 * \param $field the column that has to match or another SapphoQueryOptions object
	 * \param $operation a match operation
	 * \param $value the value the column has to match
	 * 
	 * \returns modified object
	 */
	function orWhere($field, $operation='', $value='')
	{
		if(count($this->where) < 1) return false;
		
		$data = array();
		
		if(is_a($field, "SapphoQueryOptions"))
			$data = array(self::WHERE_SUB_OR, $field);
		else
			$data = array(self::WHERE_OR, $field, $operation, $value);
			
		array_push($this->where, $data);
		return $this;
	}
	
	/**
	 * \brief Construct a valid WHERE clause.
	 *
	 * By using this method the stored options are processed and a valid \c WHERE clause
	 * is built and returned. It is already correctly escaped for use with the assigned
	 * connection. This method is mainly designed to be used internally but you may call
	 * it to get the \c WHERE clause separatly.
	 *
	 * It is necessary to provide the table that the query will be executed on to do
	 * correct datatype escaping by using the tablecache of the assigned connection.
	 *
	 * \param $table the table the statement will be executed on
	 * \param $subcontition set to true if used during #getWhereClause() if used in nested conditions
	 * \returns the correctly escaped \c WHERE clause as string
	 */
	function getWhereClause($table, $subcondition=false)
	{
		$clause = "";
		if(count($this->where) < 1) return "";
		
		if(!$subcondition)
			$clause = "WHERE ";
		
		foreach($this->where as $condition)
		{
			switch($condition[0])
			{
				case self::WHERE_INITIAL: break;
				case self::WHERE_AND:     $clause .= " AND ";
                                          break;
				case self::WHERE_OR:      $clause .= " OR ";
				                           break;
				case self::WHERE_SUB_AND: $clause .= " AND (";
				                           break;
				case self::WHERE_SUB_OR:  $clause .= " OR (";
				                           break;
			}
			
			// subcondition
			if($condition[0] == self::WHERE_SUB_AND || 
			   $condition[0] == self::WHERE_SUB_OR)
			{
				$clause .= $condition[1]->getWhereClause($table, true);
				$clause .= ")";
			}
			// normal condition
			else
			{
				$clause .= $condition[1];
			
				switch($condition[2])
				{
					case self::EQUALS:   $clause .= " = ";
										 break;
					case self::GREATER:  $clause .= " > ";
										 break;
					case self::LOWER:    $clause .= " < ";
										 break;
				}
			
				$clause .= $this->synopt->formatField($this->sdbc->escape($condition[3]), 
													  $this->tablecache[$table]->getType($condition[1]));
			}
		}
		
		return $clause;
	}
	
	/**
	 * \brief Get all clauses set with this options.
	 *
	 * By using this method all conditions and options defined with this object are evaluated
     * and all set clauses are generated. There is a fixed order for the generated clauses:
	 * -# \c WHERE
	 *
	 * It is necessary to provide the table that the query will be executed on to do
	 * correct datatype escaping by using the tablecache of the assigned connection.
	 *
	 * \param $table the table the query will be executed on
	 * \returns all conditions and clauses in a string
	 */
	function getClause($table)
	{
		$clause = "";
		
		// where
		if(count($this->where) > 0)
			$clause .= $this->getWhereClause($table);
		
		return $clause;
	}
	
}
?>