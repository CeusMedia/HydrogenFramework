<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
declare(strict_types=1);

namespace CeusMedia\HydrogenFramework\Controller;

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Text\CamelCase;
use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\HydrogenFramework\Model;
use CeusMedia\HydrogenFramework\View;
use DomainException;
use ReflectionException;
use RuntimeException;

/**
 * @property Environment $env;
 */
abstract class Abstraction
{
	public static string $moduleId			= '';
	public static string $prefixModel		= 'Model_';
	public static string $prefixView		= 'View_';

	/**	@var	string						$alias			Optional alternative path for restarting */
	public string $alias					= '';

	/**	@var	string						$controller		Name of called Controller */
	protected string $controller			= '';

	/**	@var	string						$action			Name of called Action */
	protected string $action				= '';

	/**	@var	bool						$logRestarts	Flag: Log redirections */
	protected bool $logRestarts				= FALSE;

	/**	@var	bool						$redirect		Flag for Redirection */
	public bool $redirect					= FALSE;

	/**	@var	View|NULL					$view			View instance for controller */
	protected ?View $view					= NULL;

	/**	@var	Dictionary					$moduleConfig	Map of module configuration pairs */
	protected Dictionary $moduleConfig;

	/**
	 *	Set activity of logging of restarts.
	 *	@access		public
	 *	@param		boolean		$log		Flag: Activate logging of restarts (default)
	 *	@return		self
	 */
	public function setLogRestarts( bool $log = TRUE ): self
	{
		$this->logRestarts	= $log;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@param		string		$key
	 *	@param		mixed		$value
	 *	@param		string|NULL	$topic
	 *	@return		self
	 */
	protected function addData( string $key, mixed $value, string $topic = NULL ): self
	{
		$this->view?->setData( [$key => $value], $topic );
		return $this;
	}

	/**
	 *	@param		string		$resource
	 *	@param		string		$event
	 *	@param		object|NULL	$context
	 *	@param		array		$payload
	 *	@return		bool|NULL
	 *	@throws		ReflectionException
	 */
	protected function callHook( string $resource, string $event, ?object $context, array & $payload ): ?bool
	{
		if( isset( $this->env ) && $this->env instanceof Environment )
			return $this->env->getCaptain()->callHook( $resource, $event, $context ?? $this, $payload );
		return NULL;
	}

	/**
	 *	Tries to find logic class for short logic key and returns instance.
	 *	This protected method can be used within your custom controller to load logic classes.
	 *	Example: $this->getLogic( 'mailGroupMember' ) for instance of class 'Logic_Mail_Group_Member'
	 *
	 *	If no short logic key is given, the logic pool resource of environment will be returned.
	 *	So, you can use $this->getLogic() as shortcut for $this->env->getLogic().
	 *
	 *	@access		protected
	 *	@param		string		$key		Key for logic class (ex: 'mailGroupMember' for 'Logic_Mail_Group_Member')
	 *	@return		Logic					Logic instance or logic pool if no key given
	 *	@throws		RuntimeException		if no logic class could be found for given short logic key
	 *	@throws		DomainException
	 *	@throws		ReflectionException
	 */
	protected function getLogic( string $key ): Logic
	{
//		if( is_null( $key ) || !strlen( trim( $key ) ) )
//			return $this->env->getLogic();
		if( !isset( $this->env ) || !$this->env instanceof Environment )
			throw new RuntimeException( 'No environment with logic pool available' );

		/** @var Logic $logic */
		$logic	= $this->env->getLogic()->get( $key );
		return $logic;
	}

	/**
	 *	Tries to find model class for short model key and returns instance.
	 *	@access		protected
	 *	@param		string		$key		Key for model class (eG. 'mailGroupMember' for 'Model_Mail_Group_Member')
	 *	@return		Model					Model instance
	 *	@throws		RuntimeException		if no model class could be found for given short model key
	 *	@throws		ReflectionException
	 *	@todo		create model pool environment resource and apply to created shared single instances instead of new instances
	 *	@see		duplicate code with Logic::getModel
	 */
	protected function getModel( string $key ): Model
	{
		if( !isset( $this->env ) || !$this->env instanceof Environment )
			throw new RuntimeException( 'No environment available' );

		if( preg_match( '/^[A-Z][A-Za-z0-9_]+$/', $key ) )
			$className	= self::$prefixModel.$key;
		else{
			$classNameWords	= ucwords( CamelCase::decode( $key ) );
			$className		= str_replace( ' ', '_', 'Model '.$classNameWords );
		}
		if( !class_exists( $className ) )
			throw new RuntimeException( 'Model class "'.$className.'" not found' );
		/** @var Model $model */
		$model	= ObjectFactory::createObject( $className, [$this->env] );
		return $model;
	}

	/**
	 *	Loads View Class of called Controller.
	 *	@access		protected
	 *	@param		string|NULL		$section	Section in locale file
	 *	@param		string|NULL		$topic		Locale file key, e.g. test/my, default: current controller
	 *	@return		array
	 */
	protected function getWords( string $section = NULL, string $topic = NULL ): array
	{
		if( empty( $topic )/* && $this->env->getLanguage()->hasWords( $this->controller )*/ )
			$topic = $this->controller;
		if( empty( $section ) )
			return $this->env->getLanguage()->getWords( $topic );
		return $this->env->getLanguage()->getSection( $topic, $section );
	}
}