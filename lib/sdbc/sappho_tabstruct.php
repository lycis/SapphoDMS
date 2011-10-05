<?php
/**
 * \class SapphoTableStructure
 *
 * \brief Internal presentation of used tables
 *
 * This class represents the structure of a database table, regardless if
 * that is MySQL or postgreSQL.
 *
 * \bug Currently not all datatypes of MySQL and especially postgres are
 *      recognized properly and may be cataloged as unknown.
 *
 * \author Daniel Eder
 * \version 0.1
 * \date 2011-09-26
 * \copyright GNU Public License Version 3
 */
class SapphoTableStructure{
	// list of columns
	private $columns;
	
	// autoincremented field in this table
	private $serial_field;
	
	// table name - just for fun...
	private $table;
	
	// definition to classfiy column datatypes
	private $numeric_types = array('SERIAL', 'BIT', 'TINYINT', 'BOOL', 'BOOLEAN', 'SMALLINT', 'MEDIUMINT',
	                                 'INT', 'INTEGER', 'BIGINT', 'DOUBLE', 'FLOAT', 'DECIMAL', 'DEC', 'FIXED');
	private $string_types  = array('CHAR', 'VARCHAR', 'TEXT', 'BINARY', 'VARBINARY', 'TINYBLOB', 'TINYTEXT',
	                                 'BLOB', 'MEDIUMBLOB', 'LONGBLOB', 'LONGTEXT', 'CHARACTER VARYING');
									 
	// constants for data type classification
	const dtype_numeric = 'N'; /**< datatype mark for numeric values */
	const dtype_string  = 'S'; /**< datatype mark for string (character) values */
	const dtype_unknown = 'U'; /**< datatype mark for anything unknown */
	
    /**
	 * \brief Create an instance.
	 *
	 * \param $tablename name of the table the struct represents
     */	
	function __construct($tablename)
	{
		$this->table = $tablename;
		$this->columns = array();
		$this->serial_field = false;
	}
	
	/**
	 * \brief Add a new column to the structure
	 *
	 * \param $name column name
	 * \param $dtype datatype
	 * \param $length data length
	 * \param $serial if \c true this field is marked as automatically incremented (only relevant for postgreSQL)
	 */
	function addColumn($name, $dtype, $length, $serial=false)
	{
		$typemark = '';
		$dtype = strtoupper($dtype);
		if(in_array($dtype, $this->numeric_types))
			$typemark = self::dtype_numeric;
		else if(in_array($dtype, $this->string_types))
			$typemark = self::dtype_string;
		else
			$typemark = self::dtype_unknown;
		
		$this->columns[$name] = array($typemark, $length);
		
		if($serial === true)
			$this->serial_field = $name;
	}
	
	/**
	 * \brief Returns the table name.
	 *
	 * \returns name of the table
	 */
	function getName()
	{
		return $table;
	}
	
	/**
	 * \brief Get the datatype of a column in the table
	 *
	 * \returns datatype mark
	 */
	function getType($column)
	{
		if(!in_array($column, array_keys($this->columns)))
			return false;
			
		return $this->columns[$column][0];
	}

	/**
	 * \brief Get the length of the datafield
	 *
	 * \returns length of the column
	 */
	function getLength($column)
	{
		if(!in_array($column, array_keys($this->columns)))
			return false;
			
		return $this->columns[$column][1];
	}
	
	/**
	 * \brief Amount of fields the table contains
	 *
	 * \returns the amount of fields the table contains
	 */
	function getFieldNum()
	{
		if(!in_array($column, array_keys($this->columns)))
			return false;
			
		return $count($this->columns);
	}
	
	/**
	 * \brief The field that is automatically incremented within this table.
	 * \warning This function is used for postgreSQL only!
	 * \returns name of the column.
	 */
	 function serialField()
	 {
		return $this->serial_field;
	 }
}

?>