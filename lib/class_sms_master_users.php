<?php
/**
	@brief		User handling part of SMS master.
**/
class class_SMS_Master_Users extends class_SMS_Master_Generic
{
	public $updateable_columns = array(
		'administrator',
		'enabled',
		'public_key',
		'public_key_id',
		'user_description',
	);
	
	/**
		@brief		Returns a count of all the users.
		
		@return		false|integer			How many users are in the database. False if there is no users table at all.
	**/
	public function count()
	{
		try
		{
			$result = $this->db->fetchAll( 'SELECT COUNT(*) AS count FROM users' ) ;
			return $result[0]['count'];
		}
		catch( Exception $e )
		{
			return false;
		}
	}
	
	/**
		@brief		Create a new user.
		
		@param		object		$options		Values for the user. See the database.
		@return		integer		The user ID of the created user.
	**/
	public function create( $options )
	{
		if ( ! isset( $options->public_key ) )
			$options->public_key = SMS_Master::hash( microtime() );
		if ( ! isset( $options->public_key_id ) )
			$options->public_key_id = SMS_Master::hash( microtime() );
		$this->db->executeUpdate( "INSERT INTO `users`
			(`datetime_created`, `public_key_id`)
			VALUES
			( now(), '". $options->public_key_id ."')" );
		$options->user_id = $this->db->lastInsertID();
		$this->update( $options->user_id, $options );
		return $options->user_id;
	}
	
	/**
		@brief		Deletes a user.
		
		@param		int		$user_id		ID of user to remove.
		@throws		SMS_Master_User_Exception if the specified user_id does not exist.
	**/
	public function delete( $user_id )
	{
		// Make sure the user exists first.
		$this->get( $user_id );
		
		$this->db->delete( 'users', array('user_id' => $user_id) );
	}
	
	/**
		@brief		Finds a user using the the specified key + value criteria.
		
		@param		string			$key		Key to look in.
		@param		string			$value		Value to search for.
		@return		false|object	The database row if the user was found, else false.
	**/
	public function find( $key, $value )
	{
		$result = $this->db->fetchAll( 'SELECT * FROM `users` WHERE `'.$key.'` = ?', array( $value ) ) ;
		if ( count( $result ) !== 1 )
			return false;
		else
			return (object)$result[0];
	}
	
	/**
		@brief		Returns the datbase row of the specified user.
		
		@param		integer		$user_id		The ID of the user.
		@return		object		Database row of user.
		@throws		SMS_Master_User_Exception if the specified user_id does not exist.
	**/ 
	public function get( $user_id )
	{
		$user = $this->find( 'user_id', $user_id );
		if ( $user === false )
			throw new SMS_Master_User_Exception( SMS_Master_User_Exception::ERROR_USER_NOT_FOUND );
		else
			return $user;
	}
	
	/**
		@brief		Lists all users.
		
		@return		array		Array of user objects.
	**/
	public function list_()
	{
		$results = $this->db->fetchAll( 'SELECT * FROM users ORDER BY `user_description`, `public_key`' ) ;
		return $this->arrays_to_objects( $results );
	}

	/**
		@brief		Updates a phone's settings.
		
		@param		integer		$user_id		User ID to update.
		@param		object		$data			New date to write.
	**/
	public function update( $user_id, $data )
	{
		// Make sure the user exists first.
		$this->get( $user_id );
		
		$q = $this->db->q()
        	->update( 'users', 'u' )
        	->where( 'u.user_id = :user_id' )
        	->setParameter( 'user_id', $user_id );
        
       	$data->public_key = trim( $data->public_key );
        // Update the public key id automatically.
        $data->public_key_id = SMS_Master::get_public_key_id( $data->public_key );
        
        foreach( $this->updateable_columns as $column )
        	if ( isset( $data->$column ) )
        		$q->set( 'u.' . $column, $data->$column );
        	
        $q->execute();
	}
}

