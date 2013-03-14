<?php
/**
	@brief		Order handling part of SMS master.
**/
class class_SMS_Master_Orders
	extends class_SMS_Master_Generic
{
	/**
		@brief		Which table columns are updateable using update().
		@var		$updateable_columns
		@see		update
	**/
	public $updateable_columns = array(
		'completed',
		'datetime_completed',
		'datetime_created',
		'email_addresses',
		'email_report_sent',
		'email_report_text',
		'error_log',
		'numbers',
		'number_count',
		'order_uuid',
		'priority',
		'text',
		'user_id',
	);
	
	/**
		@brief		Completes an order.
		
		Deletes numbers, creates a send report, etc.

		@param		int		$order_id		Order ID to complete.
		@throws		SMS_Master_Order_Not_Found_Exception
	**/
	public function complete( $order_id )
	{
		$order = $this->get( $order_id );
	
		$this->mark_as_completed( $order_id );
		
		// New data for updating the order.
		$new_data = new stdClass();
		
		// Do we send a report?
		if ( $order->email_addresses != '' )
			$new_data->email_report_text = $this->generate_report( $order_id );
		
		// Time to censor!
		$setting = $this->order_setting( $order_id, 'censor_text' );
		if ( $this->sms_master->settings->get( $setting ) !== false )
			$new_data->text = T_( '**redacted**' );
		$this->sms_master->settings->delete( $setting );

		$setting = $this->order_setting( $order_id, 'censor_numbers' );
		if ( $this->sms_master->settings->get( $setting ) !== false )
		{
			$new_data->numbers = T_( '**redacted**' );
			$new_data->number_count = 1;
			$this->sms_master->numbers->delete( $order_id );
		}
		$this->sms_master->settings->delete( $setting );
		
		$this->update( $order_id, $new_data );
		
		$this->sms_master->numbers->delete( $order_id );
	}
	
	/**
		@brief		Counts the completed orders.
		@return		integer		Number of completed orders.
	**/
	public function count_completed()
	{
		$result = $this->db->fetchAll( "SELECT COUNT(*) as count FROM orders WHERE completed = '1'" ) ;
		return $result[0]['count'];
	}

	/**
		@brief		Counts the uncompleted orders.
		@return		integer		Number of uncompleted orders.
	**/
	public function count_uncompleted()
	{
		$result = $this->db->fetchAll( "SELECT COUNT(*) as count FROM orders WHERE completed = '0'" ) ;
		return $result[0]['count'];
	}
	
	/**
		@brief		Create a new order.
		
		Options:
		- @e bool @b censor_numbers		Censor the numbers after completion.
		- @e bool @b censor_report		Censor the report after completion.
		- @e bool @b censor_text		Censor the order text after completion.
		- @e string @b email_addresses	Comma separated list of email addresses to send report to.
		- @e array @b numbers			Array of numbers to send to.
		- @e int @b priority			0 is standard. Bigger is higher / more important. Use less than 0 for bulk.
		- @e string @b text				Text to send. Max 160 characters.
		- @e object @b user				User from database which has created this order.
		
		@param		object		$options		Order creation options.
		@return		integer		The newly-created order's ID.
		@see		$updateable_columns
	**/
	public function create( $options )
	{
		$options = SMS_Master::merge_objects( array(
			'censor_numbers' => false,
			'censor_report' => false,
			'censor_text' => false,
			'email_addresses' => null,
			'numbers' => array(),
			'priority' => 0,
			'user' => null,
		), $options );
		
		$order_uuid = SMS_Master::hash( rand( 0, microtime(true) ) . microtime(true) );
		
		$this->db->executeUpdate( "INSERT INTO orders (
			`datetime_created`,
			`email_addresses`,
			`number_count`,
			`numbers`,
			`order_uuid`,
			`priority`,
			`text`,
			`user_id`
		) VALUES (
			:datetime_created,
			:email_addresses,
			:number_count,
			:numbers,
			:order_uuid,
			:priority,
			:text,
			:user_id
		)",
			array(
				'datetime_created' => new \DateTime('now'),
				'email_addresses' => $options->email_addresses,
				'number_count' => count( $options->numbers ), 
				'numbers' => implode( ",", $options->numbers ),
				'order_uuid' => $order_uuid,
				'priority' => $options->priority,
				'text' => $options->text,
				'user_id' => $options->user->user_id,
			),
			array(
				'datetime_created' => \Doctrine\DBAL\Types\Type::getType('datetime'),
			)
		);
		
		$order_id = $this->db->lastInsertID();
	
		// Settings that are true or false. For convenience.
		$true_false_settings = array( 'censor_numbers', 'censor_report', 'censor_text' );
		
		foreach( $true_false_settings as $setting )
		if ( $options->$setting === true )
		{
			$order_setting = $this->order_setting( $order_id, $setting );
			$this->sms_master->settings->update( $order_setting, true );
		}
	
		// And now put the numbers into the order.
		foreach( $options->numbers as $number )
			$this->sms_master->numbers->create( array( 'order_id' => $order_id, 'number' => $number ) );
		
		return $order_id;
	}
	
	/**
		@brief		Deletes an order using the id.

		@param		string		$order_id		Order ID to delete.
		@throws		SMS_Master_Order_Not_Found_Exception
	**/
	public function delete( $order_id )
	{
		$order = $this->get( $order_id );
		
		$order_id = $order->order_id;	
		$this->db->delete( 'orders',
			array('order_id' => $order_id)
		);
		$this->db->delete( 'order_numbers',
			array('order_id' => $order_id)
		);
		
		// Delete all the settings associated with this order.
		$this->sms_master->settings->delete_like( $this->order_setting( $order_id, '%' ) );
	}
	
	/**
		@brief		Deletes an order using the uuid.

		@param		string		$order_uuid		Order UUID to delete.
		@throws		SMS_Master_Order_Not_Found_Exception
	**/
	public function delete_using_uuid( $order_uuid )
	{
		$order = $this->get_using_uuid( $order_uuid );
		$this->delete( $order->order_id );
	}
	
	/**
		@brief		Generates an array of mail data containing the report about this order.
		@param		int		$order_id		ID of order to create
		@return		string					Base64-encoded mail data array for send_mail().
	**/
	public function generate_report( $order_id )
	{
		$order = $this->get( $order_id );
		
		$body_text = '';
		$email_addresses = $order->email_addresses;
		$email_addresses = explode( ",", $email_addresses );
		
		// Collect a list of all numbers and report their status.
		$numbers = $this->sms_master->numbers->list_from_order( $order_id );
		
		$numbers_text = array(
			'failures' => array(),
			'sent' => array(),
		);
		
		foreach( $numbers as $number )
		{
			if ( $number->sent != '' )
				$numbers_text['sent'][] = $number->number . ", " . $number->sent;
			else
				$numbers_text['failures'][] = $number->number . ' ' . T_('could not be sent');
		}
		
		$body_text .= sprintf( T_( "This is an automatic delivery report for the SMS order placed %s. The text sent was:%s%s" ),
			$order->datetime_created,
			"\n\t",
			$order->text
		);

		if ( count( $numbers_text['failures'] ) > 0 )
			$body_text .= "\n\n" . sprintf(
				T_('The text could not be sent to the following %s numbers:%s%s'),
				count( $numbers_text['failures'] ) ,
				"\n",
				implode( "\n", $numbers_text['failures'] )
			);

		if ( count( $numbers_text['sent'] ) > 0 )
			$body_text .= "\n\n" . sprintf(
				T_('The text was successfully sent to the following %s numbers:%s%s'),
				count( $numbers_text['sent'] ) ,
				"\n",
				implode( "\n", $numbers_text['sent'] )
			);
			
		if ( $order->error_log != '' )
			$body_text .= "\n\n" . T_( 'Error messages from the phones:' ) . "\n" . $order->error_log;
		
		$body_text .= $this->sms_master->config['email']['signature'];
		
		$mail_data = array(
			'from' => array( $this->sms_master->config['email']['from_address'] => $this->sms_master->config['email']['from_name'] ),
			'to' => $email_addresses,
			'subject' => sprintf(
				T_( 'Delivery report for the SMS order placed %s' ),
				$order->datetime_created
			),
			'body' => $body_text,
		);
		
		$mail_data = serialize( $mail_data );
		$mail_data = base64_encode( $mail_data );
		
		return $mail_data;
	}
	
	/**
		@brief		Returns the database object of an order.

		@param		integer		$order_id		Order ID to retrieve.
		@return		array		Database row.
		@throws		SMS_Master_Order_Not_Found_Exception
	**/
	public function get( $order_id )
	{
		$result = $this->db->fetchAll( 'SELECT * FROM orders WHERE order_id = ?', array( $order_id ) ) ;
		if ( count( $result ) !== 1 )
			throw new SMS_Master_Order_Not_Found_Exception();
		else
			return (object)$result[0];
	}
	
	/**
		@brief		Returns the database object of an order, using the uuid as the key.

		@param		string		$order_uuid		Order UUID to retrieve.
		@return		object		Database row.
		@throws		SMS_Master_Order_Not_Found_Exception
	**/
	public function get_using_uuid( $order_uuid )
	{
		$result = $this->db->fetchAll( 'SELECT * FROM orders WHERE order_uuid = ?', array( $order_uuid ) ) ;
		if ( count( $result ) !== 1 )
			throw new SMS_Master_Order_Not_Found_Exception();
		else
			return (object)$result[0];
	}
	
	/**
		@brief		Returns a list of specific order objects.
		
		Options are:
		- @e int @b page Which page to return? The first page is page 1.
		- @e int @b limit How many orders to return per page.
		- @e int @b completed Return completed orders?
		
		@param		object		$options		Options object.
		@return		array		An array of order objects, from the database.
	**/
	public function list_( $options )
	{
		$options = SMS_Master::merge_objects( array(
			'page' => 1,
			'limit' => 50,
			'completed' => 1,
		), $options );

		$page = $options->page - 1;
		$start = $page * $options->limit;
		$limit = $options->limit;
		
		$results = $this->db->fetchAll( "SELECT * FROM orders WHERE completed = '". $options->completed ."' ORDER BY datetime_created DESC LIMIT $start, $limit" ) ;
		return $this->arrays_to_objects( $results );
	}
	
	/**
		@brief		Lists completed orders.
		
		@param		object		$options		Optional options. See list_ for a complete list.
		@return		array		Array of object orders from the database.
		@see		list_
	**/
	public function list_completed( $options = array() )
	{
		$options = SMS_Master::merge_objects( array(), $options );
		$options->completed = 1;
		return $this->list_( $options );
	}
	
	/**
		@brief		Lists uncompleted orders.
		
		@param		object		$options		Optional options. See list_ for a complete list.
		@return		array		Array of object orders from the database.
		@see		list_
	**/
	public function list_uncompleted( $options = array() )
	{
		$options = SMS_Master::merge_objects( array(), $options );
		$options->completed = 0;
		return $this->list_( $options );
	}
	
	/**
		@brief		List all orders with unsent e-mail reports.
		
		@return		array		An array of order objects.
	**/
	public function list_unsent_email_reports()
	{
		$results = $this->db->fetchAll( 'SELECT * FROM orders WHERE email_report_sent IS NULL AND email_report_text IS NOT NULL' ) ;
		return $this->arrays_to_objects( $results );
	}
	
	/**
		@brief		Appends a log message to this order.
		
		@param		integer		$order_id		Order ID to append to.
		@param		string		$text			Error message.
	**/	
	public function log_error( $order_id, $text )
	{
		$this->db->executeUpdate( "UPDATE orders SET error_log = CONCAT( error_log, :text ) WHERE order_id = :order_id",
			array(
				'order_id' => $order_id,
				'text' => $text,
			)
		);
	}

	/**
		@brief		Marks an order as complete.
		
		@param		integer		$order_id		Order ID to complete.
	**/
	public function mark_as_completed( $order_id )
	{
		$this->db->executeUpdate( "UPDATE orders SET completed = :completed, datetime_completed = :datetime_completed WHERE `order_id` = :order_id",
			array(
				'order_id' => $order_id,
				'completed' => '1',
				'datetime_completed' => new \DateTime('now'),
			),
			array(
				'datetime_completed' => \Doctrine\DBAL\Types\Type::getType('datetime'),
			)
		);
	}
	
	/**
		@brief		Marks an e-mail report as sent.
		
		@param		integer		$order_id		Order ID for e-mail report to be marked as sent.
	**/
	public function mark_email_report_sent( $order_id )
	{
		// Censor the report?
		$setting = $this->order_setting( $order_id, 'censor_report' );
		error_log( $this->sms_master->settings->get( $setting ) );
		if ( $this->sms_master->settings->get( $setting ) !== false )
		{
			$data = new stdClass();
			$data->email_report_text = T_( '**redacted**' );
			$this->update( $order_id, $data );
		}
		// Delete the setting since it's no longer needed.
		$this->sms_master->settings->delete( $setting );
		
		$this->db->executeUpdate( "UPDATE orders SET email_report_sent = now() WHERE `order_id` = :order_id",
			array(
				'order_id' => $order_id,
			)
		);
	}
	
	/**
		@brief		Decides whether to complete the order.
		
		Looks for unsent numbers for the order. If none are found = mark the order as complete.

		@param		int		$order_id		Order ID to check.
	**/
	public function maybe_complete( $order_id )
	{
		// Return a list of unsent numbers for this order;
		$unsent_numbers_for_order = $this->sms_master->numbers->list_ready_to_send_from_order( $order_id );
		
		if ( count( $unsent_numbers_for_order ) > 0 )
			return;
		
		$this->complete( $order_id );	
	}
	
	/**
		Returns the name of the setting key for this order_id + key.
		
		@param		int			$order_id		Order ID
		@param		string		$key			Key
		@return		string		Returns something like 'order_534_send_later'
	**/
	public function order_setting( $order_id, $key )
	{
		return 'order_' . $order_id . '_' . $key;
	}
				
	/**
		@brief		Updates an order.
		
		@param		integer		$order_id		ID of order to update.
		@param		object		$data			New date to write.
		@see		$updateable_columns
	**/
	public function update( $order_id, $data )
	{
		$q = $this->db->q()
        	->update( 'orders', 'o' )
        	->where( 'o.order_id = :order_id' )
        	->setParameter( 'order_id', $order_id );
        
        foreach( $this->updateable_columns as $column )
        	if ( property_exists( $data, $column ) )
        		$q->set( 'o.' . $column, $data->$column );
        	
        $q->execute();
	}
}
