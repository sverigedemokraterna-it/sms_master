<?php

// Build requires and includes that are common to both CGI and CLI.

require_once( 'sms_master.php' );
require_once( 'sms_master_request.php' );
require_once( 'sms_master_request_admin.php' );
require_once( 'sms_master_request_numbers.php' );
require_once( 'sms_master_request_orders.php' );
require_once( 'sms_master_request_phones.php' );
require_once( 'sms_master_request_slaves.php' );
require_once( 'sms_master_request_users.php' );
require_once( 'sms_master_request_user_orders.php' );
require_once( 'sms_master_curl.php' );
require_once( 'sms_master_exception.php' );

// A debug function that spits out information about a variable.

if ( !function_exists('dbg') )
{
	if ( SMS_Master::is_cli() )
	{
		function dbg($variable)
		{
			echo date('Y-m-d H:i:s ');
			echo var_dump($variable);
		}
	}
	else
	{
		function dbg($variable)
		{
/*	
			$text = var_export( $variable, true ) . "\n";
			file_put_contents( '/tmp/sms.log', $text, FILE_APPEND );
			return;
*/			
			echo '<pre>';
			echo htmlspecialchars( var_dump($variable) );
			echo '</pre>';
			@ob_flush();
			@flush();
		}
	}
}

