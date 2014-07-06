<?php
class CMF_Hydrogen_Environment_Resource_Captain {

	protected $env;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
	}

	public function callHook( $resource, $event, $context, $arguments = array() ){
//		$this->env->clock->profiler->tick( 'Resource_Module_Library_Local::callHook: '.$event.'@'.$resource.' start' );
		$count		= 0;
		$modules	= $this->env->hasModules() ? $this->env->getModules()->getAll() : array();
		foreach( $modules as $module ){
			if( empty( $module->hooks[$resource][$event] ) )
				continue;
			$function	= $module->hooks[$resource][$event];
			$pattern	= "/^([a-z0-9_]+)::([a-z0-9_]+)$/i";
			if( preg_match( $pattern, $function ) ){
				$className	= preg_replace( $pattern, "\\1", $function );
				$methodName	= preg_replace( $pattern, "\\2", $function );
				$function	= array( $className, $methodName );
			}
			else
				$function	= create_function( '$env, $context, $module, $arguments = array()', $function );
			try{
				$count++;
				ob_start();
				$args	= array( $this->env, &$context, $module, $arguments );
				call_user_func_array( $function, $args );
				$this->env->clock->profiler->tick( '<!--Resource_Module_Library_Local::call-->Hook: '.$event.'@'.$resource.': '.$module->id );
				$stdout	= ob_get_clean();
				if( strlen( $stdout ) )
					if( $this->env->has( 'messenger' ) )
						$this->env->getMessenger()->noteNotice( 'Call on event '.$event.'@'.$resource.' hooked by module '.$module->id.' reported: '.$stdout );
					else
						throw new RuntimeException( $stdout );
			}
			catch( Exception $e ){
				if( $this->env->has( 'messenger' ) )
					$this->env->getMessenger()->noteFailure( 'Call on event '.$event.'@'.$resource.' hooked by module '.$module->id.' failed: '.$e->getMessage() );
				else
					throw new RuntimeException( 'Hook '.$module->id.'::'.$resource.'@'.$event.' failed: '.$e->getMessage(), 0, $e );
			}
		}
//		$this->env->clock->profiler->tick( 'Resource_Module_Library_Local::callHook: '.$event.'@'.$resource.' done' );
		return $count;
	}
}
?>