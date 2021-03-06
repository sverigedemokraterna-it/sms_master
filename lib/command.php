<?php
/**
	Handles commands sent to itself via $_POST.
**/

require_once( 'sms_master_include_cgi.php' );

if ( SMS_Master::is_cli() )
	exit;

$sms_master = new SMS_Master();

if ( ! $sms_master->check_nonce( $_POST ) )
	$sms_master->error( 'Invalid nonce!' );

echo $sms_master->handle_command( $_POST );

