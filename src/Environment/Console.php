<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Console extends CMF_Hydrogen_Environment{

	/**	@var	CLI_ArgumentParser								$request	Console Request Object */
	protected $request;

	/** @var	CMF_Hydrogen_Environment_Console_Messenger		$messenger	Messenger Object */
	protected $messenger;

	/** @var	CMF_Hydrogen_Environment_Resource_Language		$language	Language Object */
	protected $language;

	/** @var	ADT_List_Dictionary								$session	Session Storage Object */
	protected $session;

	protected $pathConfig	= "";

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( $options = array(), $isFinal = TRUE ){
//		ob_start();
		try{
			parent::__construct( $options, FALSE );													//  construct parent but dont call __onInit
			$this->detectSelf();
			$this->initMessenger();																	//  setup user interface messenger
			$this->initRequest();																	//  setup console request handler
			$this->initSession();																	//  setup session storage
#			$this->initResponse();																	//  setup console response handler
#			$this->initRouter();																	//  setup request router
			$this->initLanguage();																	//  setup language support
#			$this->initPage();																		//
			$this->initAcl();

			if( !$isFinal )
				return;
			$this->modules->callHook( 'Env', 'constructEnd', $this );								//  call module hooks for end of env construction
			$this->__onInit();																		//  default callback for construction end

		}
		catch( Exception $e ){
			print( $e->getMessage() );
			die();
		}
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
		return $this->session;
	}


	//  --  PROTECTED  --  //

	protected function detectSelf(){
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

//	protected function initConfiguration(){
//		$this->config	= new ADT_List_Dictionary();
//	}

	protected function initLanguage(){
		$this->language		= new CMF_Hydrogen_Environment_Resource_Language( $this );
		$this->clock->profiler->tick( 'env: language' );
	}

	protected function initMessenger(){
		$this->messenger	= new CMF_Hydrogen_Environment_Console_Messenger( $this );
	}

	protected function initRequest(){
		$this->request	= new CLI_ArgumentParser();
		$this->request->parseArguments();
	}

	/**
	 * Setup a "session", which is persistent storage for this run only.
	 */
	protected function initSession(){
		$this->session	= new ADT_List_Dictionary();
		return $this;
	}
}
class CMF_Hydrogen_Environment_Console_Messenger extends CMF_Hydrogen_Environment_Resource_Messenger{

	protected function noteMessage( $type, $message ){
		CLI::out( $message );
		flush();
	}
}
