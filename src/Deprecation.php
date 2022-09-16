<?php
/*
*/
/**
 *	Indicator for deprecated methods.
 *	@category		Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/Common
 */
namespace CeusMedia\HydrogenFramework;

use CeusMedia\Common\Deprecation as CommonDeprecation;
use Exception;

/**
 *	Indicator for deprecated methods.
 *
 *	Example:
 *		use CeusMedia\HydrogenFramework\Deprecation;
 *		Deprecation::getInstance()
 *			->setErrorVersion( '0.9' )
 *			->setExceptionVersion( '0.9' )
 *			->message( 'Use method ... instead' );
 *
 *	@category		Library
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/Common
 */
class Deprecation extends CommonDeprecation
{
	/**
	 *	Contructor, needs to be called statically by getInstance.
	 *	Will detect library version.
	 *	Will set error version to curent library version by default.
	 *	Will not set an exception version.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __construct()
	{
		$iniFilePath		= dirname( __DIR__ ).'/hydrogen.ini';
		$iniFileData		= parse_ini_file( $iniFilePath, TRUE );
		$this->version		= $iniFileData['project']['version'];
		$this->phpVersion	= phpversion();
		$this->errorVersion	= $this->version;
	}

	/**
	 *	Creates a new deprection object.
	 *	@static
	 *	@access		public
	 *	@return		Deprecation
	 */
	static public function getInstance(): Deprecation
	{
		return new self();
	}

	/**
	 *	Show message as exception or deprecation error, depending on set versions and PHP version.
	 *	Will throw an exception if set exception version reached detected library version.
	 *	Will throw a deprecation error if set error version reached detected library version using PHP 5.3+.
	 *	Will throw a deprecation notice if set error version reached detected library version using PHP lower 5.3.
	 *	@access		public
	 *	@param		string		$message	Message to show
	 *	@return		void
	 *	@throws		Exception				if set exception version reached detected library version
	 *	@todo		set type hint after CeusMedia::Common updated
	 */
	public function message( $message )
	{
		$trace	= debug_backtrace();
		$caller = next( $trace );
		if( isset( $caller['file'] ) && isset( $caller['line'] ) )
			$message .= ', invoked in '.$caller['file'].' on line '.$caller['line'];
		if( $this->exceptionVersion )
			if( version_compare( $this->version, $this->exceptionVersion ) >= 0 )
				throw new Exception( 'Deprecated: '.$message );
		if( version_compare( $this->version, $this->errorVersion ) >= 0 ){
			self::notify( $message );
		}
	}

	/**
	 *	@todo		set type hint after CeusMedia::Common updated
	 */
	public static function notify( $message )
	{
		$message .= ', triggered';
		if( version_compare( phpversion(), "5.3.0" ) >= 0 )
			trigger_error( $message, E_USER_DEPRECATED );
		else
			trigger_error( 'Deprecated: '.$message, E_USER_NOTICE );
	}

	/**
	 *	Set library version to start showing deprecation error or notice.
	 *	Returns deprecation object for chainability.
	 *	@access		public
	 *	@param		string		$version	Library version to start showing deprecation error or notice
	 *	@return		self
	 *	@todo		set type hint after CeusMedia::Common updated
	 */
	public function setErrorVersion( $version ): self
	{
		$this->errorVersion		= $version;
		return $this;
	}

	/**
	 *	Set library version to start throwing deprecation exception.
	 *	Returns deprecation object for chainability.
	 *	@access		public
	 *	@param		string		$version	Library version to start throwing deprecation exception
	 *	@return		self
	 *	@todo		set type hint after CeusMedia::Common updated
	 */
	public function setExceptionVersion( $version ): self
	{
		$this->exceptionVersion		= $version;
		return $this;
	}

	/**
	 *	Set version of currently installed component.
	 *	By default, the "component" is the framework itself and the version will be detected.
	 *	On handling deprecations within modules, you can use this method to set the module version.
	 *	@access		public
	 *	@param		string		$version		Version of component to compare with
	 *	@return		self
	 */
	public function setVersion( string $version ): self
	{
		$this->version	= $version;
		return $this;
	}
}
