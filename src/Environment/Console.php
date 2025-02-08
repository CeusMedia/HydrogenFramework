<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace CeusMedia\HydrogenFramework\Environment;

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Features\MessengerByCli as MessengerByCliFeature;
use CeusMedia\HydrogenFramework\Environment\Features\RequestByCli as RequestByCliFeature;
use CeusMedia\HydrogenFramework\Environment\Features\SelfDetectionByCli as SelfDetectionByCliFeature;
use CeusMedia\HydrogenFramework\Environment\Features\SessionByDictionary as SessionByDictionaryFeature;
use Exception;
use ReflectionException;

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@todo			extend from (namespaced) Environment after all modules are migrated to 0.9
 */
class Console extends Environment
{
	use MessengerByCliFeature;
	use RequestByCliFeature;
	use SelfDetectionByCliFeature;
	use SessionByDictionaryFeature;

	/**
	 *	Constructor, sets up Resource Environment.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct( array $options = [], bool $isFinal = TRUE )
	{
//		ob_start();
		try{
//			parent::__construct( $options, FALSE );													//  construct parent but dont call __onInit
			$this->runBaseEnvConstruction( $options, FALSE );										//  construct parent but dont call __onInit
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

	/**
	 *	Tries to unbind registered environment handler objects.
	 *	@access		public
	 *	@param		array		$additionalResources	List of resource member names to be unbound, too
	 *	@param		boolean		$keepAppAlive			Flag: do not end execution right now if turned on
	 *	@return		void
	 */
	public function close( array $additionalResources = [], bool $keepAppAlive = FALSE ): void
	{
		parent::close( array_merge( [																//  delegate closing with these resources, too
			'request',																				//  CLI request handler
//			'response',																				//  CLI response handler
			'session',																				//  CLI session handler
			'messenger',																			//  application message handler
			'language',																				//  language handler
		], array_values( $additionalResources ) ), $keepAppAlive );									//  add additional resources and carry exit flag
	}

	//  --  PROTECTED  --  //


	/**
	 *	@param		array		$options 			@todo: doc
	 *	@param		boolean		$isFinal			Flag: there is no extending environment class, default: TRUE
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function runBaseEnvConstruction( array $options = [], bool $isFinal = TRUE ): void
	{
//		$this->modules->callHook( 'Env', 'constructStart', $this );									//  call module hooks for end of env construction
		/** @noinspection DuplicatedCode */
		$this->detectFrameworkVersion();

		$this->options		= $options;																//  store given environment options
		$this->path			= rtrim( $options['pathApp'] ?? getCwd(), '/' ) . '/';	//  detect application path
		$this->uri			= rtrim( $options['uri'] ?? getCwd(), '/' ) . '/';															//  detect application base URI

		$this->setTimeZone();

		$this->initSession();
		$this->initRuntime();																		//  setup runtime clock
		$this->initConfiguration();																	//  setup configuration
		$this->detectMode();
		$this->initLog();																			//  setup logger
		$this->initPhp();																			//  setup PHP environment
		$this->initCaptain();																		//  setup captain
		$this->initLogic();																			//  setup logic pool
		$this->initModules();																		//  setup module support
		$this->initDatabase();																		//  setup database connection
		$this->initCache();																			//  setup cache support
//		$this->initLanguage();

		if( !$isFinal )
			return;
		$this->captain->callHook( 'Env', 'constructEnd', $this );									//  call module hooks for end of env construction
		$this->__onInit();																			//  default callback for construction end
	}
}
