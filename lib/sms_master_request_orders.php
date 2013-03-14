<?php

/**
	@brief		Superclass for all order requests from admins.
**/
class SMS_Master_Order_Admin_Request
	extends SMS_Master_Admin_Request
{
}

/**
	@brief		Delete an order.
	
	Either $order_id or $order_uuid can be specified, with uuid taking preference.
**/
class SMS_Master_Delete_Order_Request
	extends SMS_Master_Order_Admin_Request
{
	/**
		@brief		ID of order
		@var		$order_id
		@in
	**/
	public $order_id = null;
	
	/**
		@brief		UUID of order
		@var		$order_uuid
		@in
	**/
	public $order_uuid = null;
	
	public function handle()
	{
		if ( $this->order_uuid !== null )
			$this->sms_master->orders->delete_using_uuid( $this->order_uuid );
		else
			$this->sms_master->orders->delete( $this->order_id );
	}
}

/**
	@brief		Get an order.
	
	Either the order_id or the order_uuid can be specified to return the order.
	
	uuid takes preference over id.
**/
class SMS_Master_Get_Order_Request
	extends SMS_Master_Order_Admin_Request
{
	/**
		@brief		ID of order
		@var		$order_id
		@in
	**/
	public $order_id = null;
	
	/**
		@brief		Order database row.
		@var		$order
		@out
	**/
	public $order;
	
	public function handle()
	{
		$this->order = $this->sms_master->orders->get( $this->order_id );
		if ( $this->order->completed == 0 )
			$this->order_numbers = $this->sms_master->numbers->list_from_order( $this->order->order_id );
	}
}

class SMS_Master_List_Completed_Orders_Request
	extends SMS_Master_Order_Admin_Request
{
	/**
		@brief		Object of list options.
		@var		$options
		@in
	**/
	public $options;
	
	/**
		@brief		Count of completed orders.
		@var		$count
		@out
	**/
	public $count;
	
	/**
		@brief		Array of completed order database objects.
		@var		$orders
		@out
	**/
	public $orders;
	
	public function handle()
	{
		$this->count = $this->sms_master->orders->count_completed();
		$this->orders = $this->sms_master->orders->list_completed( $this->options );
	}
}

/**
	@brief		Send unsent messages.
**/
class SMS_Master_Send_Unsent_Request
	extends SMS_Master_Order_Admin_Request
{
	/**
		@brief		How many unsent messages there were before sending.
		@var		$count_before
		@out
	**/
	public $count_before;
	
	/**
		@brief		How many unsent messages there were after sending.
		@var		$count_after
		@out
	**/
	public $count_after;
	
	public function handle()
	{
		$this->count_before = count( $this->sms_master->numbers->list_ready_to_send() );
		$this->sms_master->send_unsent();
		$this->count_after = count( $this->sms_master->numbers->list_ready_to_send() );
	}
}

class SMS_Master_List_Uncompleted_Orders_Request
	extends SMS_Master_Order_Admin_Request
{
	/**
		@brief		Object of list options.
		@var		$options
		@in
	**/
	public $options;
	
	/**
		@brief		Count of uncompleted orders.
		@var		$count
		@out
	**/
	public $count;
	
	/**
		@brief		Array of uncompleted order database objects.
		@var		$orders
		@out
	**/
	public $orders;
	

	public function handle()
	{
		$this->count = $this->sms_master->orders->count_uncompleted();
		$this->orders = $this->sms_master->orders->list_uncompleted( $this->options );
	}
}

