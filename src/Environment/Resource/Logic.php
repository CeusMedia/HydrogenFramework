<?php
/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\HydrogenFramework\Environment;
use InvalidArgumentException;

/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2021 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Logic
{
	const OS_UNKNOWN			= 0;
	const OS_LINUX				= 1;
	const OS_WINDOWS			= 2;

	/**	@var	Environment			$env	Environment object */
	protected $env;

	protected $config;

	protected $os				= self::OS_UNKNOWN;

	protected $timePrefixes		= array(
		'u'		=> 1,
		'm'		=> 1000,
		''		=> 1000000
	);

	public $fileNameLogDev		= 'logs/dev.log';


	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env	Environment
	 *	@return		void
	 */
	public function  __construct( Environment $env )
	{
		$this->env		= $env;
		$this->config	= $env->getConfig();
		$this->os		= self::OS_LINUX;															//  set OS to Linux by default
		$this->os		= preg_match( '/win/i', PHP_OS ) ? self::OS_WINDOWS : $this->os;			//  detect Windows and set OS

		$arguments		= array_slice( func_get_args(), 1 );										//  collect additional arguments for extended logic classes
//		Alg_Object_MethodFactory::callObjectMethod( $this, '__onInit', $arguments, TRUE, TRUE );	//  invoke possibly extended init method
		$this->__onInit();																			//  invoke possibly extended init method
	}

	//  --  PROTECTED  --  //

	protected function __onInit(){}

	protected function getArrayFromRequestKey( string $key ): array
	{
		$request	= $this->env->getRequest();														//  shortcut request object
		$array		= array();
		if( is_array( $request->get( $key ) ) )
			foreach( $request->get( $key ) as $key => $value )
				$array[$key]	= $value;
		return $array;
	}

	/**
	 *	Better implementation of getConfiguredSleepTimeFor.
	 */
	protected function getConfiguredMicroTimeFor( string $configKey ): int
	{
		$parts	= explode( '.', $configKey );
		$last	= array_pop( $parts );
		$path	= implode( '.', $parts );
		foreach( $this->timePrefixes as $prefix => $factor )
			if( $this->config->has( $path.'.'.$prefix.$last ) )
				return $this->config->get( $path.'.'.$prefix.$last ) * $factor;
		throw new InvalidArgumentException( 'No valid key set' );
	}

	/**
	 *	Older implementation of getConfiguredSleepTimeFor.
	 */
	protected function getConfiguredSleepTimeFor( string $configKey ): int
	{
		if( $this->config->has( $configKey.'.usleep' ) )
			return $this->config->get( $configKey.'.usleep' ) * 1;
		if( $this->config->has( $configKey.'.msleep' ) )
			return $this->config->get( $configKey.'.msleep' ) * 1000;
		if( $this->config->has( $configKey.'.sleep' ) )
			return $this->config->get( $configKey.'.sleep' ) * 1000000;
		throw new InvalidArgumentException( 'No valid key set' );
	}

	protected function logDev( string $message )
	{
		error_log( $message."\n", 3, $this->fileNameLogDev );
	}

	protected function microsleep( int $microseconds )
	{
		$microseconds	= abs( $microseconds );
		usleep( $microseconds );
		$this->env->getRuntime()->usleep( $microseconds );											//  inform performance clock about sleep time
	}
}
