<?php

use CeusMedia\Common\CLI as Console;
use CeusMedia\HydrogenFramework\Application\ConsoleAbstraction as ConsoleApp;

class App extends ConsoleApp
{
	public function run(): ?int
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

		$payload	= ['key' => 'value'];
		$captain->callHook( 'Demo', 'demonstrateHook', $this, $payload );
		Console::out();
		Console::out( 'Hook Payload:      '.json_encode( $payload ) );
		Console::out( 'Session Data:      '.json_encode( $session->getAll() ) );
		Console::out();
		return 0;
	}
}
