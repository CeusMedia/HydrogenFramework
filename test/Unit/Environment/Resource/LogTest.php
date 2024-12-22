<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkUnitTest\Environment\Resource;

use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Log;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Hook as ModuleHookDefinition;
use CeusMedia\HydrogenFramework\Hook;
use CeusMedia\HydrogenFrameworkUnitTest\Environment\TestCase;
use Exception;
use RuntimeException;

//use PHPUnit\Framework\TestCase;

/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\Environment\Resource\Log
 */
class LogTest extends TestCase
{
	protected string $baseTestPath;

	protected Log $log;

	protected string $pathLogs;

/*	public function testFlattenMessage(): void
	{
	}*/

	/**
	 *	@covers		::log
	 *	@covers		::collectLogData
	 *	@covers		::flattenMessage
	 *	@covers		::handleLogWithAppDefault
	 *	@covers		::applyStrategyOnCollectedData
	 */
	public function testLog_useStrategyAppDefault(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_APP_DEFAULT] );
		$fileName	= $this->pathLogs.'app.log';
		unlink( $fileName );
		self::assertFileDoesNotExist( $fileName );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );
		self::assertFileExists( $fileName );
		self::assertStringEndsWith( 'INFO '.$message, trim( file_get_contents( $fileName ) ) );
		self::assertFileMatchesFormat('%sINFO '.$message.'%a', $fileName );
	}

	/**
	 *	@covers		::log
	 *	@covers		::collectLogData
	 *	@covers		::flattenMessage
	 *	@covers		::handleLogWithAppTyped
	 *	@covers		::applyStrategyOnCollectedData
	 */
	public function testLog_useStrategyAppTypes(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_APP_TYPED] );
		$fileName	= $this->pathLogs.'app.info.log';
		unlink( $fileName );
		self::assertFileDoesNotExist( $fileName );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );
		self::assertFileExists( $fileName );
		self::assertStringEndsWith( ' '.$message, trim( file_get_contents( $fileName ) ) );
		self::assertFileMatchesFormat('%s'.$message.'%a', $fileName );
	}

	/**
	 *	@covers		::log
	 *	@covers		::collectLogData
	 *	@covers		::flattenMessage
	 *	@covers		::handleLogWithCustomHooks
	 *	@covers		::applyStrategyOnCollectedData
	 */
	public function testLog_useStrategyCustomHooks(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_CUSTOM_HOOKS] );
		$this->env->getModules()->add( ModuleDefinition::create( 'TestModule', '1', 'test.xml' )
			->addHook( new ModuleHookDefinition( __NAMESPACE__.'\\TestLogHook::onLog', 'Env:Custom', 'log' ) )
		);

		$fileName	= $this->pathLogs.'hook.log';
		unlink( $fileName );
		self::assertFileDoesNotExist( $fileName );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );
		self::assertFileExists( $fileName );
		self::assertStringEndsWith( ' '.$message, trim( file_get_contents( $fileName ) ) );
		self::assertFileMatchesFormat('%s'.$message.'%a', $fileName );
	}

	/**
	 *	@covers		::log
	 *	@covers		::collectLogData
	 *	@covers		::flattenMessage
	 *	@covers		::handleLogWithCustomHooks
	 *	@covers		::applyStrategyOnCollectedData
	 *	@covers		::applyStrategyOnCollectedExceptionData
	 */
	public function testLog_useStrategyCustomHooks_fail(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_CUSTOM_HOOKS] );
		$this->env->getModules()->add( ModuleDefinition::create( 'TestModule', '1', 'test.xml' )
			->addHook( new ModuleHookDefinition( __NAMESPACE__.'\\TestLogHook::onFailToLog', 'Env:Custom', 'log' ) )
		);

		$fileNameFail		= $this->pathLogs.'hook.log';
		$fileNameDefault	= $this->pathLogs.'app.log';
		$fileNameException	= $this->pathLogs.'app.exception.log';

		unlink( $fileNameFail );
		unlink( $fileNameDefault );
		unlink( $fileNameException );
		self::assertFileDoesNotExist( $fileNameFail );
		self::assertFileDoesNotExist( $fileNameDefault );
		self::assertFileDoesNotExist( $fileNameException );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );

		self::assertFileDoesNotExist( $fileNameFail );
		self::assertFileDoesNotExist( $fileNameException );

		self::assertStringContainsString( 'THROW RuntimeException Failed to log', file_get_contents( $fileNameDefault ) );
	}

	/**
	 *	@covers		::log
	 *	@covers		::collectLogData
	 *	@covers		::flattenMessage
	 *	@covers		::handleLogWithCustomHooks
	 *	@covers		::handleExceptionWithAppTyped
	 *	@covers		::handleExceptionWithAppDefault
	 *	@covers		::applyStrategyOnCollectedData
	 *	@covers		::applyStrategyOnCollectedExceptionData
	 */
	public function testLog_useStrategyCustomHooks_failAndRecoverTyped(): void
	{
	$this->log->setStrategies( [Log::STRATEGY_CUSTOM_HOOKS, Log::STRATEGY_APP_TYPED, Log::STRATEGY_APP_DEFAULT] );
		$this->env->getModules()->add( ModuleDefinition::create( 'TestModule', '1', 'test.xml' )
			->addHook( new ModuleHookDefinition( __NAMESPACE__.'\\TestLogHook::onFailToLog', 'Env:Custom', 'log' ) )
		);

		$fileNameFail	= $this->pathLogs.'hook.log';
		$fileNameDefault	= $this->pathLogs.'app.log';
		$fileNameRecover	= $this->pathLogs.'app.info.log';
		$fileNameException	= $this->pathLogs.'app.exception.log';

		unlink( $fileNameFail );
		unlink( $fileNameRecover );
		unlink( $fileNameException );
		self::assertFileDoesNotExist( $fileNameFail );
		self::assertFileDoesNotExist( $fileNameRecover );
		self::assertFileDoesNotExist( $fileNameException );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );
		self::assertFileDoesNotExist( $fileNameFail );
		self::assertFileExists( $fileNameRecover );
		self::assertFileExists( $fileNameException );

		self::assertFileMatchesFormat('%s'.$message.'%a', $fileNameRecover );
		self::assertFileMatchesFormat( '%aMessage:    Fetching resource event hook TestModule>>Env:Custom>>log failed: Failed to log%a', $fileNameException );
		self::assertFileMatchesFormat('%aFetching resource event hook TestModule>>Env:Custom>>log failed: Failed to log%a', $fileNameDefault );
		self::assertFileMatchesFormat('%s THROW RuntimeException Failed to log%a', $fileNameDefault );
	}

	/**
	 *	@covers		::log
	 *	@covers		::collectLogData
	 *	@covers		::flattenMessage
	 *	@covers		::handleLogWithModuleHooks
	 *	@covers		::applyStrategyOnCollectedData
	 *	@covers		::applyStrategyOnCollectedExceptionData
	 */
	public function testLog_useStrategyModuleHooks(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_MODULE_HOOKS] );

		$this->env->getModules()->add( ModuleDefinition::create( 'TestModule', '1', 'test.xml' )
			->addHook( new ModuleHookDefinition( __NAMESPACE__.'\\TestLogHook::onLog', 'Env', 'log' ) )
		);

		$fileName	= $this->pathLogs.'hook.log';
		unlink( $fileName );
		self::assertFileDoesNotExist( $fileName );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );
		self::assertFileExists( $fileName );
		self::assertFileMatchesFormat('%sINFO '.$message.'%a', $fileName );
	}

	/**
	 *	@covers		::log
	 *	@covers		::collectLogData
	 *	@covers		::flattenMessage
	 *	@covers		::handleLogWithModuleHooks
	 *	@covers		::handleExceptionWithAppTyped
	 *	@covers		::handleExceptionWithAppDefault
	 *	@covers		::applyStrategyOnCollectedData
	 *	@covers		::applyStrategyOnCollectedExceptionData
	 */
	public function testLog_useStrategyModuleHooks_failAndRecoverTyped(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_MODULE_HOOKS, Log::STRATEGY_APP_TYPED, Log::STRATEGY_APP_DEFAULT] );
		$this->env->getModules()->add( ModuleDefinition::create( 'TestModule', '1', 'test.xml' )
			->addHook( new ModuleHookDefinition( __NAMESPACE__.'\\TestLogHook::onFailToLog', 'Env', 'log' ) )
		);

		$fileNameFail	= $this->pathLogs.'hook.log';
		$fileNameDefault	= $this->pathLogs.'app.log';
		$fileNameRecover	= $this->pathLogs.'app.info.log';
		$fileNameException	= $this->pathLogs.'app.exception.log';

		unlink( $fileNameFail );
		unlink( $fileNameRecover );
		unlink( $fileNameException );
		self::assertFileDoesNotExist( $fileNameFail );
		self::assertFileDoesNotExist( $fileNameRecover );
		self::assertFileDoesNotExist( $fileNameException );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );
		self::assertFileDoesNotExist( $fileNameFail );
		self::assertFileExists( $fileNameRecover );
		self::assertFileExists( $fileNameException );

		self::assertFileMatchesFormat('%s'.$message.'%a', $fileNameRecover );
		self::assertFileMatchesFormat( '%aMessage:    Fetching resource event hook TestModule>>Env>>log failed: Failed to log%a', $fileNameException );
		self::assertFileMatchesFormat('%aFetching resource event hook TestModule>>Env>>log failed: Failed to log%a', $fileNameDefault );
		self::assertFileMatchesFormat('%s THROW RuntimeException Failed to log%a', $fileNameDefault );
	}

	/**
	 *	@covers		::logException
	 *	@covers		::collectLogExceptionData
	 *	@covers		::flattenMessage
	 *	@covers		::handleExceptionWithAppDefault
	 *	@covers		::handleExceptionWithAppTyped
	 *	@covers		::applyStrategyOnCollectedExceptionData
	 */
	public function testLogException(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_APP_DEFAULT] );
		$fileName	= $this->pathLogs.'app.log';
		unlink( $fileName );
		self::assertFileDoesNotExist( $fileName );

		$message	= 'Test @'.time();
		$exception	= new Exception( $message );
		$this->log->logException( $exception );
		self::assertFileExists( $fileName );
		self::assertStringContainsString( 'THROW Exception '.$message, file_get_contents( $fileName ) );


		$this->log->setStrategies( [Log::STRATEGY_APP_TYPED] );
		$fileName	= $this->pathLogs.'app.exception.log';
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

	/**
	 *	@covers		::setStrategies
	 *	@covers		::setCustomerLogCallback
	 *	@covers		::collectLogExceptionData
	 *	@covers		::flattenMessage
	 *	@covers		::handleLogWithCustomCallback
	 */
	public function testLog_useStrategyCustomCallback(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_CUSTOM_CALLBACK] );
		$this->log->setCustomerLogCallback( [$this, 'callbackCustomerCallbackStrategy'] );

		$fileNameCustom	= $this->pathLogs.'custom.log';
		unlink( $fileNameCustom );
		self::assertFileDoesNotExist( $fileNameCustom );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );

		self::assertFileMatchesFormat( '%s INFO '.$message.'%a', $fileNameCustom );
	}

	/**
	 *	@covers		::setStrategies
	 *	@covers		::setCustomerLogCallback
	 *	@covers		::collectLogExceptionData
	 *	@covers		::flattenMessage
	 *	@covers		::handleLogWithCustomCallback
	 */
	public function testLog_useStrategyCustomCallback_fail(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_CUSTOM_CALLBACK] );
		$this->log->setCustomerLogCallback( [$this, 'callbackCustomerCallbackStrategy_fail'] );

		$fileNameCustom	= $this->pathLogs.'custom.log';
		unlink( $fileNameCustom );
		self::assertFileDoesNotExist( $fileNameCustom );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );
		self::assertFileDoesNotExist( $fileNameCustom );
	}

	/**
	 *	@covers		::setStrategies
	 *	@covers		::setCustomerLogCallback
	 *	@covers		::log
	 *	@covers		::collectLogExceptionData
	 *	@covers		::flattenMessage
	 *	@covers		::handleLogWithCustomCallback
	 *	@covers		::handleExceptionWithAppDefault
	 */
	public function testLog_useStrategyCustomCallback_failAndRecover(): void
	{
		$this->log->setStrategies( [Log::STRATEGY_CUSTOM_CALLBACK, Log::STRATEGY_APP_DEFAULT] );
		$this->log->setCustomerLogCallback( [$this, 'callbackCustomerCallbackStrategy_fail'] );

		$fileNameCustom		= $this->pathLogs.'custom.log';
		$fileNameRecover	= $this->pathLogs.'app.log';
		unlink( $fileNameCustom );
		unlink( $fileNameRecover );
		self::assertFileDoesNotExist( $fileNameCustom );
		self::assertFileDoesNotExist( $fileNameRecover );

		$message	= 'Test @'.time();
		$this->log->log( Log::TYPE_INFO, $message );
		self::assertFileDoesNotExist( $fileNameCustom );

		self::assertFileExists( $fileNameRecover );
		self::assertFileMatchesFormat( '%aINFO '.$message.'%a', $fileNameRecover );
	}
	protected function setUp(): void
	{
		parent::setUp();
		$this->log		= new Log( $this->env );
		$this->pathLogs	= $this->baseTestPath.'assets/app/logs/';
		if( !file_exists( $this->pathLogs ) )
			mkdir( $this->pathLogs );

//		Loader::create( 'php', $this->baseTestPath.'assets/app/classes' )->register();
	}

	public function callbackCustomerCallbackStrategy( $payload )
	{
		$filePath	= $this->env->path.$this->env->getPath( 'logs' ).'custom.log';
		$data		= (object) $payload;
		$entryLine	= join( ' ', [
			date( $data->datetime ),
			strtoupper( $data->type ),
			$data->message
		] );
		file_put_contents( $filePath, $entryLine.PHP_EOL );
	}

	public function callbackCustomerCallbackStrategy_fail( $payload )
	{
		throw new RuntimeException( 'Failed to log' );
	}
}

class TestLogHook extends Hook
{
	/**
	 *	@return		void
	 *	@throws		RuntimeException
	 */
	public function onFailToLog(): void
	{
		throw new RuntimeException( 'Failed to log' );
	}

	public function onLog(): void
	{
		$filePath	= $this->env->path.$this->env->getPath( 'logs' ).'hook.log';
		$data		= (object) $this->payload;
		$entryLine	= join( ' ', [
				date( $data->datetime ),
				strtoupper( $data->type ),
				$data->message
			] );
		file_put_contents( $filePath, $entryLine.PHP_EOL );
	}

	public function onLogException(): void
	{
		$filePath	= $this->env->path.$this->env->getPath( 'logs' ).'hook.exception.log';
		$data		= (object) $this->payload;
		$entryLine	= join( ' ', [
				date( $data->datetime ),
				strtoupper( $data->type ),
				$data->message
			] );
		file_put_contents( $filePath, $entryLine.PHP_EOL );
	}
}