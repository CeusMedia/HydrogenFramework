<?php
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition\Hook as ModuleHookDefinition;

class Controller_Index extends Controller
{
	public function index(): void
	{
		$this->addData( 'microtime', microtime( TRUE ) );
		$this->env->getCaptain()->registerCustomHook( new ModuleHookDefinition( 'Hook_Custom::random', 'Custom', 'random' ) );
	}
}
