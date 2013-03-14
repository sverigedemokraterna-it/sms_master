<?php
chdir( 'lib' );
require_once( 'sms_master_include_cgi.php' );

$sms_master = new SMS_Master();
$sms_master->handle_post();

