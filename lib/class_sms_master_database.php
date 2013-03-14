<?php
require_once( 'doctrine-dbal/Doctrine/Common/ClassLoader.php' );
require_once( 'doctrine-dbal/Doctrine/DBAL/Query/QueryBuilder.php' );

use Doctrine\Common\ClassLoader;
/**
	@brief		Prepares a Doctrina DBAL connection.
	
	The $database_settings array requires
	- string @b hostname The name of the database server.
	- string @b database Database to use.
	- string @b username Username with which to connect.
	- string @b password Related password.
	
	@param		array		$database_settings		Array of database settings.
**/
function doctrine_init($database_settings)
{
	$classLoader = new ClassLoader('Doctrine', dirname( __FILE__ ) . '/doctrine-dbal/');
	$classLoader->register();
	 
	$config = new \Doctrine\DBAL\Configuration();

	$conn = \Doctrine\DBAL\DriverManager::getConnection(array(
		'charset' 	=> 'utf8',
	    'dbname'    => $database_settings['database'],
	    'driver'    => 'pdo_mysql',
	    'host'      => $database_settings['hostname'],
	    'password'  => $database_settings['password'],
	    'user'      => $database_settings['username'],
	));
	
	return $conn;
}

/**
	@brief		SMS Master database class.
	
	Can be used pretty much like Doctrine's connection class.
**/
class class_SMS_Master_Database
{
	/**
		@brief		The Doctrine connection.
		@var		$conn
	**/
	public $conn;
	
	/**
		@brief		SMS_Master class.
		@var		$sms_master
	**/
	public $sms_master;
	
	public function __construct( $sms_master )
	{
		$this->sms_master = $sms_master;
		$database_settings = $sms_master->config[ 'database' ];
		$this->conn = doctrine_init( $database_settings );
		return $this->conn;
	}
	
	/**
		@brief		Construct a QueryBuilder.
		@see		class_SMS_Master_Database_QueryBuilder
	**/
	public function q()
	{
		return new class_SMS_Master_Database_QueryBuilder( $this->conn );
	}
	
	/**
		@brief		Call the Doctrine connection's equivalent function.
		
		@param		string		$function		The Doctrine connection function to call.
		@param		array		$args			Function parameters.
		@return		mixed		Whatever the Doctrine connection function returns.
	**/
	protected function call_conn( $function, $args )
	{
		return call_user_func_array( array( $this->conn, $function ), $args ); 
	}
	
	/**
		@brief		Delete query.
	**/
	public function delete()
	{
		return $this->call_conn( 'delete', func_get_args() ); 
	}
	
	/**
		@brief		Create an executeQuery query.
	**/
	public function executeQuery()
	{
		return $this->call_conn( 'executeQuery', func_get_args() ); 
	}
	
	/**
		@brief		Createan update query.
	**/
	public function executeUpdate()
	{
		return $this->call_conn( 'executeUpdate', func_get_args() ); 
	}
	
	/**
		@brief		Execute a query and return the results.
	**/
	public function fetchAll()
	{
		return $this->call_conn( 'fetchAll', func_get_args() ); 
	}
	
	/**
		@brief		Create an insert query.
	**/
	public function insert()
	{
		return $this->call_conn( 'insert', func_get_args() ); 
	}
	
	/**
		@brief		Returns the latest insert ID.
	**/
	public function lastInsertID()
	{
		return $this->call_conn( 'lastInsertID', func_get_args() ); 
	}
	
	/**
		@brief		Create an update query.
	**/
	public function update()
	{
		return $this->call_conn( 'update', func_get_args() ); 
	}
}

use Doctrine\DBAL\Query\QueryBuilder;
/**
	@brief		Wrapper for Doctrine's QueryBuilder.
	
	Used mostly because the ->set function is smarter.
**/
class class_SMS_Master_Database_QueryBuilder
	extends QueryBuilder
{
	/**
		@brief		Execute a query.
		
		Overloaded to check if there is anything to do. Normally, executing without having done anything results in an SQL exception.
		
		Stupid, really.
	**/
	public function execute()
	{
		$sqlParts = $this->getQueryParts();
		if ( count( $sqlParts[ 'set' ] ) < 1 )
			return;
		call_user_func_array(array('parent', 'execute'), func_get_args() );
	}
	
	/**
		@brief		Set a parameter.
		
		Differs from QueryBuilder's set by:
		- True and false are set to their integer equivalents.
		- Strings are wrapped in literal expressions.
		
		@param		string		$key		Name of key in table.
		@param		mixed		$value		Value to set key to.
	**/
	public function set( $key, $value )
	{
		if ( is_string( $value ) )
			$value = $this->expr()->literal( $value );
		if ( $value === true || $value === false )
			$value = intval( $value );
		if ( $value === null )
			$value = 'null';
		parent::set( $key, $value );
	}
	
	/**
		@brief		Wraps a string in a literal string expression.
	**/
	public function string( $string )
	{
		return $this->expr()->literal( $string );
	}
}

