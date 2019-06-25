<?php
if( !@include_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ).'/vendor/autoload.php' )
    die( 'You need to "composer install" first.' );

CMF_Hydrogen_Environment::$configPath	= '';

class DemoApp extends CMF_Hydrogen_Application_Console{
	public function run(){
		print "Hello World!";
	}
}

$app	= new DemoApp();
$app->run();

