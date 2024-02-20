<?php

namespace SyberIsle\Laravel\Scribe\Test\Console;

use Illuminate\Support\Facades\File;
use SyberIsle\Laravel\Scribe\Test\TestCase;

class MakeMigrationForJournalTest
	extends TestCase
{
	public function testMigrationFailsWhenExists()
	{
		File::put($this->expectedFile = database_path('migrations/20230219_000000_create_test_exists_table.php'), '');

		self::expectException(\InvalidArgumentException::class);
		self::expectExceptionMessage("Migration for `create_test_exists_table` already exists");
		$this->artisan(
			'make:scribe:migration',
			[
				'name'     => 'SyberIsle\Laravel\Scribe\Test\Model\TestLog',
				'--create' => 'test_exists'
			]
		)->run(); // no point in running assertions
	}

	public function testBasicMigration()
	{
		$this->artisan(
			'make:scribe:migration',
			[
				'name'     => 'SyberIsle\Laravel\Scribe\Test\Model\TestLog',
				'--create' => 'test_logs'
			]
		)->assertSuccessful();

		$this->expectedFile = $this->findMigration('create_test_logs_table');

		self::assertNotFalse($this->expectedFile);
	}

	public function provideUuidTypes()
	{
		return [
			['uuid', "uuid('id')"],
			['subject-uuid', "uuid('subject_id')"],
			['causer-uuid', "nullableUuidMorphs('causer', 'causer')"],
		];
	}

	/**
	 * @dataProvider provideUuidTypes
	 */
	public function testMigrationSetsUuids($type, $expected)
	{
		$this->artisan(
			'make:scribe:migration',
			[
				'name'      => 'SyberIsle\Laravel\Scribe\Test\Model\TestLog',
				'--create'  => 'test_logs',
				"--{$type}" => true
			]
		)->assertSuccessful();

		$this->expectedFile = $this->findMigration('create_test_logs_table');
		self::assertTrue(str_contains(File::get($this->expectedFile), $expected));
	}
}