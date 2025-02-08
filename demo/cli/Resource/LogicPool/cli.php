<?php
use CeusMedia\Common\CLI as Console;
use CeusMedia\Common\Env as CommonEnv;

//use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Application\ConsoleAbstraction as ConsoleApp;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as LanguageResource;

if( !@include_once dirname( __DIR__, 4 ).'/vendor/autoload.php' )
    die( 'You need to "composer install" first.' );
if( !CommonEnv::isCli() )
	die( 'Access denied: Execution via CLI, only.' );

chdir( __DIR__ );

new CeusMedia\Common\UI\DevOutput;

//Environment::$configFile	= 'config.ini';
Environment::$defaultPaths['config']	= '';
LanguageResource::$fileExtension	= 'locale';


class DemoApp extends ConsoleApp
{
	public function run(): ?int
	{
		Console::out();
		Console::out( 'Hydrogen CLI Demo: Resource/LogicPool' );
		Console::out( '-------------------------------------' );
		Console::out();

		$logicPool		= $this->env->getLogic();
		$logicClassName	= 'Logic_IP_Lock_Transport';
		$logicPoolKey	= $logicPool->getKeyFromClassName( $logicClassName );

		Console::out( 'Logic class "'.$logicClassName.'" would be stored in logic pool by key "'.$logicPoolKey.'"' );
		Console::out();
		return 0;
	}
}

$app	= new DemoApp();
$app->run();
