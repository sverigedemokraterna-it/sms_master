<?php
/**
	Basic settings
	--------------
	
	Basic settings meant to be customized in the config.local.php file, which is read after this file.
	
	Any settings found in the local file override settings here.

	# cp config.dist.php config.local.php
	Edit local.php
	Keep only the basic settings
	Change the basic settings
**/

// URL where the SMS Master is reachable. Used by the SMS Master to contact itself.
$config['base_url'] = 'http://smsmaster.my.company';

// Database configuration
$config['database']['hostname'] = 'HOSTNAME';
$config['database']['database'] = 'DATABASE';
$config['database']['username'] = 'USERNAME';
$config['database']['password'] = 'PASSWORD';

/**
	After having cleaned (emptied the phones of any messages), to which e-mail address(es)
	shall the cleaning report be sent to?
	
	Leave empty for no cleaning report.
	For several e-mail addresses, separate using commas.
**/
$config['clean']['email_addresses'] = 'cleaning_report@nonexistent.none';

/**
	The e-mail settings are used when sending reports.
**/
$config['email']['from_name'] = 'SD SMS Master';
$config['email']['from_address'] = 'noreply@nonexistent.none';
$config['email']['signature'] = "\n\n--\nSweden Democrat SMS Master";

/**
	Advanced settings
	-----------------
**/

/**
	The SMS Master's public and private keys.
	
	These keys are automatically generated if they do not exist.
	
	The private key is safe to have lying around because of a php exit command on the first line of the file.
**/

$config['public_key'] = file_get_contents( 'sms_master.public.key.php' );
$config['private_key'] = SMS_Master::load_keyfile( 'sms_master.private.key.php' );

// -------------------------------------- CLEANING --------------------------------------

/**
	Enable cleaning of phones.
	
	If phones are not cleaned, the message storage of sent and received messages could get filled up.
	
	Depends on the phone.
**/
$config['clean']['enabled'] = true;

/**
	How long to wait between cleaning runs.
	Standard is three days.
**/
$config['cron']['clean']['interval'] = 60 * 60 * 24 * 3;

// Language and locale to use. The default is English and whatever you find in /lib/locale is fine.
$config['locale'] = 'en_US.UTF-8';

// How many times to remain in the send loop before exiting.
// Used to prevent memory leaks. Use PHP_INT_MAX for a loop that never ends.
// Each loop has a 0.1 second sleep. Therefore 1000 * 0.1 = 100 idle seconds before quitting.
$config[ 'max_send_loops' ] = 1000;

// How many times to retry to send the message to a phone before giving up.
$config['send_retries'] = 3;
$config['send_retry_delay'] = 60 * 15;	// 15 minutes

// Temp directory. Relative to cron.php.
$config['temp_directory'] = 'temp';

// How long the script is allowed to run when receiving requests.
// This setting is ignored for the cron.
$config['time_limit'] = 300;
