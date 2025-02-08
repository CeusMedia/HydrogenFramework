<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;

trait RequestByHttp
{
	/**	@var	HttpRequest				$request	HTTP Request Object */
	private HttpRequest $request;

	/**
	 *	Returns Request Object.
	 *	@access		public
	 *	@return		HttpRequest
	 */
	public function getRequest(): HttpRequest
	{
		return $this->request ?? new HttpRequest();
	}

	/**
	 *	Initialize HTTP request resource instance.
	 *	Request data will be imported from given web server environment.
	 *	@access		protected
	 *	@return		static
	 */
	protected function initRequest(): static
	{
		$this->request		= new HttpRequest();
		$this->request->fromEnv();
		$this->runtime->reach( 'env: request' );
		return $this;
	}
}
