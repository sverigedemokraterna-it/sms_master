<?php

/**
	@brief		Superclass for all phone requests.
	
	All phone requests require admin access.
**/
class SMS_Master_Phone_Request
	extends SMS_Master_Admin_Request
{
}

/**
	@brief		Clean a phone.
	@see		class_SMS_Master_Phones::clean
**/
class SMS_Master_Clean_Phone_Request
	extends SMS_Master_Phone_Request
{
	/**
		@brief		ID of phone to clean.
		@in
		@var		$phone_id
	**/
	public $phone_id;
	
	/**
		@brief		Empty the phone of messages.
		@in
		@var		$empty
	**/
	public $empty = true;
	
	/**
		@brief		Output of cleaning commands.
		@out
		@var		$output
	**/
	public $output;
	
	public function handle()
	{
		$o = new stdClass();
		$o->phone_id = $this->phone_id;
		$o->empty = true;
		$this->output = $this->sms_master->phones->clean( $o );
		
		// Send cleaning report
		$clean_data = array( $this->phone_id => $this->output );
		$this->sms_master->send_cleaning_report( $clean_data );
	}
	
	public function command()
	{
		$this->output = $this->sms_master->phones->clean( $this );
		$this->sms_master->phones->untouch( $this->phone_id );
	}
}

/**
	@brief		Cleans all the phones by calling cron_clean()
*/
class SMS_Master_Clean_Phones_Request
	extends SMS_Master_Phone_Request
{
	/**
		@brief		The output from the clean commands. An array of phone_id -> text.
		@var		$output
		@see		SMS_Master::cron_clean
		@see		class_SMS_Master_Phones::clean
		@out
	**/
	public $output;
	
	public function handle()
	{
		$this->sms_master->cron_clean();
	}
}

/**
	@brief		Create a new phone.
	@see		class_SMS_Master_Phones::create
**/
class SMS_Master_Create_Phone_Request
	extends SMS_Master_Phone_Request
{
	/**
		@brief		The options object with which to create the slave.
		
		Uses the same variables as ->update(), but leaves the phone_id unset.
		
		@in
		@see		SMS_Master_Update_Phone_Request
		@var		$options
	**/
	public $options;
	
	/**
		@brief		ID of newly created phone.
		@out
		@var		$phone_id
	**/
	public $phone_id;
	
	public function handle()
	{
		$this->phone_id = $this->sms_master->phones->create( $this->options );
	}
}

/**
	@brief		Delete a phone.
	@see		class_SMS_Master_Phones::delete
**/
class SMS_Master_Delete_Phone_Request
	extends SMS_Master_Phone_Request
{
	/**
		@brief		ID of phone to delete.
		@in
		@var		$phone_id
	**/
	public $phone_id;
	
	public function handle()
	{
		$this->phone = $this->sms_master->phones->get( $this->phone_id );	// To see that it exists first.
		$this->sms_master->phones->delete( $this->phone_id );
	}
}

/**
	@brief		Get a phone.
	@see		class_SMS_Master_Phones::get
**/
class SMS_Master_Get_Phone_Request
	extends SMS_Master_Phone_Request
{
	/**
		@brief		ID of phone to get.
		@in
		@var		$phone_id
	**/
	public $phone_id;
	
	/**
		@brief		Phone row (+ slave row) from table.
		
		Also returns the phone settings in $this->phone->settings.
		@out
		@var		$phone
	**/
	public $phone;
	
	public function handle()
	{
		$this->phone = $this->sms_master->phones->get( $this->phone_id );
		$this->phone->settings = $this->sms_master->phones->get_settings( $this->phone_id );
	}
}

/**
	@brief		Identify a phone.
	@see		class_SMS_Master_Phones::identify
**/
class SMS_Master_Identify_Phone_Request
	extends SMS_Master_Phone_Request
{
	/**
		@brief		ID of phone to identify.
		@in
		@var		$phone_id
	**/
	public $phone_id;
	
	/**
		@brief		Output text.
		@out
		@var		$output
	**/
	public $output;
	
	public function handle()
	{
		$this->phone = $this->sms_master->phones->get($this->phone_id);					// To see that it exists first.
		$this->output = $this->sms_master->phones->identify( $this->phone_id );
	}
}

/**
	@brief		Sends a command to a phone.
**/
class SMS_Master_Send_Number_Request
	extends SMS_Master_Phone_Request
{
	/**
		@brief		ID of number to send.
		@in
		@var		$number_id
	**/
	public $number_id;

	/**
		@brief		ID of phone to send to.
		@in
		@var		$phone_id
	**/
	public $phone_id;

	/**
		@brief		Return code from gnokii.
		@out
		@var		$code
	**/
	public $code;

	/**
		@brief		Output from exec command.
		@out
		@var		$output
	**/
	public $output;
	
	public function command()
	{
		$number = $this->sms_master->numbers->get( $this->number_id );
		$phone = $this->sms_master->phones->get( $this->phone_id );
		$slave = $this->sms_master->slaves->get( $phone->slave_id );
		
		$text = trim( $number->text );
		$text_encoding = mb_detect_encoding($text, "ASCII, UTF-8", true);
		$replacements = array(
			'"' => "'",
		);
		foreach( $replacements as $search => $replace )
			$text = str_replace( $search, $replace, $text );
	
		// Assemble the gammu command line.
		$phone_command = 'echo \"'.$text.'\" | gnokii --phone ' . $phone->phone_index . ' --sendsms '.trim( $number->number );
		
		$command = $this->sms_master->send_slave_command( $slave, $phone_command );
		
		$this->code = $command->code;
		$this->output = $command->output;
	}
}


/**
	@brief		Update a phone.
	@see		class_SMS_Master_Phones::update
**/
class SMS_Master_Update_Phone_Request
	extends SMS_Master_Phone_Request
{
	/**
		@brief		New phone data.
		@in
		@out
		@var		$phone
	**/
	public $phone;
	
	public function handle()
	{
		// Make sure the phone exists first.
		$this->sms_master->phones->get( $this->phone->phone_id );
		$this->sms_master->phones->update( $this->phone->phone_id, $this->phone );
		// Return the phone as a convenience.
		$this->phone = $this->sms_master->phones->get( $this->phone->phone_id );
	}
}

