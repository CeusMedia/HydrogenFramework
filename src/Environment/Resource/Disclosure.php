<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
namespace CeusMedia\HydrogenFramework\Environment\Resource;

use CeusMedia\HydrogenFramework\Deprecation;
use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileFilter;
use ReflectionClass;
use ReflectionMethod;
use SplFileObject;
use stdClass;

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2025 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 *	@deprecated		use module Resource_Disclosure instead
 *	@todo			remove in version 0.9
 */
class Disclosure
{
	protected array $options	= [
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
		'skipFramework'		=> TRUE,
		'methodFilter'		=> ReflectionMethod::IS_PUBLIC
	];

	public function __construct( array $options = [] )
	{
		Deprecation::getInstance()
			->setErrorVersion( '0.8.8' )
			->setExceptionVersion( '0.9' )
			->message( 'Use module Resource_Disclosure instead' );
		$this->options	= array_merge( $this->options, $options );
	}

	public function reflect( string $path, array $options = [] ): array
	{
		$options	= array_merge( $this->options, $options );

		$classes	= [];
		$path		= realpath( $path );
		$index		= new RecursiveRegexFileFilter( $path, '/^[^_].+\.'.$options['fileExtension'].'$/' );
		/** @var SplFileObject $entry */
		foreach( $index as $entry ){
			$fileName	= preg_replace( '@^'.$path.'/@', '', $entry->getPathname() );
			$fileBase	= preg_replace( '@\.'.$options['fileExtension'].'$@', '', $fileName );
			$controller	= str_replace( '/', '_', $fileBase );
			$className	= $options['classPrefix'].$controller;

			if( !class_exists( $className ) )
				continue;
			$classReflection	= new ReflectionClass( $className );
			$class	= new stdClass();
			$class->name			= $className;
			$class->methods		= [];
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
					if( str_starts_with( $method->class, 'CMF_' ) )									//  method is inherited from framework
						continue;																	//  skip this method
				if( $options['skipMagic'] )															//  skipping magic methods is enabled
					if( str_starts_with( $method->name, '__' ) )										//  method is magic
						continue;																	//  skip this method
				if( $options['reflectMethod'] )														//  reflecting methods is enabled to
					$method->reflection	= $methodReflection;										//  store the method reflection object
				$method->parameters	= [];
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
