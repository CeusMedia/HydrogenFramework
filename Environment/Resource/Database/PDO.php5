<?php
class CMF_Hydrogen_Environment_Resource_Database_PDO extends Database_PDO_Connection
{
	public function __construct( CMF_Hydrogen_Environment_Abstract $env )
	{
		$this->env	= $env;
		$this->setUp();
	}

	protected function setUp()
	{
		$config			= $this->env->getConfig();
		$driver			= $config->get( 'database.driver' );
		$host			= $config->get( 'database.host' );
		$port			= $config->get( 'database.port' );
		$name			= $config->get( 'database.name' );
		$username		= $config->get( 'database.username' );
		$password		= $config->get( 'database.password' );
		$prefix			= $config->get( 'database.prefix' );
		$logfile		= $config->get( 'database.log' );
#		$lazy			= $config->get( 'database.lazy' );
		$charset		= $config->get( 'database.charset' );
		$logStatements	= $config->get( 'database.log.statements' );
		$logErrors		= $config->get( 'database.log.errors' );

		if( empty( $driver ) )
			throw new RuntimeException( 'Database driver must be set in config:database.driver' );

		$dsn		= new Database_PDO_DataSourceName( $driver, $name );
		if( $host )
			$dsn->setHost( $host );
		if( $port )
			$dsn->setPort( $port );
		if( $username )
			$dsn->setUsername( $username );
		if( $password )
			$dsn->setPassword( $password );

		$driverOptions	= array();																	// to be implemented

		parent::__construct( $dsn, $username, $password, $driverOptions );
		if( $logStatements )
			$this->setStatementLogFile( $logStatements );
		if( $logErrors )
			$this->setErrorLogFile( $logErrors );

#		if( $charset )
#			$this->exec( "SET NAMES '".$charset."';" );
	}

	public function tearDown(){}
}
?>
