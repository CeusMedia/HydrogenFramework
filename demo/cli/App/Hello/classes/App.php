<?php

use CeusMedia\Common\CLI;
use CeusMedia\HydrogenFramework\Application\Console as ConsoleApp;

class App extends ConsoleApp
{
	public function run()
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$captain	= $this->env->getCaptain();

		$session->set( 'timeOnAppRun', microtime( TRUE ) );

		$commands	= $request->get( 'commands' );
		switch( current( $commands ) ){
			case 'index':
			default:
				$controller	= new Controller_CLI_Demo( $this->env );
				$controller->run();
		}

		$payload	= array( 'key' => 'value' );
		$captain->callHook( 'Demo', 'demonstrateHook', $this, $payload );
		CLI::out();
		CLI::out( 'Hook Payload:      '.json_encode( $payload ) );
		CLI::out( 'Session Data:      '.json_encode( $session->getAll() ) );
		CLI::out();
	}
}
