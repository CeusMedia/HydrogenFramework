<?php
namespace CeusMedia\HydrogenFramework\Environment\Resource\Runtime;

use CeusMedia\HydrogenFramework\Deprecation;
use CeusMedia\HydrogenFramework\Environment\Resource\Runtime;

class Profiler
{
	protected $enabled;
	protected $runtime;

	public function __construct( Runtime $runtime, bool $enabled = FALSE )
	{
		$this->runtime	= $runtime;
		$this->enabled	= $enabled;
	}

	public function tick( string $message, string $description = NULL )
	{
		$this->markDeprecation( 'tick' );
		if( $this->enabled )
			$this->runtime->reach( $message, $description );
	}

	public function get(): array
	{
		$this->markDeprecation( 'get' );
		$list	= [];
		if( $this->enabled ){
			foreach( $this->runtime->getGoals() as $goal )
				$list[]	= (array) $goal;
		}
		return $list;
	}

	protected function markDeprecation( string $type )
	{
		$message	= 'CMF_Hydrogen_Environment_Resource_Runtime_Profiler::get is deprecated. Use $env->getRuntime()->getGoals() instead';
		if( $type === 'tick' )
			$message	= 'CMF_Hydrogen_Environment_Resource_Runtime_Profiler::tick is deprecated. Use $env->getRuntime()->reach() instead';
		Deprecation::getInstance()
			->setErrorVersion( '0.8.7.9' )
			->setExceptionVersion( '0.9' )
			->message( $message );
	}
}
