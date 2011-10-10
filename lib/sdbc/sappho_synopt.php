<?php
/**
 * \class SapphoSyntaxOptimizer
 * \brief The Syntax Optimizer offers functions to optimze statements for different database systems.
 *        
 *        The SyntaxOptimizer is used to adapt statements or strings for the usage on special database systems.
 *        It may be used to test if a special string is a reserved word or to escape reserved words.
 *
 *        \warning This class is designed to be used internally so it might be a pain to use it :-)
 *
 * \author Daniel Eder
 * \version 0.1
 * \date 2011-09-22
 * \copyright GNU Public License Version 3
 */
require_once("sappho_tabstruct.php");

class SapphoSyntaxOptimizer{
	private $reserved_words_mysql   = array('ADD', 'ANALYZE', 'ASC', 'BIGINT', 'BOTH', 'CASE', 'CHARACTER', 'COLUMN', 'CONVERT', 'CURRENT_DATE', 'CURRENT_USER', 'DAY_HOUR', 'DAY_SECOND', 'DEFAULT', 'DESC', 'DISTINCTROW', 'DROP', 'ENCLOSED', 'EXPLAIN', 'FLOAT', 'FOR', 'FROM', 'GROUP', 'HOUR_MICROSECOND', 'IF', 'INDEX', 'INSERT', 'INT2', 'INT8', 'INTO', 'KEY', 'LEADING', 'LIMIT', 'LOCALTIME', 'LONG', 'LOW_PRIORITY', 'MEDIUMINT', 'MINUTE_MICROSECOND', 'NATURAL', 'NULL', 'OPTIMIZE', 'OR', 'OUTFILE', 'PRIVILEGES', 'READ', 'REGEXP', 'REQUIRE', 'RIGHT', 'SELECT', 'SHOW', 'SPATIAL', 'SQL_SMALL_RESULT', 'STRAIGHT_JOIN', 'TERMINATED', 'TINYINT', 'TRAILING', 'UNIQUE', 'UPDATE', 'USING', 'UTC_TIMESTAMP', 'VARCHAR', 'WHEN', 'WRITE', 'ZEROFILL', 'CHECK', 'LOCALTIMESTAMP', 'SSL', 'BEFORE', 'CURRENT_USER', 'DUAL', 'MINUTE_MICROSECOND', 'SECOND_MICROSECOND', 'TRUE', 'UTC_TIMESTAMP', 'ALL', 'AND', 'BEFORE', 'BINARY', 'BY', 'CHANGE', 'CHECK', 'COLUMNS', 'CREATE', 'CURRENT_TIME', 'DATABASE', 'DAY_MICROSECOND', 'DEC', 'DELAYED', 'DESCRIBE', 'DIV', 'DUAL', 'ESCAPED', 'FALSE', 'FLOAT4', 'FORCE', 'FULLTEXT', 'HAVING', 'HOUR_MINUTE', 'IGNORE', 'INFILE', 'INT', 'INT3', 'INTEGER', 'IS', 'KEYS', 'LEFT', 'LINES', 'LOCALTIMESTAMP', 'LONGBLOB', 'MATCH', 'MEDIUMTEXT', 'MINUTE_SECOND', 'NOT', 'NUMERIC', 'OPTION', 'ORDER', 'PRECISION', 'PROCEDURE', 'REAL', 'RENAME', 'RESTRICT', 'RLIKE', 'SEPARATOR', 'SMALLINT', 'SQL_BIG_RESULT', 'SSL', 'TABLE', 'THEN', 'TINYTEXT', 'TRUE', 'UNLOCK', 'USAGE', 'UTC_DATE', 'VALUES', 'VARCHARACTER', 'WHERE', 'XOR', '�', 'FORCE', 'REQUIRE', 'XOR', 'COLLATE', 'DAY_MICROSECOND', 'FALSE', 'MOD', 'SEPARATOR', 'UTC_DATE', 'VARCHARACTER', 'ALTER', 'AS', 'BETWEEN', 'BLOB', 'CASCADE', 'CHAR', 'COLLATE', 'CONSTRAINT', 'CROSS', 'CURRENT_TIMESTAMP', 'DATABASES', 'DAY_MINUTE', 'DECIMAL', 'DELETE', 'DISTINCT', 'DOUBLE', 'ELSE', 'EXISTS', 'FIELDS', 'FLOAT8', 'FOREIGN', 'GRANT', 'HIGH_PRIORITY', 'HOUR_SECOND', 'IN', 'INNER', 'INT1', 'INT4', 'INTERVAL', 'JOIN', 'KILL', 'LIKE', 'LOAD', 'LOCK', 'LONGTEXT', 'MEDIUMBLOB', 'MIDDLEINT', 'MOD', 'NO_WRITE_TO_BINLOG', 'ON', 'OPTIONALLY', 'OUTER', 'PRIMARY', 'PURGE', 'REFERENCES', 'REPLACE', 'REVOKE', 'SECOND_MICROSECOND', 'SET', 'SONAME', 'SQL_CALC_FOUND_ROWS', 'STARTING', 'TABLES', 'TINYBLOB', 'TO', 'UNION', 'UNSIGNED', 'USE', 'UTC_TIME', 'VARBINARY', 'VARYING', 'WITH', 'YEAR_MONTH', '�', 'LOCALTIME', 'SQL_CALC_FOUND_ROWS', '�', 'CONVERT', 'DIV', 'HOUR_MICROSECOND', 'NO_WRITE_TO_BINLOG', 'SPATIAL', 'UTC_TIME');
	private $reserved_words_postgre = array('A', 'ABORT', 'ABS', 'ABSENT', 'ABSOLUTE', 'ACCESS', 'ACCORDING', 'ACTION', 'ADA', 'ADD', 'ADMIN', 'AFTER', 'AGGREGATE', 'ALIAS', 'ALL', 'ALLOCATE', 'ALSO', 'ALTER', 'ALWAYS', 'ANALYSE', 'ANALYZE', 'AND', 'ANY', 'ARE', 'ARRAY', 'ARRAY_AGG', 'AS', 'ASC', 'ASENSITIVE', 'ASSERTION', 'ASSIGNMENT', 'ASYMMETRIC', 'AT', 'ATOMIC', 'ATTRIBUTE', 'ATTRIBUTES', 'AUTHORIZATION', 'AVG', 'BACKWARD', 'BASE64', 'BEFORE', 'BEGIN', 'BERNOULLI', 'BETWEEN', 'BIGINT', 'BINARY', 'BIT', 'BITVAR', 'BIT_LENGTH', 'BLOB', 'BLOCKED', 'BOM', 'BOOLEAN', 'BOTH', 'BREADTH', 'BY', 'C', 'CACHE', 'CALL', 'CALLED', 'CARDINALITY', 'CASCADE', 'CASCADED', 'CASE', 'CAST', 'CATALOG', 'CATALOG_NAME', 'CEIL', 'CEILING', 'CHAIN', 'CHAR', 'CHARACTER', 'CHARACTERISTICS', 'CHARACTERS', 'CHARACTER_LENGTH', 'CHARACTER_SET_CATALOG', 'CHARACTER_SET_NAME', 'CHARACTER_SET_SCHEMA', 'CHAR_LENGTH', 'CHECK', 'CHECKED', 'CHECKPOINT', 'CLASS', 'CLASS_ORIGIN', 'CLOB', 'CLOSE', 'CLUSTER', 'COALESCE', 'COBOL', 'COLLATE', 'COLLATION', 'COLLATION_CATALOG', 'COLLATION_NAME', 'COLLATION_SCHEMA', 'COLLECT', 'COLUMN', 'COLUMNS', 'COLUMN_NAME', 'COMMAND_FUNCTION', 'COMMAND_FUNCTION_CODE', 'COMMENT', 'COMMENTS', 'COMMIT', 'COMMITTED', 'COMPLETION', 'CONCURRENTLY', 'CONDITION', 'CONDITION_NUMBER', 'CONFIGURATION', 'CONNECT', 'CONNECTION', 'CONNECTION_NAME', 'CONSTRAINT', 'CONSTRAINTS', 'CONSTRAINT_CATALOG', 'CONSTRAINT_NAME', 'CONSTRAINT_SCHEMA', 'CONSTRUCTOR', 'CONTAINS', 'CONTENT', 'CONTINUE', 'CONTROL', 'CONVERSION', 'CONVERT', 'COPY', 'CORR', 'CORRESPONDING', 'COST', 'COUNT', 'COVAR_POP', 'COVAR_SAMP', 'CREATE', 'CROSS', 'CSV', 'CUBE', 'CUME_DIST', 'CURRENT', 'CURRENT_CATALOG', 'CURRENT_DATE', 'CURRENT_DEFAULT_TRANSFORM_GROUP', 'CURRENT_PATH', 'CURRENT_ROLE', 'CURRENT_SCHEMA', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURRENT_TRANSFORM_GROUP_FOR_TYPE', 'CURRENT_USER', 'CURSOR', 'CURSOR_NAME', 'CYCLE', 'DATA', 'DATABASE', 'DATALINK', 'DATE', 'DATETIME_INTERVAL_CODE', 'DATETIME_INTERVAL_PRECISION', 'DAY', 'DB', 'DEALLOCATE', 'DEC', 'DECIMAL', 'DECLARE', 'DEFAULT', 'DEFAULTS', 'DEFERRABLE', 'DEFERRED', 'DEFINED', 'DEFINER', 'DEGREE', 'DELETE', 'DELIMITER', 'DELIMITERS', 'DENSE_RANK', 'DEPTH', 'DEREF', 'DERIVED', 'DESC', 'DESCRIBE', 'DESCRIPTOR', 'DESTROY', 'DESTRUCTOR', 'DETERMINISTIC', 'DIAGNOSTICS', 'DICTIONARY', 'DISABLE', 'DISCARD', 'DISCONNECT', 'DISPATCH', 'DISTINCT', 'DLNEWCOPY', 'DLPREVIOUSCOPY', 'DLURLCOMPLETE', 'DLURLCOMPLETEONLY', 'DLURLCOMPLETEWRITE', 'DLURLPATH', 'DLURLPATHONLY', 'DLURLPATHWRITE', 'DLURLSCHEME', 'DLURLSERVER', 'DLVALUE', 'DO', 'DOCUMENT', 'DOMAIN', 'DOUBLE', 'DROP', 'DYNAMIC', 'DYNAMIC_FUNCTION', 'DYNAMIC_FUNCTION_CODE', 'EACH', 'ELEMENT', 'ELSE', 'EMPTY', 'ENABLE', 'ENCODING', 'ENCRYPTED', 'END', 'END-EXEC', 'ENUM', 'EQUALS', 'ESCAPE', 'EVERY', 'EXCEPT', 'EXCEPTION', 'EXCLUDE', 'EXCLUDING', 'EXCLUSIVE', 'EXEC', 'EXECUTE', 'EXISTING', 'EXISTS', 'EXP', 'EXPLAIN', 'EXTENSION', 'EXTERNAL', 'EXTRACT', 'FALSE', 'FAMILY', 'FETCH', 'FILE', 'FILTER', 'FINAL', 'FIRST', 'FIRST_VALUE', 'FLAG', 'FLOAT', 'FLOOR', 'FOLLOWING', 'FOR', 'FORCE', 'FOREIGN', 'FORTRAN', 'FORWARD', 'FOUND', 'FREE', 'FREEZE', 'FROM', 'FS', 'FULL', 'FUNCTION', 'FUNCTIONS', 'FUSION', 'G', 'GENERAL', 'GENERATED', 'GET', 'GLOBAL', 'GO', 'GOTO', 'GRANT', 'GRANTED', 'GREATEST', 'GROUP', 'GROUPING', 'HANDLER', 'HAVING', 'HEADER', 'HEX', 'HIERARCHY', 'HOLD', 'HOST', 'HOUR', 'ID', 'IDENTITY', 'IF', 'IGNORE', 'ILIKE', 'IMMEDIATE', 'IMMUTABLE', 'IMPLEMENTATION', 'IMPLICIT', 'IMPORT', 'IN', 'INCLUDING', 'INCREMENT', 'INDENT', 'INDEX', 'INDEXES', 'INDICATOR', 'INFIX', 'INHERIT', 'INHERITS', 'INITIALIZE', 'INITIALLY', 'INLINE', 'INNER', 'INOUT', 'INPUT', 'INSENSITIVE', 'INSERT', 'INSTANCE', 'INSTANTIABLE', 'INSTEAD', 'INT', 'INTEGER', 'INTEGRITY', 'INTERSECT', 'INTERSECTION', 'INTERVAL', 'INTO', 'INVOKER', 'IS', 'ISNULL', 'ISOLATION', 'ITERATE', 'JOIN', 'K', 'KEY', 'KEY_MEMBER', 'KEY_TYPE', 'LABEL', 'LAG', 'LANGUAGE', 'LARGE', 'LAST', 'LAST_VALUE', 'LATERAL', 'LC_COLLATE', 'LC_CTYPE', 'LEAD', 'LEADING', 'LEAST', 'LEFT', 'LENGTH', 'LESS', 'LEVEL', 'LIBRARY', 'LIKE', 'LIKE_REGEX', 'LIMIT', 'LINK', 'LISTEN', 'LN', 'LOAD', 'LOCAL', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCATION', 'LOCATOR', 'LOCK', 'LOWER', 'M', 'MAP', 'MAPPING', 'MATCH', 'MATCHED', 'MAX', 'MAXVALUE', 'MAX_CARDINALITY', 'MEMBER', 'MERGE', 'MESSAGE_LENGTH', 'MESSAGE_OCTET_LENGTH', 'MESSAGE_TEXT', 'METHOD', 'MIN', 'MINUTE', 'MINVALUE', 'MOD', 'MODE', 'MODIFIES', 'MODIFY', 'MODULE', 'MONTH', 'MORE', 'MOVE', 'MULTISET', 'MUMPS', 'NAME', 'NAMES', 'NAMESPACE', 'NATIONAL', 'NATURAL', 'NCHAR', 'NCLOB', 'NESTING', 'NEW', 'NEXT', 'NFC', 'NFD', 'NFKC', 'NFKD', 'NIL', 'NO', 'NONE', 'NORMALIZE', 'NORMALIZED', 'NOT', 'NOTHING', 'NOTIFY', 'NOTNULL', 'NOWAIT', 'NTH_VALUE', 'NTILE', 'NULL', 'NULLABLE', 'NULLIF', 'NULLS', 'NUMBER', 'NUMERIC', 'OBJECT', 'OCCURRENCES_REGEX', 'OCTETS', 'OCTET_LENGTH', 'OF', 'OFF', 'OFFSET', 'OIDS', 'OLD', 'ON', 'ONLY', 'OPEN', 'OPERATION', 'OPERATOR', 'OPTION', 'OPTIONS', 'OR', 'ORDER', 'ORDERING', 'ORDINALITY', 'OTHERS', 'OUT', 'OUTER', 'OUTPUT', 'OVER', 'OVERLAPS', 'OVERLAY', 'OVERRIDING', 'OWNED', 'OWNER', 'P', 'PAD', 'PARAMETER', 'PARAMETERS', 'PARAMETER_MODE', 'PARAMETER_NAME', 'PARAMETER_ORDINAL_POSITION', 'PARAMETER_SPECIFIC_CATALOG', 'PARAMETER_SPECIFIC_NAME', 'PARAMETER_SPECIFIC_SCHEMA', 'PARSER', 'PARTIAL', 'PARTITION', 'PASCAL', 'PASSING', 'PASSTHROUGH', 'PASSWORD', 'PATH', 'PERCENTILE_CONT', 'PERCENTILE_DISC', 'PERCENT_RANK', 'PERMISSION', 'PLACING', 'PLANS', 'PLI', 'POSITION', 'POSITION_REGEX', 'POSTFIX', 'POWER', 'PRECEDING', 'PRECISION', 'PREFIX', 'PREORDER', 'PREPARE', 'PREPARED', 'PRESERVE', 'PRIMARY', 'PRIOR', 'PRIVILEGES', 'PROCEDURAL', 'PROCEDURE', 'PUBLIC', 'QUOTE', 'RANGE', 'RANK', 'READ', 'READS', 'REAL', 'REASSIGN', 'RECHECK', 'RECOVERY', 'RECURSIVE', 'REF', 'REFERENCES', 'REFERENCING', 'REGR_AVGX', 'REGR_AVGY', 'REGR_COUNT', 'REGR_INTERCEPT', 'REGR_R2', 'REGR_SLOPE', 'REGR_SXX', 'REGR_SXY', 'REGR_SYY', 'REINDEX', 'RELATIVE', 'RELEASE', 'RENAME', 'REPEATABLE', 'REPLACE', 'REPLICA', 'REQUIRING', 'RESET', 'RESPECT', 'RESTART', 'RESTORE', 'RESTRICT', 'RESULT', 'RETURN', 'RETURNED_CARDINALITY', 'RETURNED_LENGTH', 'RETURNED_OCTET_LENGTH', 'RETURNED_SQLSTATE', 'RETURNING', 'RETURNS', 'REVOKE', 'RIGHT', 'ROLE', 'ROLLBACK', 'ROLLUP', 'ROUTINE', 'ROUTINE_CATALOG', 'ROUTINE_NAME', 'ROUTINE_SCHEMA', 'ROW', 'ROWS', 'ROW_COUNT', 'ROW_NUMBER', 'RULE', 'SAVEPOINT', 'SCALE', 'SCHEMA', 'SCHEMA_NAME', 'SCOPE', 'SCOPE_CATALOG', 'SCOPE_NAME', 'SCOPE_SCHEMA', 'SCROLL', 'SEARCH', 'SECOND', 'SECTION', 'SECURITY', 'SELECT', 'SELECTIVE', 'SELF', 'SENSITIVE', 'SEQUENCE', 'SEQUENCES', 'SERIALIZABLE', 'SERVER', 'SERVER_NAME', 'SESSION', 'SESSION_USER', 'SET', 'SETOF', 'SETS', 'SHARE', 'SHOW', 'SIMILAR', 'SIMPLE', 'SIZE', 'SMALLINT', 'SOME', 'SOURCE', 'SPACE', 'SPECIFIC', 'SPECIFICTYPE', 'SPECIFIC_NAME', 'SQL', 'SQLCODE', 'SQLERROR', 'SQLEXCEPTION', 'SQLSTATE', 'SQLWARNING', 'SQRT', 'STABLE', 'STANDALONE', 'START', 'STATE', 'STATEMENT', 'STATIC', 'STATISTICS', 'STDDEV_POP', 'STDDEV_SAMP', 'STDIN', 'STDOUT', 'STORAGE', 'STRICT', 'STRIP', 'STRUCTURE', 'STYLE', 'SUBCLASS_ORIGIN', 'SUBLIST', 'SUBMULTISET', 'SUBSTRING', 'SUBSTRING_REGEX', 'SUM', 'SYMMETRIC', 'SYSID', 'SYSTEM', 'SYSTEM_USER', 'T', 'TABLE', 'TABLES', 'TABLESAMPLE', 'TABLESPACE', 'TABLE_NAME', 'TEMP', 'TEMPLATE', 'TEMPORARY', 'TERMINATE', 'TEXT', 'THAN', 'THEN', 'TIES', 'TIME', 'TIMESTAMP', 'TIMEZONE_HOUR', 'TIMEZONE_MINUTE', 'TO', 'TOKEN', 'TOP_LEVEL_COUNT', 'TRAILING', 'TRANSACTION', 'TRANSACTIONS_COMMITTED', 'TRANSACTIONS_ROLLED_BACK', 'TRANSACTION_ACTIVE', 'TRANSFORM', 'TRANSFORMS', 'TRANSLATE', 'TRANSLATE_REGEX', 'TRANSLATION', 'TREAT', 'TRIGGER', 'TRIGGER_CATALOG', 'TRIGGER_NAME', 'TRIGGER_SCHEMA', 'TRIM', 'TRIM_ARRAY', 'TRUE', 'TRUNCATE', 'TRUSTED', 'TYPE', 'UESCAPE', 'UNBOUNDED', 'UNCOMMITTED', 'UNDER', 'UNENCRYPTED', 'UNION', 'UNIQUE', 'UNKNOWN', 'UNLINK', 'UNLISTEN', 'UNLOGGED', 'UNNAMED', 'UNNEST', 'UNTIL', 'UNTYPED', 'UPDATE', 'UPPER', 'URI', 'USAGE', 'USER', 'USER_DEFINED_TYPE_CATALOG', 'USER_DEFINED_TYPE_CODE', 'USER_DEFINED_TYPE_NAME', 'USER_DEFINED_TYPE_SCHEMA', 'USING', 'VACUUM', 'VALID', 'VALIDATE', 'VALIDATOR', 'VALUE', 'VALUES', 'VARBINARY', 'VARCHAR', 'VARIABLE', 'VARIADIC', 'VARYING', 'VAR_POP', 'VAR_SAMP', 'VERBOSE', 'VERSION', 'VIEW', 'VOLATILE', 'WHEN', 'WHENEVER', 'WHERE', 'WHITESPACE', 'WIDTH_BUCKET', 'WINDOW', 'WITH', 'WITHIN', 'WITHOUT', 'WORK', 'WRAPPER', 'WRITE', 'XML', 'XMLAGG', 'XMLATTRIBUTES', 'XMLBINARY', 'XMLCAST', 'XMLCOMMENT', 'XMLCONCAT', 'XMLDECLARATION', 'XMLDOCUMENT', 'XMLELEMENT', 'XMLEXISTS', 'XMLFOREST', 'XMLITERATE', 'XMLNAMESPACES', 'XMLPARSE', 'XMLPI', 'XMLQUERY', 'XMLROOT', 'XMLSCHEMA', 'XMLSERIALIZE', 'XMLTABLE', 'XMLTEXT', 'XMLVALIDATE', 'YEAR', 'YES', 'ZONE');
	
	// database types
	const db_type_mysql     = 'mysql'; /**< database type MySQL */
	const db_type_postgre   = 'postgresql'; /**< database type postgreSQL */
	
	private $db_type;
	private $debug;
	
	// constants for data type classification
	const dtype_numeric = 'N'; /**< datatype mark for numeric values */
	const dtype_string  = 'S'; /**< datatype mark for string (character) values */
	const dtype_unknown = 'U'; /**< datatype mark for anything unknown */
	
	/**
	 * \brief Constructor.
	 *
	 * \param $type the database type the optimizer has to work for
	 */
	 function __construct($type)
	 {
		$this->db_type = $type;
	 }
	 
	 /**
	  * \brief Sets debug information level
	  *
	  *  The level of debug information is set. Currently every debug level > 1 will print any available
	  *  debug information.
	  *
	  */
	 function setDebug($level)
	 {
		$this->debug = $level;
	 }
	 
	 /**
	  * \brief Formats the given parameter to be a string in an SQL query.
	  *
	  *  By using this method the given parameter is formatted so it can be used as a
	  *  string-based type like \c VARCHAR or \c TEXT in an SQL query.
	  *
	  * \param $what the string you want to parse
	  * \returns A formated value.
	  */
	 function formatString($what)
	 {
		// luckily MySQL and postgre use mostly the same string qualifier...
		return "'$what'";
	 }
	 
	 /**
	  * \brief Formats the parameter to be a number in an SQL query.
	  *
	  *  By using this method the given parameter is formatted so it can be used as a
	  *  string-based type like \c SERIAL or \c INTEGER in an SQL query.
	  *
	  * \param $what the string you want to parse
	  * \returns A formated value. 
	  */
	 function formatNumber($what)
	 {
		// we don't do anything with numbers... this method is just for continuity
		return $what;
	 }
	 
	 /**
	  * \brief Formats the parameter to be used as a date in a SQL query.
	  *
	  * By using this method the given parameter is formatted so it can be used as
	  * a date-based type like \c DATE or \c TIMESTAMP in an SQL query on the target
	  * system. This functions only formats a return value of the time() function!
	  *
	  * \params $what the string you want to format
	  * \returns a formatted value
	  */
	 function formatDate($what)
	 {
		if($this->db_type == self::db_type_mysql)
			$what = "FROM_UNIXTIME($what)";
		else if($this->db_type == self::db_type_postgre)
			$what = "to_timestamp($what)";
		return $what;
	 }
	 
	 /**
	  * \brief Formats a list of values to an according list of data types.
	  *
	  *  This functions formats an array of values to different datatypes. The datatypes are given
	  *  in a separate array. Please keep in mind that each element in the value array
	  *  has to correspond to one element on the same position in the datatype array!
	  *
	  * \param $values an array of values that shall be formatted
	  * \param $dtypes a list of datatypes that the values shall be formatted to
	  */
	 function formatFields($values, $dtypes)
	 {
		if(!is_array($values)) $values = array($values);
		if(!is_array($dtypes)) $dtypes = array($dtypes);
		
		// both arrays should be of the same size... if not i don't care...
		for($i=0; $i < count($values) && $i < count($dtypes); $i++)
			$values[$i] = $this->formatField($values[$i], $dtypes[$i]);
		
		return $values;
	 }
	 
	 /**
	  * \brief Formats one input field according to it's data type.
	  *
	  * \param $value the value to be formatted
	  * \param $dtype the datatype
	  * \returns formatted value
	  */
	 function formatField($value, $dtype)
	 {
		switch($dtype)
			{
				case SapphoTableStructure::dtype_numeric:
					$value = $this->formatNumber($value);
					break;
				case SapphoTableStructure::dtype_string:
					$value = $this->formatString($value);
					break;
				case SapphoTableStructure::dtype_date:
					$value = $this->formatDate($value);
					break;
				default:
					break;
			}
		return $value;
	 }
	
	/**
	 * \brief Escape reserved words in the string.
	 *
	 * It is checked if the given string contains words that are reserved in the used database. Such words
	 * are returned correctly quoted so that the returned string may be used in a command for that database.
	 *
	 * \param $string the string that has to bechecked
	 *
	 * \returns an escaped version of the string
	 */
	function escape_reserved_words($string)
	{
		if($this->debug > 0)
			echo "SynOpt: got $string<br/>";
			
		$words = explode(" ", $string);
		
		for($i=0; $i<count($words); $i++)
		{
			$res_words = array();
			if($this->db_type == self::db_type_mysql)
				$res_words = $this->reserved_words_mysql;
			else if($this->db_type == self::db_type_postgre)
				$res_words = $this->reserved_words_postgre;
				
			if(in_array(strtoupper($words[$i]), $res_words))
			{
				if($this->debug > 0)
					echo "SynOpt: escaping ".$words[$i]."<br/>";
				switch($this->db_type)
				{
					case self::db_type_mysql:
						$words[$i] = "`$words[$i]`";
						break;
					case self::db_type_postgre:
						$words[$i] = "\"".$words[$i]."\"";
						break;
				}
			}
		}
		
		$string = implode($words, " ");
		return $string;
	}
}