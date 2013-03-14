<?php
/**
	@brief		A client request.
	
	Clients send requests to the master, which then handles the requests and sends back a reply.
	
	Technically, the requests handle() themselves.
**/
class SMS_Master_Request
{
	private $uuid;			// Needed to ensure that every request is different, for the sake of the openssl_sign.

	/**
		@brief		A string containing a human-readable error message, if applicable. Else left non-existent.
		@var		$error_message
		@out
	**/
	public $error_message = null;
	
	public function __construct()
	{
		$this->uuid = SMS_Master::hash( microtime(true) ); 
	}
	
	/**
		@brief		Cleans up unused variables.
		
		Is called to clean up all general, common variables before returning the request.
	**/
	public function clean_1()
	{
		unset( $this->sms_master );
		if ( $this->error_message === null )
			unset( $this->error_message );
		unset( $this->loaded_user );
		unset( $this->log );
	}
	
	/**
		@brief		Cleans up unused variables.
		
		Is separate from clean_1 so that the subclasses don't have to call the parent's clean().
	**/
	public function clean()
	{
	}
	
	/**
		@brief		Returns the current error message, if any.
		
		@return		string|false		The error message string, if any, else false.
	**/
	public function get_error_message()
	{
		if ( ! isset( $this->error_message ) )
			return false;
		return $this->error_message;
	}

	/**
		@brief		Pre-handles ourself, so that the subclasses don't have to call the parent all the time.
	**/
	public function handle_1()
	{
	}
	
	/**
		@brief		Handles ourself, with the help of $sms_master.
		
		The default does nothing. Must be overloaded.
	**/
	public function handle()
	{
	}
}

/**
	@brief		A container used to contain the client private encrypted request.
	
	Contains a key id field so that the master can quickly find the correct key to verify against.
**/
class SMS_Master_Request_Container
{
	/**
		@brief		The public key ID of the client.
		
		Used to help the master look up the correct client public key.
		
		@var		$public_key_id
	**/
	public $public_key_id;
	
	/**
		@brief		The SMS_Master_Request itself.
		@var		$request
	**/
	public $request;
	
	/**
		@brief		OpenSSL signature for this request.
		@var		$signature
	**/
	public $signature;
}

/**
	@brief		A container for the reply.
**/
class SMS_Master_Reply_Container
{
	/**
		@brief		The handled SMS_Master_Request.
		@var		$reply
	**/
	public $reply;
}

/**
	@brief		A special container used only for installing a brand new admin.
	
	Used when the system has no users of any kind.
**/
class SMS_Master_Install_Container
	extends SMS_Master_Request_Container
{
}

class SMS_Master_Install_Request
	extends SMS_Master_Request
{
	/**
		@brief		The newly-created admin user on the master.
		@var		$user
		@out
	**/
	public	$user;
}

/**
	@brief		Base class for user requests.
	
	Used to differentiate user requests, with lower access, from admin requests.
**/
class SMS_Master_User_Request extends SMS_Master_Request
{
}

/**
	@brief		Used for testing connections. Replies with a connection timestamp.
**/
class SMS_Master_Test_Connection_Request extends SMS_Master_User_Request
{
	/**
		@brief		Unix timestamp of connection.
		@var		$connection_timestamp
		@out
	**/
	public $connection_timestamp = -1;
	
	public function handle()
	{
		$this->connection_timestamp = time();
	}
}

