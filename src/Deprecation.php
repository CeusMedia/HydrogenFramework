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
	 *	Constructor, needs to be called statically by getInstance.
	 *	Will detect library version.
	 *	Will set error version to current library version by default.
	 *	Will not set an exception version.
	 *	@access			protected
	 *	@return			void
	 *	@noinspection	PhpMissingParentConstructorInspection
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
	 *	Creates a new deprecation object.
	 *	@static
	 *	@access		public
	 *	@return		self
	 */
	static public function getInstance(): self
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
	 *	@return		self
	 *	@throws		Exception				if set exception version reached detected library version
	 */
	public function message( string $message ): self
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
		return $this;
	}

	/**
	 *	@return		void
	 */
	public static function notify( string $message )
	{
		trigger_error( $message.', triggered', E_USER_DEPRECATED );
	}

	/**
	 *	Set library version to start showing deprecation error or notice.
	 *	Returns deprecation object for method chaining.
	 *	@access		public
	 *	@param		string		$version	Library version to start showing deprecation error or notice
	 *	@return		self
	 *	@todo		set type hint after CeusMedia::Common updated
	 */
	public function setErrorVersion( string $version ): self
	{
		$this->errorVersion		= $version;
		return $this;
	}

	/**
	 *	Set library version to start throwing deprecation exception.
	 *	Returns deprecation object for method chaining.
	 *	@access		public
	 *	@param		string		$version	Library version to start throwing deprecation exception
	 *	@return		self
	 *	@todo		set type hint after CeusMedia::Common updated
	 */
	public function setExceptionVersion( string $version ): self
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
