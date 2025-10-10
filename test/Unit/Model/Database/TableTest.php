<?php
declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkUnitTest\Model\Database;

use CeusMedia\Database\PDO\Connection as DatabasePdoConnection;
use CeusMedia\HydrogenFramework\Environment\Console as ConsoleEnvironment;
use CeusMedia\HydrogenFramework\Model\Database\Table;
use PHPUnit\Framework\TestCase;
use Throwable;

class TableTest extends TestCase
{
	protected ConsoleEnvironment $env;

	public function testGetInstance(): void
	{
		try{
			$m1	= TestModel1::getInstance( $this->env );
			$m2	= TestModel2::getInstance( $this->env );
			self::assertInstanceOf( TestModel1::class, $m1 );
			self::assertInstanceOf( TestModel2::class, $m2 );
		}
		catch ( Throwable $e ){
			self::fail( $e->getMessage() );
		}
	}

	public function testGetInstance_withEntityClass(): void
	{
		$m	= TestModel3::getInstance( $this->env );
		self::assertEquals( \PDO::FETCH_CLASS, $m->getFetchMode() );
	}

	public function testGetInstance_withPrefix(): void
	{
		/** @var DatabaseResource $dbc */
		$dbc	= $this->env->get( 'database' );
		$dbc->getConnection()->setPrefix( 'test_' );

		$m	= TestModel3::getInstance( $this->env );
		self::assertEquals( \PDO::FETCH_CLASS, $m->getFetchMode() );
		self::assertEquals( 'test_t3', $m->getName() );
	}

	public function testGetInstance_exception_noDatabaseResource(): void
	{
		self::expectException( \RuntimeException::class );
		WrongModel1::getInstance( new ConsoleEnvironment( [
			'pathApp'	=> '',
			'uri'		=> $this->baseTestPath.'assets/app/',
			'isTest'	=> TRUE,
		] ) );
	}

	public function testGetInstance_exception_getConnectionMissingOnResource(): void
	{
		self::expectException( \RuntimeException::class );
		$env	= new ConsoleEnvironment( [
			'pathApp'	=> '',
			'uri'		=> $this->baseTestPath.'assets/app/',
			'isTest'	=> TRUE,
		] );
		$env->set( 'database', new \PDO( 'sqlite::memory:' ) );
		WrongModel1::getInstance( $env );
	}

	public function testGetInstance_exception_missingFetchClassName(): void
	{
		self::expectException( \RuntimeException::class );
		self::expectExceptionMessage( 'Cannot set set mode FETCH_CLASS without entity class name' );
		WrongModel2::getInstance( $this->env );
	}

	public function testGetInstance_exception_connectionNotPdo(): void
	{
		self::expectException( \RuntimeException::class );
		self::expectExceptionMessage( 'Set up database is not a fitting PDO connection' );
		$this->env->set( 'database', new WrongDatabaseResource() );
		TestModel3::getInstance( $this->env );
		self::assertTrue( TRUE );
	}

	protected function setUp(): void
	{
		$this->baseTestPath	= dirname( __DIR__, 3 ).'/';
		$this->env		= new ConsoleEnvironment( [
			'pathApp'	=> '',
			'uri'		=> $this->baseTestPath.'assets/app/',
			'isTest'	=> TRUE,
		] );

//		$dbc	= new PDO( $this->env );
		$dbc	= new DatabaseResource();
		$this->env->set( 'database', $dbc );
	}
}

class DatabaseConnection extends DatabasePdoConnection
{
	protected string $prefix = '';

	public function getPrefix(): string
	{
		return $this->prefix;
	}

	public function setPrefix( string $prefix ): self
	{
		$this->prefix	= $prefix;
		return $this;
	}
}

class DatabaseResource
{
	protected DatabaseConnection|\PDO $dbc;
	protected string $prefix;

	public function __construct()
	{
		$this->dbc	= new DatabaseConnection( 'sqlite::memory:' );
	}

	public function getConnection(): DatabaseConnection|\PDO
	{
		return $this->dbc;
	}

	public function getPrefix(): string
	{
		return $this->prefix;
	}

	public function setPrefix( string $prefix ): self
	{
		$this->prefix	= $prefix;
		return $this;
	}
}

class WrongDatabaseResource
{
	protected object $dbc;
	protected string $prefix;

	public function __construct()
	{
		$this->dbc	= new WrongDatabaseConnection();
	}

	public function getConnection(): object
	{
		return $this->dbc;
	}
}

class WrongDatabaseConnection
{

	public function getPrefix(): string
	{
		return 'wrong_';
	}
}

class TestModel1 extends Table
{
	protected string $name = 't1';
	protected array $columns = ['id'];
	protected string $primaryKey = 'id';
}
class TestModel2 extends Table
{
	protected string $name = 't2';
	protected array $columns = ['id'];
	protected string $primaryKey = 'id';
}

class TestModel3 extends Table
{
	protected string $name = 't3';
	protected array $columns = ['id'];
	protected string $primaryKey = 'id';
	protected int $fetchMode	= \PDO::FETCH_CLASS;
	protected ?string $className	= Table_Entity::class;

}

class WrongModel1 extends Table
{
}
class WrongModel2 extends Table
{
	protected string $name = 't2';
	protected array $columns = ['id'];
	protected string $primaryKey = 'id';
	protected int $fetchMode	= \PDO::FETCH_CLASS;
}
class Table_Entity
{
	public $id;
}