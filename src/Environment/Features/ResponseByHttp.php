<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Environment\Features;

use CeusMedia\Common\Net\HTTP\Response as HttpResponse;

trait ResponseByHttp
{
	/**	@var	HttpResponse			$response	HTTP Response Object */
	protected HttpResponse $response;

	/**
	 *	Returns HTTP Response Object.
	 *	@access		public
	 *	@return		HttpResponse
	 */
	public function getResponse(): HttpResponse
	{
		return $this->response;
	}

	/**
	 *	Initialize HTTP response resource instance.
	 *	@access		protected
	 *	@return		static
	 */
	protected function initResponse(): static
	{
		$this->response	= new HttpResponse();
		$this->runtime->reach( 'env: response' );
		return $this;
	}
}
