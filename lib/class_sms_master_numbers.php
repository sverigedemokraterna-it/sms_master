<?php
/**
	@brief		Number handler.
	
	A number is a part of an order.
**/
class class_SMS_Master_Numbers
	extends class_SMS_Master_Generic
{
	/**
		@brief		Adds a number to an order.
		
		Options:
		- @e int @b number		The telephone number itself.
		- @e int @b order_id	ID of order to append number to.
		
		@param		object		$o		Options
	**/
	public function create( $o )
	{
		$o = (object) $o;
		$this->db->insert( 'order_numbers', array(
			'number' => $o->number,
			'order_id' => $o->order_id,
		) );
	}

	/**
		@brief		Delete this number.
		@param		int		$number_id		ID of number to delete.
	**/
	public function delete( $number_id )
	{
		$this->db->delete( 'order_numbers', array(
			'number_id' => $number_id,
		) );
	}
	
	/**
		@brief		Retrieves a number.
		
		Joins the number together to its order.
		
		@param		int		$number_id		ID of number to retrieve.
		@return		false|object			The number database row, or false if the number does not exist.
	**/
	public function get( $number_id )
	{
		$result = $this->db->fetchAll( 'SELECT * FROM `order_numbers`
			INNER JOIN `orders` USING (order_id)
			WHERE `order_numbers`.number_id = :number_id', array(
			'number_id' => $number_id,
		) ) ;
		if ( count( $result ) !== 1 )
			return false;
		else
			return (object)$result[0];
	}

	/**
		@brief		Increases the failure count of a number.
		
		@param		int		$number_id		ID of number.
	**/
	public function increase_fails( $number_id )
	{
		$this->db->executeUpdate( "UPDATE order_numbers SET failures = failures + 1 WHERE `number_id` = :number_id",
			array(
				'number_id' => $number_id,
			)
		);
	}
	
	/**
		@brief		List all the numbers from an order.
		@param		int		$order_id		Order ID for which to find numbers.
		@return		array					An array of number objects (database row).
	**/
	public function list_from_order( $order_id )
	{
		$results = $this->db->fetchAll( 'SELECT * FROM order_numbers WHERE `order_id` = :order_id ORDER BY `failures`, `number`', array(
			'order_id' => $order_id,
		) ) ;
		return $this->arrays_to_objects( $results );
	}
	
	/**
		@brief		Returns a list of all numbers thare are ready ot be sent.		
		@return		array					An array of number objects (database row).
	**/
	public function list_ready_to_send()
	{
		$results = $this->db->fetchAll( 'SELECT * FROM order_numbers INNER JOIN orders USING (order_id)
			WHERE `sent` IS NULL
			AND ( `touched` < now() OR `touched` IS NULL )
			AND `failures` < ' . $this->sms_master->config['send_retries'] . '
			ORDER BY `priority` DESC, `failures`, `touched`' ) ;
		return $this->arrays_to_objects( $results );
	}
	
	/**
		@brief		List all numbers that are ready to be sent from a specific order.
		@param		int		$order_id		Find numbers in this order.
		@return		array					An array of number objects (database row).
	**/
	public function list_ready_to_send_from_order( $order_id )
	{
		$results = $this->db->fetchAll( 'SELECT * FROM order_numbers INNER JOIN orders USING (order_id) WHERE `sent` IS NULL AND `failures` < '. $this->sms_master->config['send_retries'] .' AND `order_id` = :order_id ORDER BY `touched`', array(
			'order_id' => $order_id,
		) ) ;
		return $this->arrays_to_objects( $results );
	}

	/**
		@brief		Marks this number as sent.
		@param		int		$number_id		ID of number to mark as sent.
	**/
	public function mark_sent( $number_id )
	{
		$this->db->executeUpdate( "UPDATE order_numbers SET sent = :sent WHERE `number_id` = :number_id",
			array(
				'number_id' => $number_id,
				'sent' => new \DateTime('now'),
			),
			array(
				'sent' => \Doctrine\DBAL\Types\Type::getType('datetime'),
			)
		);
	}
	
	/**
		@brief		Marks this number as unsent.
		@param		int		$number_id		ID of number to mark as unsent.
	**/
	public function mark_unsent( $number_id )
	{
		$this->db->executeUpdate( "UPDATE order_numbers SET sent = NULL WHERE `number_id` = :number_id",
			array(
				'number_id' => $number_id,
			)
		);
	}
	
	/**
		@brief		Resets the failure counter of a number.
		@param		int		$number_id		ID of number.
	**/
	public function reset_failures( $number_id )
	{
		$this->db->executeUpdate( "UPDATE order_numbers SET failures = 0 WHERE `number_id` = :number_id",
			array(
				'number_id' => $number_id,
			)
		);
	}
	
	/**
		@brief		Touch this number (prevent it from being used for a while).
		@param		int		$number_id		ID of number to touch.
	**/
	public function touch( $number_id )
	{
		$this->db->executeUpdate( "UPDATE order_numbers SET touched = now() + INTERVAL " . $this->sms_master->config['send_retry_delay'] . " SECOND WHERE `number_id` = :number_id",
			array(
				'number_id' => $number_id,
			)
		);
	}
	
	/**
		@brief		Untouch this number (make it ready to be sent).
		@param		int		$number_id		ID of number to untouch.
	**/
	public function untouch( $number_id )
	{
		$this->db->executeUpdate( "UPDATE order_numbers SET touched = null WHERE `number_id` = :number_id",
			array(
				'number_id' => $number_id,
			)
		);
	}	
}

