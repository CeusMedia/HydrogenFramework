<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkUnitTest;

use CeusMedia\Common\Exception\Data\Missing as DataMissingException;
use CeusMedia\Common\Exception\NotSupported as NotSupportedException;
use CeusMedia\HydrogenFramework\Entity;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\View
 */
class EntityTest extends TestCase
{
	public function testConvertTypeAccordingToReflection(): void
	{
		$reflectedClass		= new ReflectionClass( TestEntity::class );
		$reflectedMethod	= $reflectedClass->getMethod( 'convertTypeAccordingToReflection' );
		$reflectedProperty	= $reflectedClass->getProperty( 'simpleInteger' );

		$result = $reflectedMethod->invoke( null, "1", $reflectedProperty );
		$this->assertSame( 1, $result );

		$result = $reflectedMethod->invoke( null, 1, $reflectedProperty );
		$this->assertSame( 1, $result );

		$result = $reflectedMethod->invoke( null, 1.1, $reflectedProperty );
		$this->assertSame( 1, $result );
	}

	public function testConvertTypeAccordingToReflection_throwsReflectionException_onInvalidPropertyName(): void
	{
		$this->expectException( ReflectionException::class );
		$this->expectExceptionMessageMatches( '/Property .+invalidPropertyName does not exist/' );
		$reflectedClass		= new ReflectionClass( TestEntity::class );
		$reflectedMethod	= $reflectedClass->getMethod( 'convertTypeAccordingToReflection' );
		$reflectedProperty	= $reflectedClass->getProperty( 'invalidPropertyName' );
	}

	public function testConvertTypeAccordingToReflection_throwsNotSupportedException_union(): void
	{
		$this->expectException( NotSupportedException::class );
		$reflectedClass		= new ReflectionClass( TestEntity::class );
		$reflectedMethod	= $reflectedClass->getMethod( 'convertTypeAccordingToReflection' );
		$reflectedProperty	= $reflectedClass->getProperty( 'unionType' );
		$result = $reflectedMethod->invoke( null, "1", $reflectedProperty );
	}

	public function testCheckMandatoryFields_throwsDataMissingException(): void
	{
		$this->expectException( DataMissingException::class );
		$this->expectExceptionMessage( 'Missing data for key "unionType"' );
		$reflectedClass		= new ReflectionClass( TestEntity::class );
		$reflectedMethod	= $reflectedClass->getMethod( 'checkMandatoryFields' );
		$reflectedMethod->invoke( $reflectedClass, [] );
	}
}

class TestEntity extends Entity
{
	public int $simpleInteger	= 0;
	public int|string $unionType	= 0;

	protected static array $autoTypeConvertFields	= [
		'simpleInteger',
		'unionType',
	];

	protected static array $mandatoryFields			= [
		'unionType',
	];
}