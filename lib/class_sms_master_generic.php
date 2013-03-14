<?php
/**
	@brief		Generic superclass.
	
	Used by users, orders, et al.
**/
class class_sms_master_generic
{
	protected $sms_master;	// Master class
	protected $db;			// Database class
	protected $log;			// Log class.
	
	/**
		@brief		Constructor.
		
		@param	SMS_Master		$sms_master		The main SMS Master class.
	**/
	public function __construct( $sms_master )
	{
		$this->sms_master = $sms_master;
		$this->db = $this->sms_master->db;
		$this->log = $this->sms_master->log;
	}
	
	/**
		@brief		Converts an array of arrays to an array of objects.
		
		@param		array		$array		Array of arrays.
		@return		array		An array of objects.
	**/
	public function arrays_to_objects( $array )
	{
		$rv = array();
		foreach( $array as $item )
			$rv[] = (object) $item;
		return $rv;
	}
}

