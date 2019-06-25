<?php
class TestCase extends PHPUnit\Framework\TestCase{

	protected $pathLibrary;
	protected $pathTests;
	protected $configFile;
	protected $configDefaultKeys	= array(
		'server.host',
		'server.port',
		'server.encryption',
		'mailbox.name',
		'mailbox.address',
		'auth.mode',
		'auth.username',
		'auth.password',
	);

	public function __construct(){
		parent::__construct();
		new UI_DevOutput;
		$this->pathLibrary		= dirname( __DIR__ ).'/';
		$this->pathTests		= __DIR__.'/';
		$this->configFile		= $this->pathLibrary.'Mail.ini';
	}

	protected function getReceiverConfig(){
		$config	= array();
		foreach( $this->configDefaultKeys as $key )
			$config[$key]	= NULL;
		if( !file_exists( $this->configFile ) )
			throw new RuntimeException( 'Config file "Mail.ini" is missing' );
		$ini	= parse_ini_file( $this->configFile, TRUE );
		if( !isset( $ini['phpunit.receiver'] ) )
			throw new RuntimeException( 'Config file "Mail.ini" is missing section "phpunit.receiver"' );
		foreach( $ini['phpunit.receiver'] as $key => $value )
			if( !preg_match( '/^\{\{.+\}\}$/', $value ) )
				$config[$key]	= $value;
		if( !$config['server.host'] )
			throw new RuntimeException( 'Config file "Mail.ini" is not having settings in section "phpunit.receiver"' );
		return new ADT_List_Dictionary( $config );
	}

	protected function getSenderConfig(){
		$config	= array();
		foreach( $this->configDefaultKeys as $key )
			$config[$key]	= NULL;
		if( !file_exists( $this->configFile ) )
			throw new RuntimeException( 'Config file "Mail.ini" is missing' );
		$ini	= parse_ini_file( $this->configFile, TRUE );
		if( !isset( $ini['phpunit.sender'] ) )
			throw new RuntimeException( 'Config file "Mail.ini" is missing section "phpunit.sender"' );
		foreach( $ini['phpunit.sender'] as $key => $value )
			if( !preg_match( '/^\{\{.+\}\}$/', $value ) )
				$config[$key]	= $value;
		if( !$config['server.host'] )
			throw new RuntimeException( 'Config file "Mail.ini" is not having settings in section "phpunit.sender"' );
		return new ADT_List_Dictionary( $config );
	}

	protected function requireReceiverConfig(){
		try{
			return $this->getReceiverConfig();
		}
		catch( Exception $e ){
			$this->markTestSkipped( 'Runtime incomplete: '.$e->getMessage() );
		}
	}

	protected function requireSenderConfig(){
		try{
			return $this->getSenderConfig();
		}
		catch( Exception $e ){
			$this->markTestSkipped( 'Runtime incomplete: '.$e->getMessage() );
		}
	}
}
