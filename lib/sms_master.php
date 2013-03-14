<?php

require_once ( 'class_sms_master_generic.php' );
require_once ( 'class_sms_master_log.php' );
require_once ( 'class_sms_master_settings.php' );
require_once ( 'class_sms_master_users.php' );
require_once ( 'class_sms_master_slaves.php' );
require_once ( 'class_sms_master_phones.php' );
require_once ( 'class_sms_master_orders.php' );
require_once ( 'class_sms_master_numbers.php' );

/*!
	@mainpage	SD SMS Master
	
	@section	smsm_introduction				Introduction
		
	@smsm is an SMS-sending server that allows clients to send one-way SMS's using multiple remote machines and their connected phones.
	
	@smsm consists of three parts:
	
	- lib(rary) contains the logic.
	- web contains web server scripts and is needed on the server.
	- A Wordpress plugin which can and should be used to configure and monitor the master.
	
	@smsm is free software, as per the @ref smsm_license
	
	@section	smsm_introduction_works			How it works
	
	The web server (master) contains the SMS Master itself. Clients (users) connect to the master and create orders. Orders contain a text to send and a list of numbers to send to.
	
	The master then connects to remote machines (slaves) and distributes the order numbers (+text) to all enabled phones on the slaves.
	
	The communication is one-way: incoming SMS's are not read.
	
	@section	smsm_requirements				Requirements
	
	- Gnokii
	- PHP
	- SQL server (one)
	- Web server (one - called the master)
	- Shell on the web server for running cron.php.
	- Slave (one+)
	- SSH on both the master and slave(s)
	- Phone that gnokii supports (one+ per slave)
	
	@subsection	smsm_requirements_database		Database

	The database abstraction layer (Doctrine) should work with most common database servers. Only mysql has been tested.
	
	@subsection	smsm_requirements_phones		Phones
	
	The phones should communicate via USB, in order to bypass BlueTooth weirdness.

	Phones that work well are old SonyEricssons that have USB data/charge cables, for example the K550 or Z530.
	
	@subsection	smsm_requirements_ssh			SSH

	Communication between the master and the slaves is acheived using SSH public keys.
	
	For extra security, why not put the whole system on its own VPN?
	
	@subsection	smsm_requirements_webserver		Web server

	Any old webserver with PHP + openssl + ssh support should work just fine. Only nginx has been tested.
	
	@section	smsm_installation				Installation
	
	Installation, although possible manually, is easiest accomplished by using the Wordpress plugin, which can be found on the @smsm site.
	This installation assumes that the plugin is installed and activated on some webserver that can reach the master's web server.
	
	@subsection	smsm_installation_master		Installation of the master
	
	Create a database for the master.
	
	Install the master files onto a web server. In the lib directory, download the lib files.
	
	Copy config/config.dist.php to config/config.local.php and change whatever settings necessary. At a bare minimum the URL and database settings will need changing.
	
	Start a shell on the server and run cron.sh, which loops running cron.php. The first time master is run it will create config/sms_master.public.key.php, which contains the master's public key. We'll need this key for the Wordpress plugin. This file can be read by pointing your browser to `URL/config/sms_master.public.key.php`.
	
	The master is now ready to be initialized and configured by the Wordpress plugin.
	
	@subsection	smsm_installation_wordpress		Installation of Wordpress plugin
	
	Download and activate the plugin. Click on the plugin's menu option and you will be presented with an option to have git automatically download the latest lib. If this does not work you will have to manually download and copy the SMS Master lib package into the plugin's lib subdirectory.
	
	Once the lib directory is ready, visiting the SD SMS Master menu in the admin panel will result in an error message. This is because neither the plugin nor the masters is yet configured.
	
	Configuration is as follows:
	
	- Visit the Tools submenu.
	- Generate an SSH keypair.
	- Visit the Settings submenu.
	- Enter the URL to the server.
	- Enter the masters's public key.
	- Enter this client's private and public keys.
	- Update the settings.
	- Click on the Install to SMS Master button.
	- Test the settings.
	
	If all has gone well, the SMS Master should have taken the client's public key and created a new administrator user. The Test button should reply with the current time.
	
	After configuring a slave, you can use the menu items to add slaves and phones and then test them.
	
	Debugging this procedure:
	
	- Is the URL reachable?
	- Have the tables in the @smsm database been created?
	- Has a user been created in the database?
	
	@subsection	smsm_installation_slave		Installation of a slave
	
	A slave is nothing but a machine, with a gnokii phone, that the master connects to and uses to send the SMS's. The only software necessary is an openssh-server and a configured gnokii.
	
	Configuration is as follows:
	
	- Create a user. The username `smsslave` is popular.
	- Give the user access to `/dev/ttyACM*`. Try adding the user to the `dialout` group.
	- Generate an SSH keypair for the user using `ssh-keygen`.
	- Add the public key into the `.ssh/authorized_keys` file, so that the master can connect to this slave.
	- Create `~/.config/gnokii/config`
	
	<pre><code>
	[global]
	model = AT
	port = /dev/ttyACM0
	connection = serial

	[phone_1]
	port = /dev/ttyACM0
	model = AT
	connection = serial
	</code></pre>
	
	The above config works for USB-connected SonyEricsson phones in "phone mode".
	
	- Connect the phone.
	- Check the phone connection: `su -l smsslave -c "gnokii --phone 1 --identify"`. You should see some text identifying the phone.
	
	Configuration of the slave is complete. The master has to be configured with this new slave. I suggest using the Wordpress plugin for that. Use the above settings and SSH keypair and press the `Identify slave` button to test the connection.
	
	After adding the slave to the master, add the phone. The above example shows that the phone index is 1.
	
	@subsection	smsm_installation_checklist		Installation checklist
	
	@par Master
	
	- Have you edited the `config/config.local.php` file?
	- Does the master have a valid keypair in `config/`?
	- Is the cron.sh loop running?
	- Are there any slaves?
	- Have you identified the slaves?
	- Do the slaves have any phones?
	- Have you identified each phone?
	
	@par Slave
	
	- Do you see the phone in `/dev/ttyACM*`?
	- Have you created gnokii's config file for the smsslave user?
	- Can gnokii identify the phone?
	- Can you send an SMS as the smsslave user?
	
	@par Wordpress plugin
	
	- Have you downloaded / copied the lib into the lib directory of the plugin? There's a button for that, if your git works...
	- Have you filled in the settings?
	- Does a user exist in the database corresponding with your settings?
	- Have you enabled the master from the overview?
	
	@section	smsm_usage						Usage
	
	You need to program the clients to talk to the master. Luckily, the SMS Master is well documented. Use doxygen to create the documentation in the `apidoc` directory.
	
	For examples of how to connect to a master and create orders and what not, see the Wordpress plugin.	
	
	@subsection	smsm_usage_cleaning				Cleaning
	
	Some phone store incoming and outgoing messages which can fill up the message storage and cause sending errors. Cleaning / emptying deletes all stored SMS's, optionally sending a report to any administrators.
	
	@section	smsm_changelog					Changelog
	
	@par		1.0 2013-03-14
	
	Initial release.
	
	@section	smsm_development				Development
	
	See the <a href="https://it.sverigedemokraterna.se/program/sms-master/">SD SMS Master project page</a> for download links
	and links to the git.
	
	The project author welcomes translations.
	
	@section	smsm_libraries					Libraries
	
	@smsm uses the following libraries:

	- The <a href="http://doctrine-project.org" title="The Doctrine Project">Doctrine DBAL</a>
	- <a href="https://launchpad.net/php-gettext">PHP-gettext</a>
	- <a href="http://phpmailer.codeworxtech.com/">PHPmailer</a>
	
	@section	smsm_license					License
	
	The lib directory is licensed under the <a title="GNU General Public License v3" href="http://gplv3.fsf.org/">GPL, version 3</a>.
	
	Subdirectories are licensed under whatever is in the subdirectory.
	
	@author		Sverigedemokraterna		http://www.sverigedemokraterna.se
	@author		Edward Plainview		edward.plainview@sverigedemokraterna.se
**/

/**
	@brief		Main class.
**/
class SMS_Master
{
	/**
		@brief		Settings loaded from the config.php file.
		@var		array		$config
	**/
	public $config; 

	/**
		@brief		Database class.
		@see		class_SMS_Master_Database
		@var		$db
	**/
	public $db;

	/**
		@brief		Logging class.
		@see		class_SMS_Master_Log
		@var		$log
	**/
	public $log;

	/**
		@brief		Phone class.
		@see		class_SMS_Master_Phones
		@var		$phone
	**/
	public $phone;

	/**
		@brief		Settings class.
		@see		class_SMS_Master_Settings
		@var		$settings
	**/
	public $settings;

	/**
		@brief		Slave handling class.
		@see		class_SMS_Master_Slaves
		@var		$slaves
	**/
	public $slaves;

	/**
		@brief		User handling class.
		@see		class_SMS_Master_Users
		@var		$users
	**/
	public $users;
	
	public function __construct()
	{
		$this->load_config();
		
		// Is the temp directory writeable?
		if ( ! is_writeable( $this->config['temp_directory'] ) )
			$this->error( 'Temp directory ' . $this->config['temp_directory'] . ' is not writeable!' );
		
		if ( isset( $this->config['time_limit'] ) )
			set_time_limit( $this->config['time_limit'] );
		
		$this->db = new class_SMS_Master_Database( $this );
		$this->log = new class_SMS_Master_Log( $this );
		$this->settings = new class_SMS_Master_Settings( $this );
		$this->users = new class_SMS_Master_Users( $this );
		$this->slaves = new class_SMS_Master_Slaves( $this );
		$this->phones = new class_SMS_Master_Phones( $this );
		$this->orders = new class_SMS_Master_Orders( $this );
		$this->numbers = new class_SMS_Master_Numbers( $this );
	}
	
	/**
		@brief		Adds nonce data to an array.
		
		This is used to add a nonce to the locally sent commands (lib/command*php).
		
		The files are potentially publically reachable and it would therefore be smart to restrict @e usage of the
		commands. @e Access can be restricted by the webserver config.
		
		@param		array		$array		Array to which to add nonce data.
	**/
	public function add_nonce( $array )
	{
		$master_private_key = $this->config['private_key'];
		
		// The nonce seed is there to make each signing unique.
		// If a seed is already present, use it. Else make a new one.
		if ( ! isset( $array[ '_nonce_seed' ] ) )
			$nonce_seed = $this->hash( microtime(), 8 );
		else
			$nonce_seed = $array[ '_nonce_seed' ];
		
		unset( $array[ '_nonce' ] );
		unset( $array[ '_nonce_seed' ] );
		
		$string = serialize( $array );
		
		$array[ '_nonce' ] = $this->hash( $string . $master_private_key . $nonce_seed, 8 );
		$array[ '_nonce_seed' ] = $nonce_seed;
		
		return $array;
	}
	
	/**
		@brief		Is this $POST locally generated?
		
		@param		array		$array		The $array to check.
	**/
	public function check_nonce( $array )
	{
		if ( ! isset( $array[ '_nonce' ] ) )
			return false;
		if ( ! isset( $array[ '_nonce_seed' ] ) )
			return false;
		$nonce = $array[ '_nonce' ];
		$new_nonce = $this->add_nonce( $array );
		return ( $nonce == $new_nonce[ '_nonce' ] );
	}
	
	/**
		@brief		Cleans a phone.
		
		@see		class_SMS_Master_Phones::clean
		@param		object		$options		See options for class_SMS_Master_Phones::clean
	**/
	public function clean_phone( $options )
	{
		$reply = new stdClass();
		$reply->output = $this->phones->clean( $options );
		$this->phones->untouch( $options->phone_id );
	
		$reply = serialize( $reply );
		$reply = base64_encode( $reply );
		echo $reply;
		exit;
	}
	
	/**
		@brief		The main loop.
		
		Run a set amount of times. See the config variable max_send_loops.
		
		Each loop takes, at a minimum, 0.1 seconds. If there is something to send a loop can take several seconds.
	**/
	public function cron()
	{
		$counter = 0;
		while ( $counter < $this->config[ 'max_send_loops' ] )
		{
			$counter++;
			if ( $this->settings->get( 'enabled' ) == true )
				$this->send_unsent();
			usleep( 100 * 1000 );	// 0.1 seconds
		}
	}
	
	/**
		@brief		Cleaning cron.
		
		* Cleans the temp directory from old files.
		* Cleans the phones from incoming SMSs.
	**/
	public function cron_clean()
	{
		// Clean the temp directory
		$temp_files = glob( $this->config['temp_directory'] . '/*' );
		foreach( $temp_files as $temp_file )
		{
			// Files shouldn't be kept more than 5 minutes
			if ( time() - filemtime($temp_file) > 5*60 )
				unlink( $temp_file );
		}
		
		// Get a list of all phones.
		$phones = $this->phones->find_all_available();
		
		$curls = array();
		foreach( $phones as $phone )
		{
			if ( ! $phone->clean )
				continue;
			$curls[] = $this->prepare_clean_phone( $phone );
		}
		
		if ( count( $curls ) < 1 )
			return;
	
		$mh = curl_multi_init();
		
		foreach( $curls as $curl )
			curl_multi_add_handle($mh, $curl->curl);
		
		$mrc = curl_multi_exec($mh, $active);
	
		do
		{
			usleep(100000);
			curl_multi_exec( $mh, $running );
		}
		while ( $running >  0);
		
		$clean_data = array();
		
		foreach( $curls as $index => $curl )
		{
			$reply = curl_multi_getcontent( $curl->curl );
			$reply = base64_decode( $reply );
			$reply = @unserialize( $reply );
			$clean_data[ $curl->phone['phone_id'] ] = $reply->output;
		}
		
		$this->send_cleaning_report( $clean_data );	
	}
	
	/**
		@brief		Shuts down because of an error.
		@param		string		$error_message		Error message.
	**/
	public function error( $error_message )
	{
		$error_message = call_user_func_array( 'sprintf', func_get_args() );
		if ( isset( $this->log ) )
			$this->log->error( 'Dying: ' . $error_message );
		if ( self::is_cli() )
			echo date('Y-m-d H:i:s') . "\terror\t" . $error_message . "\n";
		$this->shutdown();
	}
	
	/**
		@brief		Generates an openssl public/private keypair.
		
		@return		object		->public contains the public key and ->private contains the private key.
	**/
	public static function generate_keypair()
	{
		$rv = new stdClass();
		// See Brad's comment @ http://www.php.net/manual/en/function.openssl-pkey-new.php
		// Create the keypair
		$res=openssl_pkey_new();
		
		// Get private key
		openssl_pkey_export( $res, $rv->private );
		// Get public key
		$pubkey=openssl_pkey_get_details( $res );
		$rv->public = $pubkey["key"];
		return $rv;
	}
	
	/**
		@brief		Return an 8-character hash of a public key.
		@param		string		$public_key		The public key to hash.
		@return		string		8 character hash of public key.
	**/
	public static function get_public_key_id( $public_key )
	{
		$public_key = trim( $public_key );
		return substr( SMS_Master::hash( $public_key ), 0, 8 );
	}
	
	/**
		@brief		Returns a temporary file name in the temp dir.
		
		@param		string		$prefix		The prefix the file name should have.
	**/
	
	public function get_temp_filename( $prefix )
	{
		return tempnam( $this->config['temp_directory'], $prefix );
	}
	
	/**
		@brief		Handle an install request (container).
		
		@param		SMS_Master_Install_Container		$container		The install container.
	**/
	private function handle_install_request( $container )
	{
		if ( intval( $this->users->count() ) > 0 )
			throw new SMS_Master_Exception( 'User table already exist.' );
		
		// Check that the SQL install file exists
		$sql = 'lib/install.sql';
		if ( ! is_readable( $sql ) )
			throw new SMS_Master_Exception( 'install.sql file is not readable!' );
		
		// Create the database tables
		$this->db->executeQuery( file_get_contents( $sql ) );
		
		// Create an admin user
		$user = new stdClass();
		$user->administrator = true;
		$user->enabled = true;
		$user->user_description = sprintf( 'Installed admin %s', date( 'Y-m-d H:i:s' ) );
		$user->public_key = $container->client_public_key;
		$user_id = $this->users->create( $user );
		$container->request = new SMS_Master_Install_Request();
		$container->request->user = $this->users->get( $user_id );
	}
	
	/**
		@brief		Handles the command in the $_POST, if any.
	**/ 
	public function handle_post()
	{
		if ( count( $_POST ) < 1 )
			$this->shutdown();
		
		// The keys a and b in the POST must be set.
		if ( !isset($_POST['a']) || !isset($_POST['b']) )
			$this->error( 'POST missing A and B.' );
		
		$data = base64_decode( $_POST['a'] );
		$key = base64_decode( $_POST['b'] );;
		
		// Prepare the server's private key
		$master_private_key = $this->config['private_key'];
		$master_private_key = openssl_get_privatekey( $master_private_key );
		
		// Try to open the data using the private key.
		$result = openssl_open( $data, $opened_data, $key, $master_private_key );
		if ( $result === false )
			$this->error( 'Unable to open sealed data.');
		
		$container = unserialize( $opened_data );
		if ( $container === false )
			$this->error( 'Container could not be unserialized.');
		
		// Is this an install request?
		if ( is_a( $container, 'SMS_Master_Install_Container' ) )
		{
			try
			{
				$this->handle_install_request( $container );
				$o = new stdClass();
				$o->client_public_key = $container->request->user->public_key;
				$o->reply = $container->request;
				$this->send_reply( $o );
				// Install was OK. Shut it all down.
				$this->shutdown();
			}
			catch ( SMS_Master_Exception $e )
			{
				$this->error( 'Install request failed: %s', $e->get_error_message() );
			}
		}
		// Check the signature.
		// This requires that we have the public key.
		$user = $this->users->find( 'public_key_id', $container->public_key_id );
		
		if ( $user === false )
			$this->error( 'User for %s not found.', $container->public_key_id );
	
		if ( $user->enabled == 0 )
			$this->error( 'User %s is inactive.', $container->public_key_id );
		
		// Check the signature
		$client_public_key = openssl_get_publickey( $user->public_key );
		if ( openssl_verify( $container->request, $container->signature, $client_public_key ) !== 1 )
			$this->error( 'Signature verfication failed.' );
		
		// Unserialize the request
		$container->request = @unserialize( $container->request );
		if ( $container->request === false )
			$this->error( 'Unable to unserialize the request.' );
		
		// Give the request some variables.
		$container->request->sms_master = $this;
		$container->request->log = $this->log;
		$container->request->loaded_user = $user;
		try
		{
			// Ask the request to handle itself.
			// Pre handle
			$container->request->handle_1();
			// And actually handle
			$container->request->handle();
			// Call the specific request's cleanup.
			$container->request->clean();
		}
		catch ( SMS_Master_Exception $e )
		{
			$container->request->error_message = $e->get_error_message();
		}
		
		// Call the general, internal cleanup.
		$container->request->clean_1();
		
		$o = new stdClass();
		$o->reply = $container->request;
		$o->client_public_key = $user->public_key;
		$this->send_reply( $o );
		$this->shutdown();
	}
	
	/**
		@brief		Handles the reply from a send_text command.
		
		@param		resource		$curl		CURL resource that sent the command.
		@param		object			$reply		The reply from send_text.
		@see		SMS_Master::send_text
	**/
	public function handle_send_reply( $curl, $reply )
	{
		$code = $reply->code;
		
		$order_id = $curl->order_id;
		$phone_id = $curl->phone->phone_id;
		$number_id = $curl->number->number_id;
		 
		if ( $code !== 0 )
		{
			// 255 = no route to host, so don't count it as a failure.
			if ( $code == 255 )
			{
			    $this->numbers->untouch( $number_id );
			}
			else
			{
				$this->orders->log_error( $order_id, date('Y-m-d H:i:s ') . "\n\tPhone: " . $phone_id . "\n\tNumber: " . $curl->number->number . "\n\t" . implode("\n\t", $reply->output) . "\n" );
				$this->numbers->increase_fails( $number_id );
				$this->phones->untouch( $phone_id );
			}
		}
		else
		{
			$this->numbers->mark_sent( $number_id );
			$this->numbers->untouch( $number_id );
			$this->phones->increase_sent_stats( $phone_id );
			$this->phones->touch_successfully( $phone_id );
		}
		$this->orders->maybe_complete( $order_id );
	}
	
	/**
		@brief		Returns a hash of a string.
		
		@param		string		$string		String to hash.
		@param		int			$size		Characters to return.
		@return		string					Hashed string
	**/
	public static function hash( $string, $size = 8 )
	{
		$rv = hash( 'sha512', $string );
		return substr( $rv, 0, $size );
	}
	
	/**
		@brief		Increase the sent SMS statistics of the master.
	**/
	public function increase_sent_stats()
	{
		// Total stat
		$key = 'stat_sent_sms';
		$sent = $this->settings->get( $key );
		$this->settings->update( $key, $sent + 1 );
		
		// This year
		$key = 'stat_sent_sms_' . date('Y');
		$sent = $this->settings->get( $key );
		$this->settings->update( $key, $sent + 1 );
		
		// This month
		$key = 'stat_sent_sms_' . date('Y_m');
		$sent = $this->settings->get( $key );
		$this->settings->update( $key, $sent + 1 );
	}
	
	/**
		@brief		Are we running from the cli?
		
		@return		bool		True, if running php from the command line.
	**/
	public static function is_cli()
	{
		return ! isset( $_SERVER['REQUEST_URI'] );
	}
	
	/**
		@brief		Loads the config from the config files.
		
		Will automatically generate a keypair if none exists.
	**/
	private function load_config()
	{
		$config = array(
			'database' => array(),		// Prepare this array for the coming settings in the file itself.
		);
		
		// Change our working directory to config, for convenience of any file reading functions.
		chdir( '../config' );
		
		// Check for keypair existence.
		$file = 'sms_master.public.key.php';
		if ( ! is_readable( $file ) )
		{
			$keypair = SMS_Master::generate_keypair();
			file_put_contents( $file, $keypair->public );
			
			// Protect the private key by making the key file a PHP file with an exit at the top.
			$phpexit = "<?php exit;\n";
			file_put_contents( 'sms_master.private.key.php', $phpexit . $keypair->private );
		}
		if ( ! is_readable( $file ) )
			$this->error( 'Unable to write the public/private keypair to config/%s. Check that the directory is writeable.', $file );
		
		require_once( 'config.dist.php' );
		require_once( 'config.local.php' );
		$this->config = $config;
		
		// Now go to the root
		chdir( '..' );
		
		setlocale( LC_ALL, $this->config['locale'] );

		// Load the translation system.
		// Normal gettext is broken to hell, therefore we have to use php-gettext.
		require_once( 'php-gettext/gettext.inc' );
		// The language is the part of the locale before the period.
		$language = preg_replace( '/\..*/', '', $this->config['locale'] );
		// And the charset is after the period.
		$charset = preg_replace( '/.*\./', '', $this->config['locale'] );
		T_setlocale( LC_MESSAGES, $language );
		T_bindtextdomain( 'default', 'lib/locale' );
		T_bind_textdomain_codeset( 'default', $charset );
		T_textdomain( 'default' );
	}
	
	/**
		@brief		Loads a key file, stripping off unnecessary data.
		
		The first line, containing the "?php exit;" is stripped.
		
		@param		string		$file		The filename to load.
		@return		string		The loaded keyfile, stripped of trash.
	**/
	
	public static function load_keyfile( $file )
	{
		$data = file_get_contents( $file );
		$data = trim( $data );
		// Remove the first line
		$data = array_filter( explode( "\n", $data ) );
		array_shift( $data );
		$data = implode( "\n", $data );
		return $data;
	}
	
	/**
		@brief		Returns the merge of two objects.
		
		@param		object		$obj1		The base object.
		@param		object		$obj2		The object from which to append / overwrite new values.
		@return		object					The merged object.
	**/
	public static function merge_objects( $obj1, $obj2 )
	{
		return (object) array_merge( (array)$obj1, (array)$obj2 );
	}
	
	/**
		@brief		Prepares a phone cleaning request.
		
		@param		object		$phone		A phone object (from phones->get).
		@return		SMS_Master_Curl			A curl object.
	**/
	public function prepare_clean_phone( $phone )
	{
		// Touch them, to make then inaccessible for a while.
		$this->phones->touch( $phone->phone_id );
		
		$url = $this->config['base_url'] . '/lib/command_clean_phone.php';
		
		$POST = array(
			'phone_id' => $phone->phone_id,
			'empty' => true,
		);
		
		$POST = $this->add_nonce( $POST );
		
		$curl = new SMS_Master_Curl( $url, $POST );
		$curl->phone = $phone;
		
		return $curl;
	}
	
	/**
		@brief		Prepares a number and phone for sending.
		
		@param		object		$number		A order number object.
		@param		object		$phone		A phone object.
		@return		SMS_Master_Curl			A curl object.
	**/
	public function prepare_send_text( $number, $phone )
	{
		// Touch them, to make then inaccessible for a while.
		$this->phones->touch( $phone->phone_id );
		$this->numbers->touch( $number->number_id );
		
		$url = $this->config['base_url'] . '/lib/command_send_text.php';
		
		$POST = array(
			'number_id' => $number->number_id,
			'phone_id' => $phone->phone_id,
		);
		
		$POST = $this->add_nonce( $POST );
		
		$curl = new SMS_Master_Curl( $url, $POST );
		
		$curl->order_id = $number->order_id;
		$curl->number = $number;
		$curl->phone = $phone;
		
		return $curl;
	}
	
	/**
		@brief		Is a cron_type ready for cronning?
		
		@param		string		$cron_type		Type of cron to check.
		@param		int			$seconds		Time between crons.
	**/
	public function ripe_for_cron( $cron_type, $seconds )
	{
		$last_cronned = intval( $this->settings->get( 'cron_' . $cron_type ) );
	
		$time = time();
	
		if ( $time < $last_cronned + $seconds )
			return false;
		return true;
	}
	
	/**
		@brief		Sends an array of phone_id -> output to the admins, if there are email addresses set.
		
		@param		array		$clean_data		An array of phone_id -> output.
		@see		SMS_Master::cron_clean
	**/
	public function send_cleaning_report( $clean_data )
	{
		if ( $this->config['clean']['email_addresses'] == '' )
			return;
		
		$email_addresses = str_replace( ' ', ',', $this->config['clean']['email_addresses'] );
		$email_addresses = explode( ',', $email_addresses ); 
		$email_addresses = array_filter( $email_addresses );
		
		$mail_data = array(
			'from' => array( $this->config['email']['from_address'] => $this->config['email']['from_name'] ),
			'to' => $email_addresses,
			'subject' => 'Cleaning report',
			'body' => '',
		);
		
		foreach( $clean_data as $phone_id => $output )
			$mail_data['body'] .= "Report from phone $phone_id:\n\n" . implode( "\n", $output ) . "\n\n";
		
		$mail_data['body'] .= $this->config['email']['signature'];
		
		$this->send_mail( $mail_data );
	}
	
	/**
		@brief		Prepares and sends (echoes) a container.
		
		Encrypts the container using the client's public key.
		
		@param		SMS_Master_Reply_Container		$container				A reply container.
		@param		string							$client_public_key		The client's public key, with which to seal the container.
	**/
	private function send_container( $container, $client_public_key )
	{
		$client_public_key = openssl_get_publickey( $client_public_key );
		
		// Seal the container.
		$container = serialize( $container );
		openssl_seal( $container, $sealed_container, $keys, array( $client_public_key ) );
		
		$reply_string = array(
			'a' => $sealed_container,
			'b' => $keys[0],
		);
		
		$reply_string = serialize( $reply_string );
		$reply_string = base64_encode( $reply_string );
		
		echo $reply_string;
	}
	
	/**
		@brief		Sends a command to a slave.
		
		@param		object		$slave			Database row array of a slave.
		@param		string		$command		Command to send to the phone.
		@return		object						stdClass with the return code in ->code and text output in ->output.
	**/
	public function send_slave_command( $slave, $command )
	{
		// Save the keys to disk somewhere so that ssh can read the files.
		$tempfile = $this->get_temp_filename( $slave->hostname . '.' );
		$private_key = $tempfile;
		$public_key = $tempfile . '.pub';
		file_put_contents( $public_key, $slave->public_key );
		file_put_contents( $private_key, $slave->private_key );
		
		// Since www-data doesn't have its own home directory, ~/.ssh can't be created. So tell SSH to not use known_hosts.
		$command = "ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -p " . $slave->port . " -l " . $slave->username . " -i $private_key " . $slave->hostname . " -C \"$command 2>&1\""; //
		
		$output = '';
		exec( $command, $output, $code );
		
		// Here would normally be a good time to check if $code == 255, seeing that that's the error SSH spits out
		// if there is a connection failure. But it can also be an error code from the run command, so we can do nothing.
		
		// We're done contacting the slave. Remove keys.
		unlink( $public_key );
		unlink( $private_key );
	
		$return = new stdClass();
		$return->code = $code;
		$return->output = $output;
		return $return;
	}
	
	/**
		Sends mail using the phpmailer class.
		
		Just a wrapper for phpmailer.
		
		@param		array		$mail_data		Array of mail data.
		@return		bool						True if the mail was sent correctly.
	**/
	public function send_mail($mail_data)
	{
		require_once( 'phpmailer/class.phpmailer.php' );
		// backwards compatability
		if (isset($mail_data['bodyhtml']))
			$mail_data['body_html'] = $mail_data['bodyhtml'];
		
		$mail = new PHPMailer();
		
		// Mandatory
		$mail->From		= key($mail_data['from']);
		$mail->FromName	= reset($mail_data['from']);
		
		$mail->Subject  = $mail_data['subject'];
		
		// Optional
		
		// Often used settings...
	
		if (isset($mail_data['to']))
			foreach($mail_data['to'] as $email=>$name)
			{
				if (is_int($email))
					$email = $name;
				$mail->AddAddress($email, $name);
			}
			
		if (isset($mail_data['cc']))
			foreach($mail_data['cc'] as $email=>$name)
			{
				if (is_int($email))
					$email = $name;
				$mail->AddCC($email, $name);
			}
	
		if (isset($mail_data['bcc']))
			foreach($mail_data['bcc'] as $email=>$name)
			{
				if (is_int($email))
					$email = $name;
				$mail->AddBCC($email, $name);
			}
			
		if (isset($mail_data['body_html']))
			$mail->MsgHTML($mail_data['body_html'] );
	
		if (isset($mail_data['body']))
			$mail->Body = $mail_data['body'];
		
		if (isset($mail_data['attachments']))
			foreach($mail_data['attachments'] as $attachment=>$filename)
				if (is_numeric($attachment))
					$mail->AddAttachment($filename);
				else
					$mail->AddAttachment($attachment, $filename);
	
		if ( isset( $mail_data['reply_to'] ) )
		{
			foreach($mail_data['reply_to'] as $email=>$name)
			{
				if (is_int($email))
					$email = $name;
				$mail->AddReplyTo($email, $name);
			}
		}
				
		// Seldom used settings...
		
		if (isset($mail_data['wordwrap']))
			$mail->WordWrap = $mail_data[wordwrap];
	
		if (isset($mail_data['ConfirmReadingTo']))
			$mail->ConfirmReadingTo = true;
		
		if (isset($mail_data['SingleTo']))
		{
			$mail->SingleTo = true;
			$mail->SMTPKeepAlive = true;
		}
		
		if (isset($mail_data['SMTP']))									// SMTP? Or just plain old mail()
		{
			$mail->IsSMTP();
			$mail->Host	= $mail_data['smtpserver'];
			$mail->Port = $mail_data['smtpport'];
		}
		else
			$mail->IsMail();
		
		if ( isset($mail_data['charset']) )
			$mail->CharSet = $mail_data['charset'];
		else
			$mail->CharSet = 'UTF-8';
		
		if ( isset($mail_data['content_type']) )
			$mail->ContentType  = $mail_data['content_type'];
		
		if ( isset($mail_data['encoding']) )
			$mail->Encoding  = $mail_data['encoding'];
		
		// Done setting up.
		if(!$mail->Send())
			$returnValue = $mail->ErrorInfo;
		else 
			$returnValue = true;
			
		$mail->SmtpClose();
		
		return $returnValue;		
	}
	
	/**
		Sends a request to the master.
		
		The $o (options) object is as follows:
		- @b client_private_key Private key of the client.
		- @b client_public_key Public key of the client.
		- @b master_public_key Master's public key.
		- @b master_url URL to the SMS master / server.
		- @b request The SMS_Master_Request to send.
		
		@param		object		$o		Options object.
		
		@return		A reply object.
		@throws		SMS_Master_Connection_Exception 
	**/
	public static function send_request( $o )
	{
		// Put the request in an SMS Master Container
		if ( ! isset($o->container) )
			$request_container = new SMS_Master_Request_Container();
		else
			$request_container = $o->container;
		
		$request_container->request = serialize( $o->request );
		
		// Insert the public_key_id
		$request_container->public_key_id = SMS_Master::get_public_key_id( $o->client_public_key );
		
		// Sign the request
		$client_private_key = openssl_get_privatekey( $o->client_private_key );
		openssl_sign( $request_container->request, $request_container->signature, $client_private_key );
		
		$master_public_key = openssl_get_publickey( $o->master_public_key );
		if ( $master_public_key === false )
			throw new SMS_Master_Connection_Exception( SMS_Master_Connection_Exception::ERROR_INVALID_MASTER_PUBLIC_KEY );
		openssl_seal( serialize( $request_container ),  $encrypted_object, $encryption_keys, array( $master_public_key ) );
		
		$POST = array(
			'a' => base64_encode( $encrypted_object ),
			'b' => base64_encode( $encryption_keys[0] ),
		);
		
		$url = trim( $o->master_url, '/' ) . '/index.php';
		
		$curl = curl_init(); // initialize curl handle
	
		curl_setopt( $curl, CURLOPT_URL, $url );			// set url to post to
		curl_setopt( $curl, CURLOPT_FAILONERROR, 1 );
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 0 );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_ENCODING, "UTF-8" );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 20 );			// 20 second timeout. 
		curl_setopt( $curl, CURLOPT_POST, 1 );				// Use POST method
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $POST );	// add POST fields
		
		$result = curl_exec( $curl ); // run the whole process
		
		if ( $result === false )
			throw new SMS_Master_Connection_Exception( SMS_Master_Connection_Exception::ERROR_UNABLE_TO_CONNECT );
		
		$result = base64_decode( $result );
		if ( $result === false )
			throw new SMS_Master_Connection_Exception( SMS_Master_Connection_Exception::ERROR_REPLY_NOT_SERIALIZED );
		
		$result = @unserialize( $result );
		if ( $result === false )
			throw new SMS_Master_Connection_Exception( SMS_Master_Connection_Exception::ERROR_REPLY_NOT_SERIALIZED );
		
		// And now try to open the reply
		openssl_open( $result['a'],  $opened_reply, $result['b'], $client_private_key );
		
		$reply_container = unserialize( $opened_reply );
		if ( ! is_a( $reply_container, 'SMS_Master_Reply_Container' ) )
			throw new SMS_Master_Connection_Exception( SMS_Master_Connection_Exception::ERROR_REPLY_NOT_SERIALIZED );
		
		if ( $reply_container->reply->error_message !== null )
			throw new SMS_Master_Exception( $reply_container->reply->error_message );
		
		return $reply_container->reply;
	}
	
	/**
		@brief		Send an SMS to a phone.
		
		The options are:
		- @e int @b number_id The ID of the number to send to.
		- @e int @b phone_id The ID of the phone to use.
		- @e int @b slave_id The ID of the slave to contact.
		
		The reply object has:
		- @e int @b code The command's return code.
		- @e array @b output The command's output.
		
		The reply object is first serialized, then base64 encoded.
		
		@param		array		$options		The options array.
		@return		string		Base64 encoded, serialized reply object.
		@see		SMS_Master::send_slave_command
	**/
	public function send_text( $options )
	{
		$number = $this->numbers->get( $options->number_id );
		$phone = $this->phones->get( $options->phone_id );
		$slave = $this->slaves->get( $phone->slave_id );
		
		$text = trim( $number->text );
		$text_encoding = mb_detect_encoding($text, "ASCII, UTF-8", true);
		$replacements = array(
			'"' => "'",
		);
		foreach( $replacements as $search => $replace )
			$text = str_replace( $search, $replace, $text );
	
		// Assemble the gammu command line.
		$phone_command = 'echo \"'.$text.'\" | gnokii --phone ' . $phone->phone_index . ' --sendsms '.trim( $number->number );
		
		$command = $this->send_slave_command( $slave, $phone_command );
	
		$reply = new stdClass();
		$reply->code = $command->code;
		$reply->output = $command->output;
		$reply = serialize( $reply );
		$reply = base64_encode( $reply );
		echo $reply; 
		$this->shutdown();
	}
	
	/**
		@brief		Creates a reply container and sends it to the client.
		
		The options object:
		- string		@b client_public_key		The client's public key.
		- object		@b reply					The reply container / object.
		
		@param		object		$options		Options.
		@see		SMS_Master::send_container
	**/
	private function send_reply( $options )
	{
		$reply_container = new SMS_Master_Reply_Container();
		$reply_container->reply = $options->reply;
		$this->send_container( $reply_container, $options->client_public_key );
	}
	
	/**
		@brief		Send unsent orders.
	**/
	public function send_unsent()
	{
		$curls = array();
		// Find some messages
		$numbers = $this->numbers->list_ready_to_send();
		foreach ( $numbers as $number )
		{
			$number_id = $number->number_id;

			$phone = $this->phones->find_first_available();
			if ( $phone === false )
				continue;
			
			$curls[] = $this->prepare_send_text( $number, $phone );
		}
		
		if ( count( $curls ) > 0 )
		{
			$mh = curl_multi_init();
			foreach( $curls as $curl )
				curl_multi_add_handle($mh, $curl->curl);
			
			$mrc = curl_multi_exec($mh, $active);

			$start_time = microtime( true );
			
			do
			{
				usleep( 100000 );
				curl_multi_exec( $mh, $running );
			}
			while ( $running >  0);
			
			$finish_time = microtime( true );

			foreach( $curls as $index => $curl )
			{
				$reply = curl_multi_getcontent( $curl->curl );
				$reply = base64_decode( $reply );
				$reply = @unserialize( $reply );
				if ( $reply !== false )
					$this->handle_send_reply( $curl, $reply );
			}
		}
		else
		{
			// Nothing to send. Maybe do something else? Send some emails?
			$this->send_unsent_email_reports();
			
			// How about cleaning a little?
			if ( $this->config['clean']['enabled'] )
			{
				$interval = $this->config['cron']['clean']['interval'];
				if ( $this->ripe_for_cron( 'clean', $interval ) )
				{
					$this->cron_clean(); 
					$this->touch_cron( 'clean', $interval );
				}
			}
		}
	}
	
	/**
		@brief		Sends unsent email reports.
	**/
	public function send_unsent_email_reports()
	{
		$unsent_email_reports = $this->orders->list_unsent_email_reports();
		
		foreach( $unsent_email_reports as $unsent_email_report )
		{
			$order_id = $unsent_email_report->order_id;
			$mail_data = base64_decode( $unsent_email_report->email_report_text );
			$mail_data = unserialize( $mail_data );
			$result = $this->send_mail( $mail_data );
			if ( $result === true )
				$this->orders->mark_email_report_sent( $order_id );
		}
	}
	
	/**
		@brief		Exits the SMS master, calling all necessary cleanup function.s
	**/
	public function shutdown()
	{
		die();
	}
	
	/**
		@brief		Move the timestamp of a cron type into the future.
		
		@param		string		$cron_type		Cron type ('clean' or 'send' or whatever).
		@param		integer		$seconds		How many seconds to move into the future.
	**/
	public function touch_cron( $cron_type, $seconds = 30 )
	{
		$this->settings->update( 'cron_' . $cron_type, time() + $seconds );
	}	
}

