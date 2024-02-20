<?php

namespace SyberIsle\Laravel\Scribe\Test\Console;

use Illuminate\Support\Facades\File;
use SyberIsle\Laravel\Scribe\Console\MakeModelLog;
use SyberIsle\Laravel\Scribe\Test\Model\Test;
use SyberIsle\Laravel\Scribe\Test\Model\TestUuid;
use SyberIsle\Laravel\Scribe\Test\TestCase;

class MakeJournalForModelTest
	extends TestCase
{
	protected $expectedFile = __DIR__ . '/../Model/TestLog.php';

	public function testFailsOnNonExistentClass()
	{
		self::expectException(\RuntimeException::class);
		$this->artisan(MakeModelLog::class, ['model' => 'Test'])->assertSuccessful();
	}

	public function testBasicModelLogIsCreated()
	{
		$this->artisan(MakeModelLog::class, ['model' => Test::class])->assertSuccessful();
		self::assertFileExists($this->expectedFile);
	}

	public function testModelLogIsCreatedWithUuids()
	{
		$this->artisan(
			MakeModelLog::class,
			[
				'model'  => Test::class,
				'--uuid' => true,
			]
		)->assertSuccessful();

		$data = File::get($this->expectedFile);
		self::assertStringContainsString('use HasUuids;', $data, 'Model not created with UUIDs');
	}

	public function testCreatesMigration()
	{
		$this->artisan(
			MakeModelLog::class,
			[
				'model'       => Test::class,
				'--migration' => true
			]
		)->assertSuccessful();

		self::assertNotFalse($file = $this->findMigration('create_test_logs_table'));
		unlink($file);
	}

	public function testCreatesMigrationWithUuids()
	{
		$this->expectedFile = __DIR__ . '/../Model/TestUuidLog.php';
		$this->artisan(
			MakeModelLog::class,
			[
				'model'         => TestUuid::class,
				'--migration'   => true,
				'--uuid'        => true,
				// subject-uui is deteremined based of the model
				'--causer-uuid' => true
			]
		)->assertSuccessful();

		self::assertNotFalse($file = $this->findMigration('create_test_uuid_logs_table'));
		$data = File::get($file);

		self::assertStringContainsString("uuid('id')", $data);
		self::assertStringContainsString("uuid('subject_id')", $data);
		self::assertStringContainsString("nullableUuidMorphs('causer', 'causer')", $data);
		unlink($file);
	}
}