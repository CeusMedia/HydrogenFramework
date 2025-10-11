<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkIntegrationTest;

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Entity;
use CeusMedia\HydrogenFramework\Model\Database\Table as TableModel;
use PHPUnit\Framework\TestCase;

/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\View
 */
class EntityTest extends TestCase
{
	public function testPresetStaticValues_onConstruct(): void
	{
		// Connect to the SQLite database
		$pdo = new \PDO('sqlite:EntityTest.sqlite');

		// Set error mode to exception
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		$stmt = $pdo->query('SELECT * FROM users WHERE id=2');
		$stmt->setFetchMode(\PDO::FETCH_CLASS, TestEntity::class);
		$userObject = $stmt->fetch();

		$entity	= new TestEntity( [] );
		$entity->set( 'id', 2 );
		$entity->set( 'username', 'jane_smith' );
		$entity->set( 'email', 'jane@example.com' );
		self::assertEquals( $entity, $userObject );
	}
}

class TestModel extends TableModel
{
	protected string $name = 'users';
	protected array $columns = [
		'id',
		'username',
		'email',
	];
	protected array $indices	= [
		'username',
		'email',
	];
	protected string $primaryKey	= 'id';

	protected int $fetchMode			= \PDO::FETCH_CLASS;
	protected ?string $fetchEntityClass = TestEntity::class;
}

class TestEntity extends Entity
{
	public int $id;
	public string $username;
	public string $email;

	protected static array $mandatoryFields			= [
		'username',
		'email',
	];

	protected static array $presetValues	= [
		'username'	=> 'jack_foo',
		'email'		=> 'unknown@nowhere.tld',
	];

	public function __construct( Dictionary|array $data = [] )
	{
		parent::__construct( $data );
	}
}