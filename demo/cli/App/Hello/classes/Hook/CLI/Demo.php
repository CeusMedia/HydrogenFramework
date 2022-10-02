<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\CLI;
use CeusMedia\HydrogenFramework\Hook;

class Hook_CLI_Demo extends Hook
{
	public static function onDemonstrateHook( $env, $context, $module, $payload )
	{
		$session		= $env->getSession();

		$timeOnAppRun	= $session->get( 'timeOnAppRun' );
		$timeDiff		= UnitFormater::formatSeconds( microtime( TRUE ) - $timeOnAppRun );
		$someThing		= $session->get( 'someThingSetByController' );

		CLI::out( 'Event "Demo::demonstrateHook" caught by Hook.' );
		CLI::out( 'Event Payload:     '.json_encode( $payload ) );
		CLI::out( 'Time since start:  '.$timeDiff );
		CLI::out( 'Session Example:   '.$someThing );

		$payload['key']	= 'valueChangedByHook@'.$someThing;

		$session->set( 'someThingSetByHook', 'yapp!' );
	}
}
