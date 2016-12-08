<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2016 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Console extends CMF_Hydrogen_Environment_Abstract{

	/**	@var	Console_RequestReceiver		$request	Console Request Object */
	protected $request;
	protected $messenger;
	protected $pathConfig	= "";

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $options = array(), $isFinal = TRUE )
	{
//		ob_start();
		try
		{
			parent::__construct( $options, FALSE );													//  construct parent but dont call __onInit
			$this->detectSelf();
			$this->initMessenger();																	//  setup user interface messenger
			$this->initRequest();																	//  setup HTTP request handler
#			$this->initResponse();																	//  setup HTTP response handler
#			$this->initRouter();																	//  setup request router
			$this->initLanguage();																	//  setup language support
#			$this->initPage();																		//
			$this->initAcl();

			if( !$isFinal )
				return;
			$this->modules->callHook( 'Env', 'constructEnd', $this );								//  call module hooks for end of env construction
			$this->__onInit();																		//  default callback for construction end

		}
		catch( Exception $e )
		{
			print( $e->getMessage() );
			die();
		}
	}

	protected function detectSelf()
	{
		$this->url = $this->config->get( 'app.url' );												//  get application URL from config
		if( !$this->url )																			//  application URL not set
			$this->url = $this->config->get( 'app.base.url' );										//  get application base URL from config
		if( !$this->url )																			//  application base URL not set
			throw new RuntimeException( 'Please define app.base.url in config.ini, first!' );		//  quit with exception

		$this->scheme	= parse_url( $this->url, PHP_URL_SCHEME );									//  note used URL scheme
		$this->host		= parse_url( $this->url, PHP_URL_HOST );									//  note requested HTTP host name
		$this->port		= parse_url( $this->url, PHP_URL_PORT );									//  note requested HTTP port
		$this->path		= $this->config->get( 'app.base.path' );									//  note absolute working path
	}

	public function getLanguage(){
		return $this->language;
	}

	public function getMessenger(){
		return $this->messenger;
	}

	public function getRequest(){
		return $this->request;
	}

	public function getSession(){
		return new ADT_List_Dictionary();
	}

//	public function initConfiguration(){
//		$this->config	= new ADT_List_Dictionary();
//	}

	public function initLanguage(){
		$this->language		= new CMF_Hydrogen_Environment_Resource_Language( $this );
		$this->clock->profiler->tick( 'env: language' );
	}

	public function initMessenger(){
		$this->messenger	= new Messenger( $this );
	}

	public function initRequest(){
		$this->request	= new CLI_ArgumentParser();
		$this->request->parseArguments();
	}
}
class Messenger extends CMF_Hydrogen_Environment_Resource_Messenger{
	protected function noteMessage($type, $message) {
		remark( $message );
		flush();
	}
}
?>
