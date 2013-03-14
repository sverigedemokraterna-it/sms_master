<?php
/**
	@brief		Slave handling.
**/
class class_SMS_Master_Slaves extends class_SMS_Master_Generic
{
	/**
		@brief		Which table columns are updateable using update().
		@var		$updateable_columns
		@see		update
	**/
	public $updateable_columns = array(
		'hostname',
		'port',
		'private_key',
		'public_key',
		'slave_description',
		'username',
	);
	
	/**
		@brief		Add a new slave.
		
		See the update method for a list of creation properties.
		
		@param		object		$options		Options for slave.
		@see		$updateable_columns
		@see		update
	**/
	public function create( $options )
	{
		$this->db->executeUpdate( "INSERT INTO slaves () VALUES ()" );
		$options->slave_id = $this->db->lastInsertID();
		$this->update( $options->slave_id, $options );
		return $options->slave_id;
	}
	
	/**
		@brief		Removes a slave.
		
		@param		int		$slave_id		ID of slave to remove.
		@throws		SMS_Master_Slave_Not_Found_Exception if the specified slave_id does not exist.
	**/
	public function delete( $slave_id )
	{
		// Get it, to make sure it exists. Will throw an exception otherwise.
		$slave = $this->get( $slave_id );
		
		// Get a list of all the phones this master has
		$phones = $this->sms_master->phones->list_from_slave_id( $slave_id );
		// Delete all the associated phones.
		foreach( $phones as $phone )
			$this->sms_master->phones->delete( $phone->phone_id );
		
		$this->db->delete( 'slaves',
			array('slave_id' => $slave_id)
		);
	}
	
	/**
		@brief		Returns a slave database row object.
		
		@param		int		$slave_id		ID of slave to retrieve.
		@return		The slave database row object.
		@throws		SMS_Master_Slave_Not_Found_Exception if the specified slave_id does not exist.
	**/
	public function get( $slave_id )
	{
		$result = $this->db->fetchAll( 'SELECT * FROM slaves WHERE slave_id = ?', array( $slave_id ) ) ;
		if ( count( $result ) !== 1 )
			throw new SMS_Master_Slave_Not_Found_Exception();
		else
			return (object)$result[0];
	}
	
	/**
		@brief		Identify a slave.
		
		Calls the two commands whoami and hostname on the slave. This function can be used as a connection test,
		without requiring at phone.
		
		@param		int			$slave_id		ID of slave to identify.
		@return		object		The object from SMS_Master::send_slave_command
		@throws		SMS_Master_Slave_Not_Found_Exception if the specified slave_id does not exist.
		@see		SMS_Master::send_slave_command
	**/
	public function identify( $slave_id )
	{
		$slave = $this->get( $slave_id );
		return $this->sms_master->send_slave_command( $slave, 'whoami && hostname' );
	}
	
	/**
		@brief		List all available slaves.
		
		@return		array		List of all slaves, sorted by description, hostname.
	**/
	public function list_()
	{
		$results = $this->db->fetchAll( "SELECT * FROM slaves ORDER BY slave_description, hostname" ) ;
		$rv = array();
		foreach( $results as $result )
		{
			$id = $result[ 'slave_id' ];
			$rv[ $id ] = (object) $result;
		}
		return $rv;
	}
	
	/**
		@brief		Updates a slave with new data.
		
		@param		int		$slave_id		The slave ID to update.
		@param		object	$data			The new data to write.
		@throws		SMS_Master_Slave_Not_Found_Exception if the specified slave_id does not exist.
		@see		$updateable_columns
	**/
	public function update( $slave_id, $data )
	{
		$slave = $this->get( $slave_id );
		$q = $this->db->q()
        	->update( 'slaves', 's' )
        	->where( 's.slave_id = :slave_id' )
        	->setParameter( 'slave_id', $slave_id );
        
        foreach( $this->updateable_columns as $column )
        	if ( isset( $data->$column ) )
        		$q->set( 's.' . $column, $data->$column );
        	
        $q->execute();
	}	
}

