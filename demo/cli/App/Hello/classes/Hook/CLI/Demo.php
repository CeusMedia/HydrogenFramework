<?php
class Hook_CLI_Demo extends CMF_Hydrogen_Hook
{
	public static function onDemonstrateHook( $env, $context, $module, $payload )
	{
		$session		= $env->getSession();

		$timeOnAppRun	= $session->get( 'timeOnAppRun' );
		$timeDiff		= Alg_UnitFormater::formatSeconds( microtime( TRUE ) - $timeOnAppRun );
		$someThing		= $session->get( 'someThingSetByController' );

		CLI::out( 'Event "Demo::demonstrateHook" caught by Hook.' );
		CLI::out( 'Event Payload:     '.json_encode( $payload ) );
		CLI::out( 'Time since start:  '.$timeDiff );
		CLI::out( 'Session Example:   '.$someThing );

		$payload->key	= 'valueChangedByHook@'.$someThing;

		$session->set( 'someThingSetByHook', 'yapp!' );
	}
}
