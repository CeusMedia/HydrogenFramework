<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\Net\HTTP\Cookie as HttpCookie;
use CeusMedia\HydrogenFramework\Environment\Features\Runtime as RuntimeFeature;
use RuntimeException;

trait Cookie
{
	use RuntimeFeature;

	/**	@var	HttpCookie				$cookie		Cookie Object */
	protected HttpCookie $cookie;

	/**
	 *	Returns Cookie Object.
	 *	@access		public
	 *	@return		HttpCookie
	 *	@throws		RuntimeException		if cookie support has not been initialized
	 */
	public function getCookie(): HttpCookie
	{
		if( !is_object( $this->cookie ) )
			throw new RuntimeException( 'Cookie resource not initialized within environment' );
		return $this->cookie;
	}

	/**
	 *	Initialize cookie resource instance.
	 *	@access		protected
	 *	@return		static
	 *	@throws		RuntimeException			if cookie resource has not been initialized before
	 */
	protected function initCookie(): static
	{
		if( !$this->url )
			throw new RuntimeException( 'URL not detected yet, run detectSelf beforehand' );
		$this->cookie	= new HttpCookie(
			(string) parse_url( $this->url, PHP_URL_PATH ),
			(string) parse_url( $this->url, PHP_URL_HOST ),
			(bool) getEnv( 'HTTPS' )
		);
		return $this;
	}
}
