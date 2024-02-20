<?php

/**
 * @file
 * Contains \SyberIsle\Laravel\Scribe\ServiceProvider
 */

namespace SyberIsle\Laravel\Scribe;

use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use SyberIsle\Laravel\Scribe\Console\MakeMigrationForModelLog;

final class ServiceProvider
	extends \Illuminate\Support\ServiceProvider
{
	public function boot(): void
	{
		if ($this->app->runningInConsole() || $this->app->runningUnitTests()) {
			$this->app->singleton(MakeMigrationForModelLog::class, function (Application $app) {
				return new MakeMigrationForModelLog(
					new MigrationCreator($app->get(Filesystem::class), realpath(__DIR__ . '/../stubs')),
					$app->get('composer')
				);
			});

			$this->commands(
				[
					Console\MakeModelLog::class,
					MakeMigrationForModelLog::class
				]
			);
		}
	}
}