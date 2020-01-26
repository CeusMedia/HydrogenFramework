<?php
if( !@include_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ).'/vendor/autoload.php' )
    die( 'You need to "composer install" first.' );
if( !CLI::checkIsCLi( FALSE ) )
	die( 'Access denied: Execution via CLI, only.' );

CMF_Hydrogen_Environment::$configPath	= '';
Loader::registerNew( 'php', '', 'classes/' );

CLI::out();
CLI::out( 'Hydrogen CLI Demo: App/Hello' );
CLI::out();

$app	= new App();
$app->run();
