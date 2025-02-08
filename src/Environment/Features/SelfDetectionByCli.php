<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\HydrogenFramework\Environment\Features\ConfigByIni as ConfigByIniFeature;
use RuntimeException;

trait SelfDetectionByCli
{
	use ConfigByIniFeature;

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
}
