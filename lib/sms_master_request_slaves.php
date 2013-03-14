<?php

/**
	@brief		Superclass for slave requests.
	
	All slave requests require admin access.
**/
class SMS_Master_Slave_Request
	extends SMS_Master_Admin_Request
{
}

/**
	@brief		Creates a slave.
	@see		class_SMS_Master_Slave::create
**/
class SMS_Master_Create_Slave_Request
	extends SMS_Master_Slave_Request
{
	/**
		@brief		The options object with which to create the slave.
		
		Uses the same variables as ->update(), but leaves the slave_id unset.
		
		@in
		@var	$options
	**/
	public $options;
	
	/**
		@brief		ID of newly created slave.
		@out
		@var	$slave_id
	**/
	public $slave_id;
	
	public function handle()
	{
		$this->slave_id = $this->sms_master->slaves->create( $this->options );
	}
}

/**
	@brief		Deletes a slave from the database.
	@see		class_SMS_Master_Slave::delete
**/
class SMS_Master_Delete_Slave_Request
	extends SMS_Master_Slave_Request
{
	/**
		@brief		ID of slave to delete.
		@in
		@var		$slave_id
	**/
	public $slave_id;
	
	public function handle()
	{
		$this->sms_master->slaves->delete( $this->slave_id );
	}
}

/**
	@brief		Returns a slave database row object.
	@see		class_SMS_Master_Slave::get
**/
class SMS_Master_Get_Slave_Request
	extends SMS_Master_Slave_Request
{
	/**
		@brief		ID of slave to update.
		@in
		@var		$slave_id
	**/
	public $slave_id;

	/**
		@brief		Database row object of slave.
		@out
		@var		$slave
	**/
	public $slave;
	
	public function handle()
	{
		$this->slave = $this->sms_master->slaves->get( $this->slave_id );
	}
}

/**
	@brief		Identify a slave.	
	@see		class_SMS_Master_Slaves::identify
**/
class SMS_Master_Identify_Slave_Request
	extends SMS_Master_Slave_Request
{
	/**
		@brief		ID of slave to identify.
		@in
		@var		$slave_id
	**/
	public $slave_id;
	
	/**
		@brief		Text output array.
		@out
		@var		$output
	**/
	public $output;
	
	public function handle()
	{
		$this->output = $this->sms_master->slaves->identify( $this->slave_id );
	}
}

/**
	@brief		Updates the data of a slave.
	@see		class_SMS_Master_Slave::update
**/
class SMS_Master_Update_Slave_Request
	extends SMS_Master_Slave_Request
{
	/**
		@brief		Slave to update.
		The whole database row should be in here, including the ->slave_id property.
		@in
		@var		$slave
	**/
	public $slave;
	
	public function handle()
	{
		$this->sms_master->slaves->update( $this->slave->slave_id, $this->slave );
	}
}

