<?php

declare(strict_types=1);

namespace CeusMedia\CommonTest\Unit\Controller;

use CeusMedia\Common\Loader;
use CeusMedia\Common\UI\OutputBuffer;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use PHPUnit\Framework\TestCase;
use CeusMedia\Common\Net\HTTP\Response as HttpResponse;
use SimpleXMLElement;

/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\Controller\Ajax
 */
class AjaxTest extends TestCase
{
	protected string $baseTestPath;
	protected WebEnvironment $env;
	protected \Controller_Ajax_Test $controller;
	protected HttpResponse|NULL $lastResponse		= NULL;

	protected string $mimeTypeJson	= 'application/json';
	protected string $mimeTypeXml	= 'application/xml';

	public function testConstruct_notAjaxInLiveMode_willResponseError_asJson(): void
	{
		$this->env->setMode( Environment::MODE_LIVE );
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

//		ob_start();
//		ob_start();
		$controller = new \Controller_Ajax_Test( $this->env );

		$body	= json_decode( $this->lastResponse->getBody() );
		self::assertSame( '406 Not Acceptable', $this->lastResponse?->getStatus() );
		self::assertSame( 'error', $body->status );
		self::assertSame( 400000, $body->code );
		self::assertSame( 'Access denied for non-AJAX requests', $body->message );
	}

	public function testRespondData_withExceptionOnInit_willRespondException_asJson(): void
	{
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);
		\Controller_Ajax_Test::$throwExceptionOnInit	= TRUE;

//		ob_start();
		$controller = new \Controller_Ajax_Test( $this->env );

		$body	= json_decode( $this->lastResponse->getBody() );
		self::assertSame( '500 Internal Server Error', $this->lastResponse?->getStatus() );
		self::assertSame( 'exception', $body->status );
		self::assertSame( 0, $body->code );
		self::assertSame( 'Test exception on init of AJAX controller', $body->message );
		self::assertSame( $this->mimeTypeJson, $this->lastResponse?->getHeader( 'Content-Type' )[0]->getValue() );
	}

	public function testRespondData_asJson(): void
	{
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

//		ob_start();
//		echo "1";

		$this->controller->testRespondData( 1, 200 );
		$body				= json_decode( $this->lastResponse->getBody() );
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );
		self::assertSame( '200 OK', $this->lastResponse?->getStatus() );
		self::assertSame( 'data', $body->status );
		self::assertSame( 1, $body->data ?? '' );
//		self::assertSame( '1', $body->dev ?? '' );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( $this->mimeTypeJson, $contentTypeHeader[0]->getValue() );
//		self::assertSame( '2', base64_decode( $this->lastResponse->getHeader( 'X-AJAX-Dev' )[0]->getValue() ) );

		$this->controller->testRespondData( 1, 201, 'text/bogus' );
		$body				= json_decode( $this->lastResponse->getBody() );
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );
		self::assertSame( '201 Created', $this->lastResponse?->getStatus() );
		self::assertSame( 'data', $body->status );
		self::assertSame( 1, $body->data ?? '' );
//		self::assertSame( '6', $body->dev ?? '' );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
//		self::assertSame( '5', base64_decode( $this->lastResponse->getHeader( 'X-AJAX-Dev' )[0]->getValue() ) );
	}

	public function testRespondError_asJson(): void
	{
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_JSON;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

		$this->controller->testRespondError( 2, 'Test Error', 400, 'text/bogus' );
		$body				= json_decode( $this->lastResponse->getBody() );
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );

		self::assertSame( '400 Bad Request', $this->lastResponse?->getStatus() );
		self::assertSame( 'error', $body->status );
		self::assertSame( 2, $body->code );
		self::assertSame( 'Test Error', $body->message );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
//		self::assertSame( '1', base64_decode( $this->lastResponse->getHeader( 'X-AJAX-Dev' )[0]->getValue() ) );
	}

	public function testRespondError_withDevOutput_asJson(): void
	{
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_JSON;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

		$this->controller->setDevBuffer( new OutputBuffer() );
		echo 'Dev Output';

		$this->controller->testRespondError( 2, 'Test Error', 400, 'text/bogus' );
		$body				= json_decode( $this->lastResponse->getBody() );
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );

		self::assertSame( '400 Bad Request', $this->lastResponse?->getStatus() );
		self::assertSame( 'error', $body->status );
		self::assertSame( 2, $body->code );
		self::assertSame( 'Test Error', $body->message );
		self::assertSame( 'Dev Output', $body->dev ?? '' );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
	}

	public function testRespondException_asJson(): void
	{
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_JSON;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

//		ob_start();
//		echo "1";
//		ob_start();
//		echo "2";

		$exception = new \RuntimeException( 'Test Exception', 3 );
		$this->controller->testRespondException( $exception, 502, 'text/bogus' );
		$body				= json_decode( $this->lastResponse->getBody() );
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );

		self::assertSame( '502 Bad Gateway', $this->lastResponse?->getStatus() );
		self::assertSame( 'exception', $body->status );
		self::assertSame( 3, $body->code );
		self::assertSame( 'Test Exception', $body->message );
//		self::assertSame( '2', $body->dev ?? '' );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
	}

	public function testRespondException_withDevOutput_asJson(): void
	{
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_JSON;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

		$this->controller->setDevBuffer( new OutputBuffer() );
		echo 'Dev Output';

		$exception = new \RuntimeException( 'Test Exception', 3 );
		$this->controller->testRespondException( $exception, 502, 'text/bogus' );
		$body				= json_decode( $this->lastResponse->getBody() );
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );

		self::assertSame( '502 Bad Gateway', $this->lastResponse?->getStatus() );
		self::assertSame( 'exception', $body->status );
		self::assertSame( 3, $body->code );
		self::assertSame( 'Test Exception', $body->message );
		self::assertSame( 'Dev Output', $body->dev ?? '' );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
	}

	public function testConstruct_notAjaxInLiveMode_willResponseError_asXml(): void
	{
		$this->env->setMode( Environment::MODE_LIVE );
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_XML;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

		$controller = new \Controller_Ajax_Test( $this->env );

		/** @var SimpleXMLElement $body */
		$body	= simplexml_load_string( $this->lastResponse->getBody() );
		/** @var array $contentTypeHeader */
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );

		self::assertSame( '406 Not Acceptable', $this->lastResponse?->getStatus() );
		self::assertSame( 'error', (string) $body->status );
		self::assertSame( 400000, (int) $body->code ?? 0 );
		self::assertSame( 'Access denied for non-AJAX requests', (string) $body->message );
		self::assertSame( 1, count( $contentTypeHeader ) );
//		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
	}

	public function testRespondData_withExceptionOnInit_willRespondException_asXml(): void
	{
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_XML;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);
		\Controller_Ajax_Test::$throwExceptionOnInit	= TRUE;

		$controller = new \Controller_Ajax_Test( $this->env );

		/** @var SimpleXMLElement $body */
		$body	= simplexml_load_string( $this->lastResponse->getBody() );
		/** @var array $contentTypeHeader */
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );

		self::assertSame( '500 Internal Server Error', $this->lastResponse?->getStatus() );
		self::assertSame( 'exception', (string) $body->status );
		self::assertSame( '0', (string) $body->code );
		self::assertSame( 'Test exception on init of AJAX controller', (string) $body->message );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( $this->mimeTypeXml, $contentTypeHeader[0]->getValue() );
	}

	public function testRespondData_asXml(): void
	{
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_XML;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

		$this->controller->testRespondData( 1, 200, 'text/bogus' );
		/** @var SimpleXMLElement $body */
		$body	= simplexml_load_string( $this->lastResponse->getBody() );
		/** @var array $contentTypeHeader */
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );
		self::assertSame( '200 OK', $this->lastResponse?->getStatus() );
		self::assertSame( 'data', (string) $body->status );
		self::assertSame( '1', (string) ( $body->data ?? '' ) );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
	}

	public function testRespondData_withDevOutput_asXml(): void
	{
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_XML;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

		$this->controller->setDevBuffer( new OutputBuffer() );
		echo 'Dev Output';

		$this->controller->testRespondData( 1, 200, 'text/bogus' );
		/** @var SimpleXMLElement $body */
		$body	= simplexml_load_string( $this->lastResponse->getBody() );
		/** @var array $contentTypeHeader */
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );
		self::assertSame( '200 OK', $this->lastResponse?->getStatus() );
		self::assertSame( 'data', (string) $body->status );
		self::assertSame( '1', (string) ( $body->data ?? '' ) );
		self::assertSame( 'Dev Output', (string) ( $body->dev ?? '' ) );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
	}

	public function testRespondError_asXml(): void
	{
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_XML;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

		$this->controller->testRespondError( 2, 'Test Error', 400, 'text/bogus' );
		/** @var SimpleXMLElement $body */
		$body	= simplexml_load_string( $this->lastResponse->getBody() );
		/** @var array $contentTypeHeader */
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );

		self::assertSame( '400 Bad Request', $this->lastResponse?->getStatus() );
		self::assertSame( 'error', (string) $body->status );
		self::assertSame( '2', (string) $body->code );
		self::assertSame( 'Test Error', (string) $body->message );
//		self::assertSame( '2', (string) ( $body->dev ?? '' ) );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
	}

	public function testRespondError_withDevOutput_asXml(): void
	{
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_XML;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

		$this->controller->setDevBuffer( new OutputBuffer() );
		echo 'Dev Output';

		$this->controller->testRespondError( 2, 'Test Error', 400, 'text/bogus' );
		/** @var SimpleXMLElement $body */
		$body	= simplexml_load_string( $this->lastResponse->getBody() );
		/** @var array $contentTypeHeader */
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );

		self::assertSame( '400 Bad Request', $this->lastResponse?->getStatus() );
		self::assertSame( 'error', (string) $body->status );
		self::assertSame( '2', (string) $body->code );
		self::assertSame( 'Test Error', (string) $body->message );
		self::assertSame( 'Dev Output', (string) ( $body->dev ?? '' ) );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
	}

	public function testRespondException_asXml(): void
	{
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_XML;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

		$exception = new \RuntimeException( 'Test Exception', 3 );
		$this->controller->testRespondException( $exception, 502, 'text/bogus' );
		/** @var SimpleXMLElement $body */
		$body	= simplexml_load_string( $this->lastResponse->getBody() );
		/** @var array $contentTypeHeader */
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );

		self::assertSame( '502 Bad Gateway', $this->lastResponse?->getStatus() );
		self::assertSame( 'exception', (string) $body->status );
		self::assertSame( '3', (string) $body->code );
		self::assertSame( 'Test Exception', (string) $body->message );
//		self::assertSame( '2', (string) ( $body->dev ?? '' ) );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
	}

	public function testRespondException_withDevOutput_asXml(): void
	{
		AjaxController::$responseFormat	= AjaxController::RESPONSE_FORMAT_XML;
		AjaxController::setResponseStrategy(
			AjaxController::RESPONSE_STRATEGY_CALLBACK,
			[$this, 'callbackResponse']
		);

		$this->controller->setDevBuffer( new OutputBuffer() );
		echo 'Dev Output';

		$exception = new \RuntimeException( 'Test Exception', 3 );
		$this->controller->testRespondException( $exception, 502, 'text/bogus' );
		/** @var SimpleXMLElement $body */
		$body	= simplexml_load_string( $this->lastResponse->getBody() );
		/** @var array $contentTypeHeader */
		$contentTypeHeader	= $this->lastResponse->getHeader( 'Content-Type' );

		self::assertSame( '502 Bad Gateway', $this->lastResponse?->getStatus() );
		self::assertSame( 'exception', (string) $body->status );
		self::assertSame( '3', (string) $body->code );
		self::assertSame( 'Test Exception', (string) $body->message );
		self::assertSame( 'Dev Output', (string) ( $body->dev ?? '' ) );
		self::assertSame( 1, count( $contentTypeHeader ) );
		self::assertSame( 'text/bogus', $contentTypeHeader[0]->getValue() );
	}

	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 2 ).'/';
		$this->env		= new WebEnvironment( [
			'pathApp'	=> '',
			'uri'		=> $this->baseTestPath.'assets/app/',
			'isTest'	=> TRUE,
		] );
		$this->env->setMode( WebEnvironment::MODE_TEST );

		Loader::create( 'php', $this->baseTestPath.'assets/app/classes' )->register();

		$this->controller = new \Controller_Ajax_Test( $this->env );
		\Controller_Ajax_Test::$throwExceptionOnInit	= FALSE;

	}

	public function callbackResponse( HttpResponse $response ): int
	{
		$this->lastResponse = $response;
		return 1;
	}
}
/*
class FakeResponse extends HttpResponse
{
	public string $protocol			= 'HTTP';
	public string $version			= '1.0';
	public ?string $body			= NULL;
	public ?string $compression		= NULL;
	public bool $sendLengthHeader	= TRUE;
	public bool $andExit			= TRUE;

	public function send( ?string $compression = NULL, bool $sendLengthHeader = TRUE, bool $andExit = TRUE ): HttpResponse
	{
		$this->compression = $compression;
		$this->sendLengthHeader = $sendLengthHeader;
		$this->andExit = $andExit;
		return $this;
	}
}*/