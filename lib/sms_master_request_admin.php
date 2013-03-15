<?php
/**
	@brief		A request that needs administrator access.
	
	Does admin user checking before handling.
**/
class SMS_Master_Admin_Request extends SMS_Master_Request
{
	public function handle_1()
	{
		parent::handle();
		if ( ! $this->loaded_user->administrator )
			throw new SMS_Master_Exception( 'No admin access.' );
	}
}

/**
	@brief		Request the status of the master. Contains relevant information about slaves, phones and orders.
**/
class SMS_Master_Status_Request extends SMS_Master_Admin_Request
{
	/**
		@brief		A count of completed orders.
		@var		$completed_orders
		@out
	**/
	public $completed_orders;

	/**
		@brief		One variable summary of the master's status.
		@var		$ok
		@out
	**/
	public $ok = false;

	/**
		@brief		An array of slave objects.
		
		Each slave has its own ->phones.
		
		@var		$slaves
		@out
	**/
	public $slaves = array();

	/**
		@brief		A count of uncompleted orders.
		@var		$uncompleted_orders
		@out
	**/
	public $uncompleted_orders;

	/**
		@brief		Count of users.
		@var		$user_count
		@out
	**/
	public $user_count;
	
	public function handle()
	{
		$this->config = $this->sms_master->config;
		$this->enabled = $this->sms_master->settings->get( 'enabled' );
		$this->user_count = $this->sms_master->users->count();
		$this->stats = new stdClass();
		foreach( $this->sms_master->settings->find( 'stat_sent_sms%' ) as $key=>$value )
			$this->stats->$key = $value;
		$this->completed_orders = $this->sms_master->orders->count_completed();
		$this->uncompleted_orders = $this->sms_master->orders->count_uncompleted();
		$this->slaves = $this->sms_master->slaves->list_();
		
		$this->phones = 0;
		foreach( $this->slaves as $index => $slave )
		{
			$phones = $this->sms_master->phones->list_from_slave_id( $slave->slave_id );
			$this->slaves[$index]->phones = array();
			foreach( $phones as $phone )
			{
				$this->phones++;
				$phone_id = $phone->phone_id;
				$this->slaves[$index]->phones[ $phone_id ] = $phone;
				$this->slaves[$index]->phones[ $phone_id ]->settings = $this->sms_master->phones->get_settings( $phone_id );
			}
		}
		
		$this->ok = true;
		if ( $this->phones < 1 )
			$this->ok = T_( 'No phones are configured' );
		if ( count( $this->slaves ) < 1 )
			$this->ok = T_( 'No slaves are configured' );
	}
}

/**
	@brief		Switch the master on / off.
**/
class SMS_Master_Switch_Request extends SMS_Master_Admin_Request
{
	/**
		@brief		Enable or disable the master?
		
		Disabling it will prevent it from cronning (send messages and cleaning).
		
		Is returned with the status after switching.
		
		@var		$enabled
		@in
		@out
	**/
	public $enabled = true;
	
	public function handle()
	{
		$this->sms_master->settings->update( 'enabled', $this->enabled );
		$this->enabled = $this->sms_master->settings->get( 'enabled' );
	}
}

