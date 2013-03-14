<?php
/**
	@brief		Class for logging.
**/
class class_SMS_Master_Log
	extends class_SMS_Master_Generic
{
	/**
		@brief		Generic logging function.
		
		Options is:
		- @e string @b level	The level: debug, info or error.
		- @e string @b text		Text to log.
		
		@param		array		$options		Event data.
	**/
	public function add( $options )
	{
		$options['datetime'] = new \DateTime('now');

		$this->db->executeUpdate("INSERT INTO log (`datetime`, `level`, `message`) VALUES (:datetime, :level, :message)",
			$options,
			array(
				'datetime' => \Doctrine\DBAL\Types\Type::getType('datetime'),
			)
		);
	}
	
	/**
		@brief		Log debug information.
		
		Debug events are generated automatically, without any special user interaction.
		
		Uses sprintf substitution with all extra parameters, so you can use %s in the $message.
		
		@param		string		$message		Event text to add.
	**/
	public function debug( $message )
	{
		$message = call_user_func_array( 'sprintf', func_get_args() );
		$this->add( array(
			'level' => 'debug',
			'message' => $message,
		) );
	}
	
	/**
		@brief		Log an error.
		
		Errors are things that cause SMS_Master to from normal usage.
		
		Uses sprintf substitution with all extra parameters, so you can use %s in the $message.
		
		@param		string		$message		Event text to add.
	**/
	public function error( $message )
	{
		$message = call_user_func_array( 'sprintf', func_get_args() );
		$this->add( array(
			'level' => 'error',
			'message' => $message,
		) );
	}
	
	/**
		@brief		Log information.
		
		Info events can be new orders placed, reports sent, phones cleaned, etc.
		
		Uses sprintf substitution with all extra parameters, so you can use %s in the $message.
		
		@param		string		$message		Event text to add.
	**/
	public function info( $message )
	{
		$message = call_user_func_array( 'sprintf', func_get_args() );
		$this->add( array(
			'level' => 'info',
			'message' => $message,
		) );
	}
}
