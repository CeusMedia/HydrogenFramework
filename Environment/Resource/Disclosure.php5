<?php
class CMF_Hydrogen_Environment_Resource_Disclosure{

	protected $options	= array(
		'classPrefix'		=> 'Controller_',
		'readMethods'		=> TRUE,
		'readParameters'	=> TRUE,
		'fileExtension'		=> 'php.?',
		'reflectClass'		=> FALSE,
		'reflectMethod'		=> FALSE,
		'reflectParameter'	=> FALSE,
		'skipAbstract'		=> TRUE,
		'skipMagic'			=> TRUE,
		'skipInherited'		=> TRUE,
		'SkipFramework'		=> TRUE,
		'methodFilter'		=> ReflectionMethod::IS_PUBLIC
	);

	public function __construct( $options = array() ){
		$this->options	= array_merge( $this->options, $options );
	}

	public function reflect( $path, $options = array() ){
		$options	= array_merge( $this->options, $options );

		$classes	= array();
		$path		= realpath( $path );
		$index		= new File_RecursiveRegexFilter( $path, '/^[^_].+\.'.$options['fileExtension'].'$/' );
		foreach( $index as $entry ){
			$fileName	= preg_replace( '@^'.$path.'/@', '', $entry->getPathname() );
			$fileBase	= preg_replace( '@\.'.$options['fileExtension'].'$@', '', $fileName );
			$controller	= str_replace( '/', '_', $fileBase );
			$className	= $options['classPrefix'].$controller;

			$classReflection	= new ReflectionClass( $className );
			$class	= new stdClass();
			$class->name			= $className;
			$class->methods		= array();
			if( $options['skipAbstract'] )															//  abstract classes shall be skipped
				if( $classReflection->isAbstract() )												//  class is abstract
					continue;																		//  skip this class
			if( $options['reflectClass'] )															//  it is enabled to ...
				$class->reflection		= $classReflection;											//  store the class reflection object
			$classes[$controller]	= $class;

			if( !$options['readMethods'] )															//  do not read class methods
				continue;																			//  we're done here

			$methods	= $classReflection->getMethods( $options['methodFilter'] );
			foreach( $methods as $methodReflection ){
				$method	= new stdClass();
				$method->name		= $methodReflection->name;
				$method->class		= $methodReflection->getDeclaringClass()->getName();
				if( $options['skipInherited'] )														//  skipping inherited methods is enabled
					if( $method->class !== $className )												//  method is inherited
						continue;																	//  skip this method
				if( $options['skipFramework'] )														//  skipping framework methods is enabled
					if( substr( $method->class, 0, 4 ) == "CMF_" )									//  method is inherited from framework
						continue;																	//  skip this method
				if( $options['skipMagic'] )															//  skipping magic methods is enabled
					if( substr( $method->name, 0, 2 ) == "__" )										//  method is magic
						continue;																	//  skip this method
				if( $options['reflectMethod'] )														//  reflecting methods is enabled to
					$method->reflection	= $methodReflection;										//  store the method reflection object
				$method->parameters	= array();
				$class->methods[$method->name]	= $method;

				if( !$options['readParameters'] )													//  do not read method parameters
					continue;																		//  we're done here

				$parameters	= $methodReflection->getParameters();
				foreach( $parameters as $parameterReflection ){
					$parameter	= new stdClass();
					$parameter->name		= $parameterReflection->name;
					$method->parameters[$parameter->name]	= $parameter;
					if( $options['reflectParameter'] )
						$parameter->reflection	= $parameterReflection;
				}
			}
			ksort( $class->methods );
		}

		ksort( $classes );
		return $classes;
	}
}
?>
