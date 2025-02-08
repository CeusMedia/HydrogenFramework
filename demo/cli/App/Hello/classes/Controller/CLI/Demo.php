<?php

use CeusMedia\Common\CLI as Console;
use CeusMedia\Common\CLI\Color as ConsoleColor;
use CeusMedia\HydrogenFramework\Controller\Console as ConsoleController;

class Controller_CLI_Demo extends ConsoleController
{
	public function run(): void
	{
		$color		= new ConsoleColor();
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$language	= $this->env->getLanguage();
		$modules	= $this->env->getModules();

		$wordsMain	= (object) $language->getWords( 'main' )['main'];

		$session->set( 'someThingSetByController', substr( md5( uniqid() ), rand( 0, 20 ), 6 ) );

		Console::out( $color->asSuccess( 'Hello World!' ) );
		Console::out();
		Console::out( 'Application Title: '.$wordsMain->title );
		Console::out();
		Console::out( 'Commands given:    '.json_encode( $request->get( 'commands' ) ) );
		Console::out( 'Parameters given:  '.json_encode( $request->get( 'parameters' ) ) );
		Console::out();
		Console::out( 'PHP Version:       '.$this->env->getPhp()->getCurrentVersion() );
		Console::out( 'Framework Version: '.$this->env->version );
		Console::out();
		Console::out( 'Environment Class: '.get_class( $this->env ) );
		Console::out( 'Environment URI:   '.$this->env->uri );
		Console::out( 'Modules Path:      '.$modules->getPath() );
		Console::out( 'Modules Installed: '.join( ', ', array_keys( $modules->getAll() ) ) );
		Console::out();

	}
}
