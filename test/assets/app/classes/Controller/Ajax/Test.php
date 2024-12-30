<?php

use CeusMedia\Common\Net\HTTP\Response as HttpResponse;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Test extends AjaxController
{
	public static $throwExceptionOnInit	= FALSE;
	public function testRespondData( mixed $data, int $statusCode = 200, string $mimeType = NULL ): int
	{
		return $this->respondData( $data, $statusCode, $mimeType );
	}

	public function testRespondError( int|string $code, string $message, int $statusCode = 412, string $mimeType = NULL ): int
	{
		return $this->respondError( $code, $message, $statusCode, $mimeType );
	}

	public function testRespondException( Throwable $e, int $statusCode, string $mimeType = NULL ): int
	{
		return $this->respondException( $e, $statusCode,$mimeType );
	}

	public function injectResponseForTesting( HttpResponse $response ): static
	{
		$this->response = $response;
		return $this;
	}

	protected function __onInit(): void
	{
		if( self::$throwExceptionOnInit )
			throw new Exception( 'Test exception on init of AJAX controller' );
	}
}