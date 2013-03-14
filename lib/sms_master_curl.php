<?php
/**
	@brief		Wrapper class for CURL object.
**/
class SMS_Master_Curl
{
	/**
		@brief		A curl object.
		@var		$curl
	**/
	public $curl;
	
	public function __construct( $url, $POST )
	{
		$this->curl_init( $url, $POST );
	}
	
	/**
		@brief		Initialize a curl object with sane defaults.
		
		@param		string		$url		URL to curl to.
		@param		array		$POST		Send a _POST with the curl?
		@return		object		The curl resource.
	**/
	public function curl_init( $url, $POST = null )
	{
		$curl = curl_init(); // initialize curl handle
	
		curl_setopt( $curl, CURLOPT_URL, $url ); // set url to post to
		curl_setopt( $curl, CURLOPT_FAILONERROR, 1 );
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 0 );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_ENCODING, "UTF-8" );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 14 );			// 14 + 1 second timeout.
		
		if ( $POST !== null )
		{
			curl_setopt( $curl, CURLOPT_POST, 1 );				// Use POST method
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $POST );	// add POST fields
		}
		
		$this->curl = $curl;
		return $curl;
	}
}
