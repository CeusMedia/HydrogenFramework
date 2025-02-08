<?php

use CeusMedia\Common\CLI as Console;
use CeusMedia\Common\Env as CommonEnv;
use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment;

( include_once dirname( __DIR__, 4 ).'/vendor/autoload.php' ) or die( 'Install packages using composer, first!'.PHP_EOL );

chdir( __DIR__ );

//die("!");

//if( !@include_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ).'/vendor/autoload.php' )
//    die( 'You need to "composer install" first.' );
if( !CommonEnv::isCli() )
	die( 'Access denied: Execution via CLI, only.' );

Environment::$defaultPaths['config']	= '';
Loader::create( 'php', __DIR__.'/classes/' )->register();

Console::out();
Console::out( 'Hydrogen CLI Demo: App/Hello' );
Console::out();

$app	= new App();
$app->run();

