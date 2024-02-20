<?php

namespace SyberIsle\Laravel\Scribe\Test;

use Illuminate\Support\Facades\File;
use SyberIsle\Laravel\Scribe\ServiceProvider;

abstract class TestCase
	extends \Orchestra\Testbench\TestCase
{
	protected $expectedFile;

	public function setUp(): void
	{
		parent::setUp();

		$this->setUpDatabase();
	}

	public function setUpDatabase()
	{
	}

	public function tearDown(): void
	{
		foreach ((array)$this->expectedFile as $file) {
			if (file_exists($file)) {
				unlink($file);
			}
		}

		$migrationsPath = database_path('migrations');
		foreach (File::files($migrationsPath) as $file) {
			unlink($file->getRealPath());
		}

		parent::tearDown();
	}

	protected function findMigration(string $name)
	{
		foreach (File::files(database_path('migrations')) as $file) {
			if (str_ends_with($file->getBasename('.php'), $name)) {
				return database_path('migrations') . '/' . $file->getFilename();
			}
		}

		return false;
	}

	protected function getPackageProviders($app)
	{
		return [
			ServiceProvider::class
		];
	}
}