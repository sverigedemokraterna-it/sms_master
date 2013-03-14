<?php
/**
	Settings handler.
**/
class class_SMS_Master_Settings extends class_SMS_Master_Generic
{
	/**
		@brief		Create a new setting pair.
		
		@param		string		$key		Key to create.
		@param		string		$value		Value for key.
	**/
	public function add( $key, $value )
	{
		$this->db->insert( 'settings', array(
			'`key`' => $key,
			'value' => $value,		
		) );
	}
	
	/**
		@brief		Deletes the key.
		
		@param		string		$key		Delete the key/value pair associated with this key.
	**/
	public function delete( $key )
	{
		$this->db->delete( 'settings', array(
			'`key`' => $key,
		) );
	}
	
	/**
		@brief		Deletes all keys that are LIKE this one.
		
		@param		string		$key		Key to LIKE. Use % for wildcards.
	**/
	public function delete_like( $key )
	{
		$this->db->executeQuery( 'DELETE FROM settings WHERE `key` LIKE ?', array(
			$key
		));
	}
	
	/**
		@brief		Return an array of keys that are SQL LIKE this one.
		
		@param		string		$search		Term to search for. Use % for wildcards.
		@return		array		An array of objects.
	**/
	public function find( $search )
	{
		$results = $this->db->fetchAll( 'SELECT `key`, `value` FROM settings WHERE `key` LIKE ?', array( $search ) );
		$rv = new stdClass();
		foreach( $results as $result )
		{
			$key = $result[ 'key' ];
			$rv->$key = $result[ 'value' ];
		}
		return $rv;
	}
	
	/**
		@brief		Returns a setting.
		
		@param		string		$key		Key for setting to find.
		@return		false|string		Exactly one string for the key. If several settings have the same key, false will be returned.
	**/
	public function get( $key )
	{
		$result = $this->db->fetchAll( 'SELECT `value` FROM settings WHERE `key` = ?', array( $key ) ) ;
		if ( count( $result ) !== 1 )
			return false;
		else
			return $result[0]['value'];
	}
	
	/**
		@brief		Updates a setting.
		
		If the setting does not exist, it will be created.
		
		@param		string		$key		Key for setting to update.
		@param		string		$value		New value for key.
	**/
	public function update( $key, $value )
	{
		if ( $this->get( $key ) === false )
			$this->add( $key, $value );
		else
			$this->db->update( 'settings', array( '`value`' => $value ), array( '`key`' => $key ) );
	}
}

