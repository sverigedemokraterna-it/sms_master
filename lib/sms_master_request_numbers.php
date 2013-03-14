<?php

/**
	@brief		Superclass for all number related requests.
	
	The subclasses all require admin access.
**/
class SMS_Master_Number_Request
	extends SMS_Master_Admin_Request
{
}

/**
	@brief		Deletes a number from the database.
**/
class SMS_Master_Delete_Number_Request
	extends SMS_Master_Number_Request
{
	/**
		@brief		Number ID to delete.
		@var		$number_id
		@in
	**/
	public $number_id;
	
	public function handle()
	{
		$this->sms_master->numbers->delete( $this->number_id );
	}
}

/**
	@brief		Gets a number from the database.
**/
class SMS_Master_Get_Number_Request
	extends SMS_Master_Number_Request
{
	/**
		@brief		Number ID to mark as sent.
		@var		int		$number_id
		@in
	**/
	public $number_id;
	
	/**
		@brief		The number object from the database.
		@var		$number
		@out
	**/
	public $number;
	
	public function handle()
	{
		$this->number = $this->sms_master->numbers->get( $this->number_id );
	}
}

/**
	@brief		Mark a number as sent.
**/
class SMS_Master_Mark_Number_Sent_Request
	extends SMS_Master_Number_Request
{
	/**
		@brief		Number ID to mark as sent.
		@var		$number_id
		@in
	**/
	public $number_id;
	
	public function handle()
	{
		$this->sms_master->numbers->mark_sent( $this->number_id );
	}
}

/**
	@brief		Mark a number as unsent.
**/
class SMS_Master_Mark_Number_Unsent_Request
	extends SMS_Master_Number_Request
{
	/**
		@brief		Number ID to mark as unsent.
		@var		$number_id
		@in
	**/
	public $number_id;
	
	public function handle()
	{
		$this->sms_master->numbers->mark_unsent( $this->number_id );
	}
}

/**
	@brief		Sets the number of sending failures to zero.
**/
class SMS_Master_Reset_Number_Failures_Request
	extends SMS_Master_Number_Request
{
	/**
		@brief		Number ID for which to reset failure count.
		@var		$number_id
		@in
	**/
	public $number_id;
	
	public function handle()
	{
		$this->sms_master->numbers->reset_failures( $this->number_id );
	}
}

/**
	@brief		Touch a number.
	
	This will prevent the number from being accessed (sent) for a length of time.
	
	Usually 15 minutes.
**/
class SMS_Master_Touch_Number_Request
	extends SMS_Master_Number_Request
{
	/**
		@brief		Number ID to touch.
		@var		$number_id
		@in
	**/
	public $number_id;
	
	public function handle()
	{
		$this->sms_master->numbers->touch( $this->number_id );
	}
}

/**
	@brief		Untouch a number.
	
	This will make the number ready to be sent by the master.
**/
class SMS_Master_Untouch_Number_Request
	extends SMS_Master_Number_Request
{
	/**
		@brief		Number ID to untouch.
		@var		$number_id
		@in
	**/
	public $number_id;
	
	public function handle()
	{
		$this->sms_master->numbers->untouch( $this->number_id );
	}
}

