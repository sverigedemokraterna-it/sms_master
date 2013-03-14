<?php

/**
	@brief		User admin requests.
	
	Requests that modify users.
**/
class SMS_Master_User_Admin_Request
	extends SMS_Master_Admin_Request
{
}

/**
	@brief		Create a user.
	@see		class_SMS_Master_Users::create
**/
class SMS_Master_Create_User_Request
	extends SMS_Master_User_Admin_Request
{
	/**
		@brief		An object of user data. See the user database.
		@in
		@var		$options
	**/
	public $options;
	
	/**
		@brief		The id of the newly-created user.
		@out
		@var		$user_id
	**/
	public $user_id;
	
	public function handle()
	{
		$user_id = $this->sms_master->users->create( $this->options );
		$user = $this->sms_master->users->get( $user_id );
		$this->user_id = $user->user_id;
	}
}

/**
	@brief		Delete a user.
	@see		class_SMS_Master_Users::delete
**/
class SMS_Master_Delete_User_Request
	extends SMS_Master_User_Admin_Request
{
	/**
		@brief		The id of the user to delete.
		@in
		@var		$user_id
	**/
	public $user_id;
	
	public function handle()
	{
		// The loaded_user shouldn't be able to delete himself.
		if ( $this->loaded_user->user_id == $this->user_id )
			throw new SMS_Master_User_Exception( SMS_Master_User_Exception::ERROR_UNABLE_TO_DELETE_SELF );
		$this->sms_master->users->delete( $this->user_id );
	}
}

/**
	@brief		Get a specific user.
	@see		class_SMS_Master_Users::get
**/
class SMS_Master_Get_User_Request
	extends SMS_Master_User_Admin_Request
{
	/**
		@brief		ID of user to retrieve.
		@in
		@var		$user_id
	**/
	public $user_id;
	
	/**
		@brief		The user database row object.
		@out
		@var		$user
	**/
	public $user; 
		
	public function handle()
	{
		$this->user = $this->sms_master->users->get( $this->user_id );
	}
}

/**
	@brief		List all the users
**/
class SMS_Master_List_Users_Request
	extends SMS_Master_User_Admin_Request
{
	/**
		@brief		An array of user database rows.
		@out
		@var		$users
	**/
	public $users;
		
	public function handle()
	{
		$this->users = $this->sms_master->users->list_();
	}
}

/**
	@brief		Update a user.
	@see		class_SMS_Master_Users::update
**/
class SMS_Master_Update_User_Request
	extends SMS_Master_User_Admin_Request
{
	/**
		@brief		User object from the database.
		@in
		@out
		@var		$user
	**/
	public $user;
		
	public function handle()
	{
		$this->sms_master->users->update( $this->user->user_id, $this->user );
		// Return the user as a convenience.
		$this->user = $this->sms_master->users->get( $this->user->user_id );
	}
}

