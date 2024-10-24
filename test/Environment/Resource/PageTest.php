<?php

declare(strict_types=1);

namespace CeusMedia\HydrogenFrameworkTest\Environment\Resource;

use CeusMedia\Common\Loader;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Captain;
use CeusMedia\HydrogenFramework\Environment\Resource\Page;
use PHPUnit\Framework\TestCase;

use Logic_Example;
use DomainException;

/**
 *	@coversDefaultClass	\CeusMedia\HydrogenFramework\Environment\Resource\Page
 */
class PageTest extends TestCase
{
	/** @dataProvider provideLoadLevelsMap */
	public function testInterpretLoadLevel( int $expectedResult, string|bool|int|NULL $input ): void
	{
		self::assertSame( $expectedResult, Page::interpretLoadLevel( $input ) );
	}

	//  --  DATA PROVIDERS  --  //
	public static function provideLoadLevelsMap(): array
	{
		return [
			'string: top, head, start'	=> [
				Captain::LEVEL_TOP, 'top', 'head', 'start',
			],
			'string: end, bottom, tail'	=> [
				Captain::LEVEL_END, 'end', 'bottom', 'tail',
			],
			'string: center, normal, default, unknown'	=> [
				Captain::LEVEL_MID, 'center', 'normal', 'default', 'unknown'
			],
			'string: highest'	=> [
				Captain::LEVEL_HIGHEST, 'highest',
			],
			'string: high'	=> [
				Captain::LEVEL_HIGH, 'high',
			],
			'string: higher'	=> [
				Captain::LEVEL_HIGHER, 'higher',
			],
			'string: lower'	=> [
				Captain::LEVEL_LOWER, 'lower',
			],
			'string: low'	=> [
				Captain::LEVEL_LOW, 'low',
			],
			'string: lowest'	=> [
				Captain::LEVEL_LOWEST, 'lowest',
			],
			'string: invalid'	=> [
				Captain::LEVEL_MID, 'invalid',
			],
			'string: invalid: 0'	=> [
				Captain::LEVEL_MID, '0',
			],
			'string: invalid: untrimmed whitespace'	=> [
				Captain::LEVEL_LOW, ' low', 'low ', 'low  ',
			],
			'string: 3'	=> [
				Captain::LEVEL_HIGH, '3',
			],
			'int: 2'	=> [
				Captain::LEVEL_HIGHEST, 2,
			],
			'int: invalid: 0'	=> [
				Captain::LEVEL_MID, 0,
			],
			'boolean: TRUE'	=> [
				Captain::LEVEL_HIGH, TRUE,
			],
			'boolean: FALSE'	=> [
				Captain::LEVEL_LOW, FALSE,
			],
			'empty string'	=> [
				Captain::LEVEL_MID, '',
			],
			'NULL'	=> [
				Captain::LEVEL_MID, NULL,
			],
		];
	}
}