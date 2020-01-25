<?php
/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
/**
 *	Basic logic class. Can be extended and uses as business logic layer class.
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class CMF_Hydrogen_Environment_Resource_Logic
{
	const OS_LINUX				= 1;
	const OS_WINDOWS			= 2;
	protected $os				= 0;
	protected $timePrefixes		= array(
		'u'		=> 1,
		'm'		=> 1000,
		''		=> 1000000
	);
	public $fileNameLogDev		= 'logs/dev.log';

	/**	@var	CMF_Hydrogen_Environment			$env	Environment object */
	protected $env;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment		$env	Environment
	 *	@return		void
	 */
	public function  __construct( CMF_Hydrogen_Environment $env ) {
		$this->env		= $env;
		$this->config	= $env->getConfig();
		$this->os		= self::OS_LINUX;															//  set OS to Linux by default
		$this->os		= preg_match( '/win/i', PHP_OS ) ? self::OS_WINDOWS : $this->os;			//  detect Windows and set OS

		$arguments		= array_slice( func_get_args(), 1 );										//  collect additional arguments for extended logic classes
		Alg_Object_MethodFactory::callObjectMethod( $this, '__onInit', $arguments, TRUE, TRUE );	//  invoke possibly extended init method
	}

	protected function __onInit(){}

	public function getArrayFromRequestKey( $key ){
		$request	= $this->env->getRequest();														//  shortcut request object
		$array		= array();
		if( is_array( $request->get( $key ) ) )
				foreach( $request->get( $key ) as $key => $value )
					$array[$key]	= $value;
		return $array;
	}

	public function getConfiguredMicroTimeFor( $configKey ) {
		$parts	= explode( '.', $configKey );
		$last	= array_pop( $parts );
		$path	= implode( '.', $parts );
		foreach( $this->timePrefixes as $prefix => $factor )
			if( $this->config->has( $path.'.'.$prefix.$last ) )
				return $this->config->get( $path.'.'.$prefix.$last ) * $factor;
		throw new InvalidArgumentException( 'No valid key set' );
	}

	public function getConfiguredSleepTimeFor( $configKey ) {
		if( $this->config->has( $configKey.'.usleep' ) )
			return $this->config->get( $configKey.'.usleep' ) * 1;
		if( $this->config->has( $configKey.'.msleep' ) )
			return $this->config->get( $configKey.'.msleep' ) * 1000;
		if( $this->config->has( $configKey.'.sleep' ) )
			return $this->config->get( $configKey.'.sleep' ) * 1000000;
	}

	public function logDev( $message ) {
		error_log( $message."\n", 3, $this->fileNameLogDev );
	}

	public function microsleep( $microseconds ) {
		$microseconds	= abs( $microseconds );
		$clock			= $this->env->getClock();
		switch( $this->os ) {
			case self::OS_WINDOWS:
				$seconds	= round( $microseconds / 1000000 );
				$seconds	= max( 1, abs( $seconds ) );
				sleep( $seconds );
				$clock->sleep( $seconds );											//  inform performance clock about sleep time
				break;
			case self::OS_LINUX:
			default:
				usleep( $microseconds );
				$clock->usleep( $microseconds );									//  inform performance clock about sleep time
				break;
		}
	}
}
