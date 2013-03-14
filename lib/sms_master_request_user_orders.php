<?php

/**
	@brief		Superclass for all order requests from normal users.
**/
class SMS_Master_Order_User_Request
	extends SMS_Master_User_Request
{
}

/**
	Marks an order as complete.
**/
class SMS_Master_Complete_Order_Request
	extends SMS_Master_Order_User_Request
{
	/**
		@brief		UUID of order.
		@var		$order_uuid
		@in
	**/
	public $order_uuid;
	
	public function handle()
	{
		$order = $this->sms_master->orders->get_using_uuid( $this->order_uuid );
		$this->sms_master->orders->complete( $order->order_id );
	}
}

/**
	@brief		Creates an order.
**/
class SMS_Master_Create_Order_Request
	extends SMS_Master_Order_User_Request
{
	/**
		@brief		Order options.
		@see		class_SMS_Master_Orders::create_order
		@var		$options
		@in
	**/
	public $options;
	
	/**
		@brief		Created order ID.
		@var		$order_id
		@out
	**/
	public $order_id;
	
	/**
		@brief		Created order database row.
		@var		$order
		@out
	**/
	public $order;
	
	public function __construct()
	{
		$this->options = new stdClass();
	}
	
	public function handle()
	{
		// Assume that our values are sane. The front-end should have taken care of weird input data.
		$this->options->user = $this->loaded_user;
		$this->order_id = $this->sms_master->orders->create( $this->options );
		$this->order = $this->sms_master->orders->get( $this->order_id ); 
	}
}

/**
	@brief		Get an order.
**/
class SMS_Master_Get_UUID_Order_Request
	extends SMS_Master_Order_User_Request
{
	/**
		@brief		UUID of order
		@var		$order_uuid
		@in
	**/
	public $order_uuid = null;
	
	/**
		@brief		Order database row.
		@var		$order
		@out
	**/
	public $order;
	
	public function handle()
	{
		$this->order = $this->sms_master->orders->get_using_uuid( $this->order_uuid );
		if ( $this->order->completed == 0 )
			$this->order_numbers = $this->sms_master->numbers->list_from_order( $this->order->order_id );
	}
}

