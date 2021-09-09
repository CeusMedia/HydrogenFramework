<?php
namespace CeusMedia\HydrogenFramework\Environment;

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Console\Messenger as Messenger;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as Language;

use ADT_List_Dictionary as Dictionary;
use CLI_ArgumentParser as ArgumentParser;

use Exception;
use RuntimeException;

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			extend from (namespaced) Environment after all modules are migrated to 0.9
 */
class Console extends \CMF_Hydrogen_Environment
{
	/**	@var	ArgumentParser			$request	Console Request Object */
	protected $request;

	/** @var	Messenger				$messenger	Messenger Object */
	protected $messenger;

	/** @var	Language				$language	Language Object */
	protected $language;

	/** @var	Dictionary				$session	Session Storage Object */
	protected $session;

	protected $pathConfig	= '';

	/**	@var	string					$host		Detected HTTP host */
	public $host;

	/**	@var	int						$port		Detected HTTP port */
	public $port;

	/**	@var	string					$scheme		Detected  */
	public $scheme;

	/**	@var	string					$path		Detected HTTP path */
	public $path;

	/**	@var	string					$url		Detected application base URL */
	public $url;

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( array $options = array(), bool $isFinal = TRUE )
	{
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

	public function getLanguage(): Language
	{
		return $this->language;
	}

	public function getMessenger(): Messenger
	{
		return $this->messenger;
	}

	public function getRequest(): ArgumentParser
	{
		return $this->request;
	}

	public function getSession(): Dictionary
	{
		return $this->session;
	}

	//  --  PROTECTED  --  //

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

//	protected function initConfiguration(){
//		$this->config	= new Dictionary();
//	}

	protected function initLanguage()
	{
		$this->language		= new Language( $this );
		$this->runtime->reach( 'env: language' );
	}

	protected function initMessenger()
	{
		$this->messenger	= new Messenger( $this );
	}

	protected function initRequest()
	{
		$this->request	= new ArgumentParser();
		$this->request->parseArguments();
	}

	/**
	 * Setup a "session", which is persistent storage for this run only.
	 */
	protected function initSession()
	{
		$this->session	= new Dictionary();
		return $this;
	}
}
