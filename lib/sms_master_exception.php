<?php

/**
	@brief		Exception wrapper and base class.
**/
class SMS_Master_Exception
	extends Exception
{
	public $error;
	
	protected $error_messages;		// An array of ERROR_CONST => 'English error message'

	public function __construct( $error )
	{
		$this->error = $error;
	}
	
	public function get_error()
	{
		return $this->error;
	}

	public function get_error_message()
	{
		if ( isset($this->error_messages[ $this->error ]) )
			return $this->error_messages[ $this->error ];
		else
			return $this->error;
	}
}

class SMS_Master_Connection_Exception extends SMS_Master_Exception
{
	const ERROR_UNABLE_TO_CONNECT = 'ERROR_UNABLE_TO_CONNECT';
	const ERROR_REPLY_NOT_SERIALIZED = 'ERROR_NOT_SERIALIZED';
	const ERROR_INVALID_MASTER_PUBLIC_KEY = 'ERROR_INVALID_MASTER_PUBLIC_KEY';
	
	protected $error_messages = array(
		self::ERROR_UNABLE_TO_CONNECT => 'Unable to connect.',
		self::ERROR_REPLY_NOT_SERIALIZED => 'The reply was invalid: it was not serialized.',
		self::ERROR_INVALID_MASTER_PUBLIC_KEY => 'Invalid master public key.',
	);
}

class SMS_Master_Order_Not_Found_Exception extends SMS_Master_Exception
{
	const ERROR_ORDER_NOT_FOUND = 'ERROR_ORDER_NOT_FOUND';
	
	protected $error_messages = array(
		self::ERROR_ORDER_NOT_FOUND => 'Order not found.',
	);

	public function __construct()
	{
		parent::__construct( self::ERROR_ORDER_NOT_FOUND ); 
	}
}

class SMS_Master_Phone_Not_Found_Exception extends SMS_Master_Exception
{
	const ERROR_PHONE_NOT_FOUND = 'ERROR_PHONE_NOT_FOUND';
	
	protected $error_messages = array(
		self::ERROR_PHONE_NOT_FOUND => 'Phone not found.',
	);

	public function __construct()
	{
		parent::__construct( self::ERROR_PHONE_NOT_FOUND ); 
	}
}

class SMS_Master_Slave_Not_Found_Exception extends SMS_Master_Exception
{
	const ERROR_SLAVE_NOT_FOUND = 'ERROR_SLAVE_NOT_FOUND';
	
	protected $error_messages = array(
		self::ERROR_SLAVE_NOT_FOUND => 'Slave not found.',
	);

	public function __construct()
	{
		parent::__construct( self::ERROR_SLAVE_NOT_FOUND ); 
	}
}

class SMS_Master_User_Exception extends SMS_Master_Exception
{
	const ERROR_UNABLE_TO_DELETE_SELF = 'ERROR_UNABLE_TO_CONNECT';
	const ERROR_USER_NOT_FOUND = 'ERROR_USER_NOT_FOUND';
	
	protected $error_messages = array(
		self::ERROR_UNABLE_TO_DELETE_SELF => 'User may not delete himself. Use another user.',
		self::ERROR_USER_NOT_FOUND => 'User not found.',
	);
}

