<?php
use CeusMedia\Common\CLI;
use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Application\ConsoleAbstraction as ConsoleApp;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Language as LanguageResource;

if( !@include_once dirname( __DIR__, 4 ).'/vendor/autoload.php' )
    die( 'You need to "composer install" first.' );
if( !CLI::checkIsCLi( FALSE ) )
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
		$logicPool	= $this->env->getLogic();

		CLI::out();
		CLI::out( 'Hydrogen CLI Demo: Resource/LogicPool' );
		CLI::out();

		$logicPool		= $this->env->getLogic();
		$logicClassName	= 'Logic_IP_Lock_Transport';
		$logicPoolKey	= $logicPool->getKeyFromClassName( $logicClassName );

		CLI::out( 'Logic class "'.$logicClassName.'" would be stored in logic pool by key "'.$logicPoolKey.'"' );
		CLI::out();
		return 0;
	}
}

$app	= new DemoApp();
$app->run();
