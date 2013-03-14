<?php
chdir( 'lib' );
require_once( 'sms_master_include_cli.php' );

if ( ! SMS_Master::is_cli() )
	exit;
	
$sms_master = new SMS_Master();
$sms_master->cron();

