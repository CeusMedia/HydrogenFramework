<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkTest\Environment\Resource;

use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Log;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\Environment\Resource\Log
 */
class LogTest extends TestCase
{
	protected string $baseTestPath;

	protected Log $log;

	/**
	 *	@covers		::log
	 *	@covers		::collectLogData
	 *	@covers		::flattenMessage
	 *	@covers		::handleLogWithAppDefault
	 *	@covers		::handleLogWithAppTyped
	 */
	public function testLog(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_APP_DEFAULT] );
		$fileName	= $this->baseTestPath.'assets/app/logs/app.log';
		unlink( $fileName );
		self::assertFileDoesNotExist( $fileName );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );
		self::assertFileExists( $fileName );
		self::assertStringEndsWith( 'INFO '.$message, trim( file_get_contents( $fileName ) ) );


		$this->log->setStrategies( [Log::STRATEGY_APP_TYPED] );
		$fileName	= $this->baseTestPath.'assets/app/logs/app.info.log';
		unlink( $fileName );
		self::assertFileDoesNotExist( $fileName );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );
		self::assertFileExists( $fileName );
		self::assertStringEndsWith( ' '.$message, trim( file_get_contents( $fileName ) ) );
	}

	/**
	 *	@covers		::logException
	 *	@covers		::collectLogExceptionData
	 *	@covers		::flattenMessage
	 *	@covers		::handleExceptionWithAppDefault
	 *	@covers		::handleExceptionWithAppTyped
	 */
	public function testLogException(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_APP_DEFAULT] );
		$fileName	= $this->baseTestPath.'assets/app/logs/app.log';
		unlink( $fileName );
		self::assertFileDoesNotExist( $fileName );

		$message	= 'Test @'.time();
		$exception	= new Exception( $message );
		$this->log->logException( $exception );
		self::assertFileExists( $fileName );
		self::assertStringContainsString( 'THROW Exception '.$message, file_get_contents( $fileName ) );


		$this->log->setStrategies( [Log::STRATEGY_APP_TYPED] );
		$fileName	= $this->baseTestPath.'assets/app/logs/app.exception.log';
		unlink( $fileName );
		self::assertFileDoesNotExist( $fileName );

		$message	= 'Test @'.time();
		$exception	= new Exception( $message );
		$this->log->logException( $exception );
		self::assertFileExists( $fileName );
		$content	= file_get_contents( $fileName );
		self::assertStringContainsString( 'Type:       Exception', $content );
		self::assertStringContainsString( 'Message:    '.$message, $content );
	}

	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 2 ).'/';
		$this->log	= new Log( new Environment( ['pathApp' => $this->baseTestPath.'assets/app/'] ) );

//		Loader::create( 'php', $this->baseTestPath.'assets/app/classes' )->register();
	}
}
