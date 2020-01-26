<?php
if( !@include_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ).'/vendor/autoload.php' )
    die( 'You need to "composer install" first.' );
if( !CLI::checkIsCLi( FALSE ) )
	die( 'Access denied: Execution via CLI, only.' );

new UI_DevOutput;

//CMF_Hydrogen_Environment::$configFile	= 'config.ini';
CMF_Hydrogen_Environment::$configPath	= '';
CMF_Hydrogen_Environment_Resource_Language::$fileExtension	= 'locale';

class DemoApp extends CMF_Hydrogen_Application_Console{
	public function run(){
		$logicPool	= $this->env->getLogic();

		CLI::out();
		CLI::out( 'Hydrogen CLI Demo: Resource/LogicPool' );
		CLI::out();

		$logicPool		= $this->env->getLogic();
		$logicClassName	= 'Logic_IP_Lock_Transport';
		$logicPoolKey	= $logicPool->getKeyFromClassName( $logicClassName );

		CLI::out( 'Logic class "'.$logicClassName.'" would be stored in logic pool by key "'.$logicPoolKey.'"' );
		CLI::out();
	}
}

$app	= new DemoApp();
$app->run();
