<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Exception as EnvironmentException;
use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;

trait SelfDetectionByHttp
{
	use RuntimeFeature;

	/**	@var	string					$host		Detected HTTP host */
	public string $host;

	/**	@var	string					$port		Detected HTTP port */
	public string $port;

	/**	@var	string|NULL				$path		Detected HTTP path */
	public ?string $path				= NULL;

	/**	@var	string					$root		Detected  */
	public string $root;

	/**	@var	string					$scheme		Detected  */
	public string $scheme;

	/**	@var	string					$uri		Detected  */
	public string $uri;

	/**	@var	string					$url		Detected application base URL */
	public string $url;

	/**
	 *	Detects basic environmental web and local information.
	 *	Notes global scheme, host, relative application path and absolute application URL.
	 *	Notes local document root path, relative application path and absolute application URI.
	 *	@access		protected
	 *	@param		boolean		$strict			Flag: strict mode: throw exceptions
	 *	@return		void
	 *	@throws		EnvironmentException	if strict mode and application has been executed outside a valid web server environment or no HTTP host has been provided by web server
	 *	@throws		EnvironmentException	if strict mode and no document root path has been provided by web server
	 *	@throws		EnvironmentException	if strict mode and no script file path has been provided by web server
	 */
	protected function detectSelf( bool $strict = TRUE ): void
	{
		if( $strict ){
			if( !getEnv( 'HTTP_HOST' ) ){														//  application has been executed outside a valid web server environment or no HTTP host has been provided by web server
				throw new EnvironmentException(
					'This application needs to be executed within by a web server'
				);
			}
			if( !getEnv( 'DOCUMENT_ROOT' ) ){													//  no document root path has been provided by web server
				throw new EnvironmentException(
					'Your web server needs to provide a document root path'
				);
			}
			if( !getEnv( 'SCRIPT_NAME' ) ){													//  no script file path has been provided by web server
				throw new EnvironmentException(
					'Your web server needs to provide the running scripts file path'
				);
			}
		}

		$this->scheme	= getEnv( "HTTPS" ) ? 'https' : 'http';								//  note used URL scheme
		$defaultPort	= $this->scheme === 'https' ? 443 : 80;										//  default port depends on HTTP scheme
		$serverPort		= (int) getEnv( 'SERVER_PORT' );
		$serverHost		= (string) getEnv( 'HTTP_HOST' );
		$this->host		= (string) preg_replace( "/:\d{2,5}$/", '', $serverHost );	//  note requested HTTP host name without port
		$this->port		= $serverPort === $defaultPort ? '' : (string) $serverPort;					//  note requested HTTP port
		$hostWithPort	= $this->host.( $this->port ? ':'.$this->port : '' );						//  append port if different from default port
		$this->root		= (string) getEnv( 'DOCUMENT_ROOT' );									//  note document root of web server or virtual host
		$path			= dirname( (string) getEnv( 'SCRIPT_NAME' ) );
		if( $this->options['pathApp'] ?? '' )
			$path		= $this->options['pathApp'];
		$this->path		= preg_replace( "@^/$@", "", $path )."/";					//  note requested working path
		$this->url		= $this->scheme.'://'.$hostWithPort.$this->path;							//  note calculated base application URI
		$this->uri		= $this->root.$this->path;													//  note calculated absolute base application path
		if( '' !== ( $this->options['uri'] ?? '' ) )
			$this->uri		= $this->options['uri'];													//  note calculated absolute base application path
		$this->runtime->reach( 'env: self detection' );
	}
}
