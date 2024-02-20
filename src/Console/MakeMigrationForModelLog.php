<?php

/**
 * @file
 * Contains \SyberIsle\Laravel\Scribe\Console\MakeMigrationForModelLog
 */

namespace SyberIsle\Laravel\Scribe\Console;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Support\Facades\File;

/**
 * There might be a better way to copy the migration since we are generating.
 */
class MakeMigrationForModelLog
	extends MigrateMakeCommand
{
	// necessary to customize the name of the migration command
	protected $signature = 'make:scribe:migration
		{name : The name of the log to create the }
		{--create= : The table to be created}
		{--table= : The table to migrate}
		{--uuid : Whether to use UUID for the log}
		{--subject-uuid : Whether to use UUID for the Subject}
		{--causer-uuid : Whether to use UUID for the Causer}
		{--path= : The location where the migration file should be created}
		{--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}';

	/**
	 * {@inheritDoc}
	 */
	protected function writeMigration($name, $table, $create)
	{
		$name = "create_{$table}_table";

		// Fix an issue where the MigrationCreator doesn't error on "named" migrations, just classes
		foreach (File::files(database_path('migrations')) as $file) {
			if (str_ends_with($file->getBasename('.php'), $name)) {
				throw new \InvalidArgumentException("Migration for `$name` already exists");
			}
		}

		// We can't use parent::writeMigration as we need the file to perform further modifications
		$file = $this->creator->create($name, $this->getMigrationPath(), $table, $create);

		File::put(
			$file,
			str_replace(
				['{{ id_type }}', '{{ subject_type }}', '{{ causer_type }}'],
				[
					$this->option('uuid') ? 'uuid' : 'bigIncrements',
					$this->option('subject-uuid') ? 'uuid' : 'bigInteger',
					$this->option('causer-uuid') ? 'nullableUuidMorphs' : 'nullableMorphs',
				],
				File::get($file)
			)
		);

		$this->components->info(sprintf('Migration [%s] created successfully.', $file));

		return $file;
	}
}