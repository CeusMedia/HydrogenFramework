<?php
/**
 *	Resource to communicate with chat server.
 *	@category		cmApps
 *	@package		Chat.Client
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id$
 */
/**
 *	Resource to communicate with chat server.
 *	@category		cmApps
 *	@package		Chat.Client
 *	@uses			Net_Reader
 *	@uses			Net_CURL
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id$
 */
class CMF_Hydrogen_Environment_Resource_Server_Json {

	protected $env;
	protected $serverUsername;
	protected $serverPassword;
	protected $serverUri;
	protected $curlOptions	= array(
		'ALL'	=> array(),
		'GET'	=> array(),
		'POST'	=> array()
	);

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env	Environment
	 *	@return		void
	 */
	public function  __construct( CMF_Hydrogen_Environment_Abstract $env ) {
		$this->env			= $env;
		$this->serverUri	= $env->getConfig()->get( 'server.uri' );

		$this->serverUsername		= $env->getConfig()->get( 'server.username' );
		$this->serverPassword		= $env->getConfig()->get( 'server.password' );
		$this->setCurlOption( CURLOPT_USERPWD, $this->serverUsername.':'.$this->serverPassword );

		$this->clientIp		= getEnv( 'REMOTE_ADDR' );
		if( empty( $this->serverUri ) )
			throw new RuntimeException( 'No server URI set in config (server.uri)' );
	}

	protected function buildServerGetUrl( $controller, $action = NULL, $arguments = array(), $parameters = array() ) {
		$url	= $this->buildServerPostUrl( $controller, $action, $arguments );
		if( is_null( $parameters ) )
			$parameters	= array();
		if( !is_array( $parameters ) )
			throw new InvalidArgumentException( 'Parameters must be an array or NULL' );
		if( $this->env->getSession()->get( 'token' ) )
		{
			$parameters['token']	= $this->env->getSession()->get( 'token' );
			if( $this->env->getSession()->get( 'ip' ) )
				$parameters['ip']	= $this->env->getSession()->get( 'ip' );
		}
		if( $parameters )
			$url	.= '?'.http_build_query( $parameters, NULL, '&' );
		return $url;
	}

	/**
	 *	Builds URL string from controller, action and arguments.
	 *	@access		protected
	 *	@param		string		$controller		Controller name
	 *	@param		string		$action			Action name
	 *	@param		array		$arguments		List of URI arguments
	 *	@return		strring		URL on server
	 */
	protected function buildServerPostUrl( $controller, $action = NULL, $arguments = array() ) {
		if( $arguments && empty( $action ) )
			$action		= 'index';
		if( $action && !$controller )
			$controller	= 'index';
		if( is_string( $controller ) && !empty( $controller ) )
			$controller	= preg_replace( '/([^\/]+)\/?/', '\\1', $controller ).'/';
		if( is_string( $action ) && !empty( $action ) )
			$action		= preg_replace( '/([^\/]+)\/?/', '\\1', $action ).'/';
		if( !is_array( $arguments ) )
			$arguments	= $arguments ? array( $arguments ) : array();
		foreach( $arguments as $nr => $argument )
			$arguments[$nr]	= urlencode( $argument );
		$arguments	= implode( '/', $arguments );
		$url		= $this->serverUri.$controller.$action.$arguments;
		return $url;
	}

	/**
	 *	Returns set CURL option by its key.
	 *	@access		public
	 *	@param		string		$key		CURL option key
	 *	@param		string		$method		Request method (ALL|GET|POST)
	 *	@param		bool		$strict		Flag: throw exception or return NULL
	 *	@return		mixed		Set CURL option value or NULL (if not strict)
	 *	@throws		InvalidArgumentException if method is invaid
	 *	@throws		InvalidArgumentException if key is not existing and strict mode
	 */
	public function getCurlOption( $key, $method = 'ALL', $strict = FALSE ) {
		$method	= strtoupper( $method );
		if( !array_key_exists( $method, $this->curlOptions ) )
			throw new InvalidArgumentException( 'Invalid method: '.$method );
		if( isset( $this->curlOptions[$method][$key] ) )
			return $this->curlOptions[$method][$key];
		if( $strict )
			throw new InvalidArgumentException( 'Invalid option key: '.$key );
		return NULL;
	}

	public function getCurlOptions( $method = 'ALL' ) {
		$method	= strtoupper( $method );
		if( !array_key_exists( $method, $this->curlOptions ) )
			throw new InvalidArgumentException( 'Invalid method: '.$method );
		return $this->curlOptions[$method];
	}

	public function getData( $controller, $action = NULL, $arguments = array(), $parameters = array(), $curlOptions = array() ) {
		$url	= $this->buildServerGetUrl( $controller, $action, $arguments, $parameters = array() );
		return	$this->getDataFromUrl( $url, $curlOptions );
	}

	public function getDataFromUri( $uri, $curlOptions = array() ) {
		return $this->getDataFromUrl( $this->serverUri.$uri, $curlOptions );
	}

	public function getDataFromUrl( $url, $curlOptions = array() ) {
		$options	= $this->curlOptions['ALL'] + $this->curlOptions['GET'] + $curlOptions;
		$username	= $this->env->getConfig()->get( 'server.username' );
		$password	= $this->env->getConfig()->get( 'server.password' );
		$reader		= new Net_Reader( $url );
#		if( $this->serverUsername && $this->serverPassword )
#			$reader->setBasicAuth( $this->serverUsername, $this->serverPassword );

		$json		= $reader->read( $options );
		$statusCode	= $reader->getStatus( Net_CURL::STATUS_HTTP_CODE );
		error_log( time()." GET (".$statusCode."): ".$json."\n", 3, "logs/server.response.log" );
		$response	= $this->handleResponse( $json, $url, $statusCode );
		return $response->data;
	}

	protected function handleResponse( $json, $url, $statusCode ) {

		if( $statusCode != 200 && $statusCode != 500 )
			throw new RuntimeException( 'Resource '.$url.' has HTTP code '.$statusCode );
		$response	= json_decode( $json );
		if( !is_object( $response ) )
			throw new RuntimeException( 'Resource '.$url.' is no JSON object' );
		if( isset( $response->exception ) && $response->exception ) {
			if( !preg_match( '/: */', $response->exception ) )
				throw new RuntimeException( $response->exception );
			list( $exception, $message ) = preg_split( '/: */', $response->exception, 2 );
			throw Alg_Object_Factory::createObject( $exception, array( $message ) );
		}
		return $response;
	}

	public function postData( $controller, $action = NULL, $arguments = NULL, $data = array(), $curlOptions = array() )
	{
		$url	= $this->buildServerPostUrl( $controller, $action, $arguments );
		return $this->postDataToUrl( $url, $data, $curlOptions );
	}

	public function postDataToUri( $uri, $data = array(), $curlOptions = array() )
	{
		return $this->postDataToUrl( $this->serverUri.$uri, $data, $curlOptions );
	}

	public function postDataToUrl( $url, $data = array(), $curlOptions = array() )
	{
		if( $data instanceof ADT_List_Dictionary )
			$data	= $data->getAll();
		if( $this->env->getSession()->get( 'token' ) )
		{
			$data['token']	= $this->env->getSession()->get( 'token' );
			if( $this->env->getSession()->get( 'ip' ) )
				$data['ip']	= $this->env->getSession()->get( 'ip' );
		}
		foreach( $data as $key => $value )															//  cURL hack (file upload identifier)
			if( is_string( $value ) && substr( $value, 0, 1 ) == "@" )								//  leading @ in field values
				$data[$key]	= "\\".$value;															//  need to be escaped
		$curl	= new Net_CURL( $url );

		$options	= $this->curlOptions['ALL'] + $this->curlOptions['POST'] + $curlOptions;
		foreach( $options as $key => $value )
			$curl->setOption( $key, $value );
		$curl->setOption( CURLOPT_POST, TRUE );
		$curl->setOption( CURLOPT_POSTFIELDS, http_build_query( $data ) );
		foreach( $options as $key => $value )
			$curl->setOption( $key, $value );
		$json		= $curl->exec();
		$statusCode	= $curl->getStatus( Net_CURL::STATUS_HTTP_CODE );
		error_log( time()." POST (".$statusCode."): ".$json."\n", 3, "logs/server.response.log" );
		$response	= $this->handleResponse( $json, $url, $statusCode );
		return $response->data;
	}

	public function setCurlOption( $key, $value, $method = 'ALL' ) {
		$method	= strtoupper( $method );
		if( !array_key_exists( $method, $this->curlOptions ) )
			throw new InvalidArgumentException( 'Invalid method: '.$method );
		$this->curlOptions[$method][$key]	= $value;
	}

	public function setCurlOptions( $curlOptions, $method = 'ALL' ) {
		$method	= strtoupper( $method );
		if( !array_key_exists( $method, $this->curlOptions ) )
			throw new InvalidArgumentException( 'Invalid method: '.$method );
		$this->curlOptions[$method]	= $curlOptions;
	}
}
?>