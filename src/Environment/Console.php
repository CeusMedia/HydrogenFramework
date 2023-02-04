<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace CeusMedia\HydrogenFramework\Environment;

use CeusMedia\Common\ADT\Collection\Dictionary as Dictionary;
use CeusMedia\Common\CLI\ArgumentParser as ArgumentParser;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Console\Messenger as ConsoleMessenger;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as LanguageResource;
use Exception;
use RuntimeException;

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2023 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2023 Christian Würker (ceusmedia.de)
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			extend from (namespaced) Environment after all modules are migrated to 0.9
 */
class Console extends Environment
{
	/**	@var	string					$host		Detected HTTP host */
	public string $host;

	/**	@var	int						$port		Detected HTTP port */
	public int $port;

	/**	@var	string					$scheme		Detected  */
	public string $scheme;

	/**	@var	string|NULL				$path		Detected HTTP path */
	public ?string $path				= NULL;

	/**	@var	string					$url		Detected application base URL */
	public string $url					= '';

	/**	@var	ArgumentParser			$request	Console Request Object */
	protected ArgumentParser $request;

	/** @var	Dictionary				$session	Session Storage Object */
	protected Dictionary $session;

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( array $options = [], bool $isFinal = TRUE )
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

	public function getLanguage(): LanguageResource
	{
		return $this->language;
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

	/**
	 *	@return		void
	 */
	protected function detectSelf(): void
	{
		$this->url = $this->config->get( 'app.url', '' );											//  get application URL from config
		if( !$this->url )																			//  application URL not set
			$this->url = $this->config->get( 'app.base.url', '' );									//  get application base URL from config
		if( in_array( $this->url,[NULL, FALSE, ''] ) )												//  application base URL not set
			throw new RuntimeException( 'Please define app.base.url in config.ini, first!' );		//  quit with exception

		$this->scheme	= (string) parse_url( $this->url, PHP_URL_SCHEME );				//  note used URL scheme
		$this->host		= (string) parse_url( $this->url, PHP_URL_HOST );					//  note requested HTTP host name
		$this->port		= (int) parse_url( $this->url, PHP_URL_PORT );					//  note requested HTTP port
		$this->path		= $this->config->get( 'app.base.path' );								//  note absolute working path
	}

//	protected function initConfiguration(){
//		$this->config	= new Dictionary();
//	}

	protected function initLanguage(): self
	{
		$this->language		= new LanguageResource( $this );
		$this->runtime->reach( 'env: language' );
		return $this;
	}

	protected function initMessenger(): self
	{
		$this->messenger	= new ConsoleMessenger( $this );
		return $this;
	}

	protected function initRequest(): self
	{
		$this->request	= new ArgumentParser();
		$this->request->parseArguments();
		return $this;
	}

	/**
	 * Setup a "session", which is persistent storage for this run only.
	 */
	protected function initSession(): self
	{
		$this->session	= new Dictionary();
		return $this;
	}
}
