<?php
namespace CeusMedia\HydrogenFramework\Run;

use CeusMedia\Common\Loader;
use CeusMedia\Common\UI\HTML\Exception\Page as ExceptionPage;
use CeusMedia\HydrogenFramework\Application\Web\Site as WebApp;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use Exception;
use ErrorException;

class Web
{
	public $errorReporting;

	public $displayErrors;

	public $catchErrors				= FALSE;

	public $configFile;

	public $classFileExtension		= 'php5';

	public $classRouter;

	public $defaultTimezone;

	public $paths					= [];

	public $pathVendor				= 'vendor/';

	protected $app;

	public function go()
	{
		try{
			$this->setupErrorHandling();
			$this->setupEnvironment();

			$app	= new WebApp();												//  create default web site application instance
			$app->run();														//  and run it
		}
		catch( Exception $t ){													//  an uncatched exception happend
			ExceptionPage::display( $t );										//  display report page with call stack
		}
	}

	public function handleErrorAsException( $errno, $errstr, $errfile, $errline, ?array $errcontext )
	{
		if( error_reporting() === 0 )											// error was suppressed with the @-operator
			return FALSE;
		throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
	}

	// --  PRIVATE  --  //
	private function setupEnvironment()
	{
	 	$pathVendor	= rtrim( $this->paths['vendor'] ?? 'vendor', '/' ).'/';
		if( !file_exists( $pathVendor ) )
		 	die( 'Please install first, using composer!' );
		require_once $pathVendor.'autoload.php';
		require_once $pathVendor.'ceus-media/common/src/compat8.php';

		if( NULL !== $this->defaultTimezone )
			date_default_timezone_set( $this->defaultTimezone );				//  set default time zone

		if( NULL !== $this->configFile )										//  an alternative config file has been set
			WebEnvironment::$configFile   = $this->configFile;							//  set alternative config file in environment

		if( NULL !== $this->classRouter )										//  an alternative router class has been set
			WebEnvironment::$classRouter  = $this->classRouter;							//  set alternative router class in environment

		if( NULL !== $this->paths && 0 !== count( $this->paths ) )
			WebEnvironment::$defaultPaths	= $this->paths + WebEnvironment::$defaultPaths;

		$classExt	= $this->classFileExtension;
		$classPath	= WebEnvironment::$defaultPaths['classes'];
		Loader::registerNew( $classExt, NULL, $classPath );							//  register autoloader for project classes
	}

	private function setupErrorHandling()
	{
		if( NULL !== $this->errorReporting )
			error_reporting( $this->errorReporting );
		if( NULL !== $this->displayErrors )
			ini_set( 'display_errors', $this->displayErrors );
		if( NULL !== $this->catchErrors && $this->catchErrors )
			set_error_handler( array( $this, 'handleErrorAsException' ) );
	}
}
