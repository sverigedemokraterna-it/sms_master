<?php
/**
	Phone handling part of SMS master.
**/
class class_SMS_Master_Phones
	extends class_SMS_Master_Generic
{
	/**
		@brief		Which table columns are updateable using update().
		@var		$updateable_columns
		@see		update
	**/
	public $updateable_columns = array(
		'clean',
		'enabled',
		'phone_description',
		'phone_index',
		'slave_id',
	);
	
	/**
		@brief		Clean a phone.
		
		The cleaning options are
		
		- @e int @b phone_id The ID of the phone to clean.
		- @e bool @b empty True, to empty the phone of messages afterwards.
		
		@param		object		$options		Cleaning options.
		@see		$updateable_columns
	**/
	public function clean( $options )
	{
		$options = SMS_Master::merge_objects( array(
			'phone_id' => -1,
			'empty' => false,
		), $options );
		
		$phone = $this->get( $options->phone_id );
		$slave = $this->sms_master->slaves->get( $phone->slave_id );
		
		$phone_command = 'gnokii --phone ' . $phone->phone_index . ' --showsmsfolderstatus';
		$command = $this->sms_master->send_slave_command( $slave, $phone_command );
		
		// Analyze the output, and see which memories need to be fetched and cleand.
		$output = $command->output;
	
		// GNOKII Version 0.6.29
		array_shift( $output );
		// No. Name                                         Id #Msg
		array_shift( $output );
		// ========================================================
		array_shift( $output );
		
		$rv = array();
		$memories_to_empty = array();
		
		foreach( $output as $line )
		{
			$line = str_replace( '  ', ' ', $line );
			$line = str_replace( '  ', ' ', $line );
			$columns = explode( ' ', $line );
			$memory = $columns[ count( $columns ) - 2 ];
			$count = $columns[ count( $columns ) - 1 ];
			if ( $count > -1 )
			{
				$memories_to_empty[ $memory ] = $count;
				$rv[] = "Memory $memory has $count messages.";
			}
		}
		
		foreach( $memories_to_empty as $memory => $count )
		{
			if ( $count < 1 )
				continue;
			$rv[] = "Removing $count messages from memory $memory.";
			// First fetch the messages
			$phone_command = 'gnokii --phone ' . $phone->phone_index . ' --getsms ' . $memory . ' 1 ' . $count;
			$command = $this->sms_master->send_slave_command( $slave, $phone_command );
			$rv += $command->output;
	
			if ( $options->empty == true )
			{
				// And now empty the phone
				$phone_command = 'gnokii --phone ' . $phone->phone_index . ' --deletesms ' . $memory . ' 1 ' . $count;
				$command = $this->sms_master->send_slave_command( $slave, $phone_command );
				$rv[] = "Phone has been emptied.";
			}
			else
				$rv[] = "Not emptying.";
		}
		$this->log->info( sprintf( 'Phone %s has been cleaned.%s',
			$phone->phone_id,
			$options->empty === true ? ' Phone emptied.' : ''
		) );
		
		return $rv;
	}
	
	/**
		@brief		Create a new phone.
		
		For a full list of available options, see update().
		@param		object		$options		Options object.
		@see		update
	**/
	public function create( $options )
	{
		$this->db->executeUpdate( "INSERT INTO `phones` () VALUES ()" );
		$options->phone_id = $this->db->lastInsertID();
		$this->update( $options->phone_id, $options );
		return $options->phone_id;
	}
	
	/**
		@brief		Deletes a phone and all its settings.
		
		@param		int		$phone_id		ID of phone to delete.
	**/
	public function delete( $phone_id )
	{
		$q = $this->db->q()
        	->delete( 'phones' )
        	->where( 'phone_id = :phone_id' )
        	->setParameter( 'phone_id', $phone_id )
        	->execute();
        $this->sms_master->settings->delete_like( $this->get_settings_key( $phone_id ) );
	}
	
	/**
		@brief		Finds all available phones that are ready for use.
		
		@return		array		An array of phone database rows that are enabled and ready for use.
	**/
	public function find_all_available( $limit = 100 )
	{
		$result = $this->db->fetchAll( 'SELECT * FROM `phones`
			INNER JOIN `slaves` USING (slave_id)
			WHERE `phones`.enabled = \'1\'
			AND ( `touched` < now() OR `touched` IS NULL )
			ORDER BY `touched`
			LIMIT ' . $limit ) ;
		return $this->arrays_to_objects( $result );
	}
	
	/**
		@brief		Finds the first available phone.
		
		@return		false|array		False, if no phone is available, or a phone array.
	**/
	public function find_first_available()
	{
		$result = $this->find_all_available( 1 );
		if ( count( $result ) !== 1 )
			return false;
		else
			return (object)$result[0];
	}
	
	/**
		@brief		Retrieves a phone's data.
		
		@param		integer		$phone_id		Phone ID to retrieve.
		@return		array		A phone row from the database.
	**/
	public function get( $phone_id )
	{
		$result = $this->db->fetchAll( 'SELECT * FROM `phones`
			INNER JOIN `slaves` USING (slave_id)
			WHERE `phones`.phone_id = :phone_id', array(
			'phone_id' => $phone_id,
		) ) ;
		if ( count( $result ) !== 1 )
			throw new SMS_Master_Phone_Not_Found_Exception();
		else
			return (object)$result[0];
	}
	
	/**
		@brief		Retrieves the settings for a phone.

		@param		integer		$phone_id		Phone ID to retrieve.
		@return		array		Phone settings.
	**/
	public function get_settings( $phone_id )
	{
		return $this->sms_master->settings->find( $this->get_settings_key( $phone_id ) );
	}
	
	/**
		@brief		Returns the base settings key for this phone.
	**/
	public function get_settings_key( $phone_id )
	{
		return 'phone_' . $phone_id . '_%';
	}
	
	/**
		@brief		Identify a phone.
		@param		int		$phone_id		ID of phone to identify.
		@return		array	Array of strings, output from gnokii identify and showsmsfolderstatus.
	**/
	public function identify( $phone_id )
	{
		$phone = $this->get( $phone_id );
		$slave = $this->sms_master->slaves->get( $phone->slave_id );
		
		$rv = array();
		
		$phone_command = 'gnokii --phone ' . $phone->phone_index . ' --identify';
		$command = $this->sms_master->send_slave_command( $slave, $phone_command );
		$rv = array_merge( $rv, $command->output );
		
		$rv[] = '';
		
		$phone_command = 'gnokii --phone ' . $phone->phone_index . ' --showsmsfolderstatus';
		$command = $this->sms_master->send_slave_command( $slave, $phone_command );
		$rv = array_merge( $rv, $command->output );
		
		return $rv;
	}
	
	/**
		@brief		Increase the sent stats of a phone.
		
		Each phone has sent statistics bound to it as settings.
		
		Every time a message is correctly sent, this method should be called.
		
		@param		int		$phone_id		ID of phone stats to increase,
	**/
	public function increase_sent_stats( $phone_id )
	{
		// Total stat
		$key = 'phone_' . $phone_id . '_sent';
		$sent = $this->sms_master->settings->get( $key );
		$this->sms_master->settings->update( $key, $sent + 1 );
		
		// Year stat
		$key = 'phone_' . $phone_id . '_sent_' . date('Y');
		$sent = $this->sms_master->settings->get( $key );
		$this->sms_master->settings->update( $key, $sent + 1 );
		
		// Month stat
		$key = 'phone_' . $phone_id . '_sent_' . date('Y_m');
		$sent = $this->sms_master->settings->get( $key );
		$this->sms_master->settings->update( $key, $sent + 1 );
		
		// Increase the total for the SMS master
		$this->sms_master->increase_sent_stats();
	}

	/**
		@brief		Lists all phones.
		
		Options are:
		- @e int @b slave_id ID of slave for which to list phones.

		@param		object		$options		List options.
		@return		array		List of all phones.
	**/
	public function list_( $options = array() )
	{
		$options = SMS_Master::merge_objects( array(), $options );
		$where = array();
		
		if ( isset($options->slave_id) )
			$where[] = "slave_id = '" . $options->slave_id . "'";

		if ( count($where) > 0 )
			$where = 'WHERE ' . implode( ' AND ', $where );
		else
			$where = '';
		
		$results = $this->db->fetchAll( 'SELECT * FROM `phones`
			INNER JOIN `slaves` USING (slave_id)
			'.$where.'
			ORDER BY `touched`' ) ;
		$rv = array();
		foreach( $results as $result )
			$rv[] = (object)$result;
		return $rv;
	}
	
	/**
		@brief		List all phones connected to a specific slave.
		@param		int		$slave_id		ID of slave to query.
	**/
	public function list_from_slave_id( $slave_id )
	{
		return $this->list_( array(
			'slave_id' => $slave_id,
		) );
	}
	
	/**
		@brief		Touch a phone.
		@param		int		$phone_id		ID of phone to touch.
	**/
	public function touch( $phone_id )
	{
		$this->db->executeUpdate( "UPDATE phones SET touched = now() + INTERVAL 1 MINUTE WHERE `phone_id` = :phone_id",
			array(
				'phone_id' => $phone_id,
			)
		);
	}
	
	/**
		@brief		Mark the phone as successfully touched.
		@param		int		$phone_id		ID of phone to mark.
	**/
	public function touch_successfully( $phone_id )
	{
		$this->db->executeUpdate( "UPDATE phones SET touched = :touched, touched_successfully = :touched WHERE `phone_id` = :phone_id",
			array(
				'phone_id' => $phone_id,
				'touched' => new \DateTime('now'),
			),
			array(
				'touched' => \Doctrine\DBAL\Types\Type::getType('datetime'),
			)
		);
	}	

	/**
		@brief		Untouch a phone.
		@param		int		$phone_id		ID of phone to untouch.
	**/
	public function untouch( $phone_id )
	{
		$this->db->executeUpdate( "UPDATE phones SET touched = now() WHERE `phone_id` = :phone_id",
			array(
				'phone_id' => $phone_id,
			)
		);
	}
	
	/**
		@brief		Updates a phone's settings.
		
		@param		integer		$phone_id		Phone ID to update.
		@param		object		$data			New date to write.
		@see		$updateable_columns
	**/
	public function update( $phone_id, $data )
	{
		$q = $this->db->q()
        	->update( 'phones', 'p' )
        	->where( 'p.phone_id = :phone_id' )
        	->setParameter( 'phone_id', $phone_id );
        
        foreach( $this->updateable_columns as $column )
        	if ( isset( $data->$column ) )
        		$q->set( 'p.' . $column, $data->$column );
        	
        $q->execute();
	}
}

