<?php

/**
 * @file
 * Contains \SyberIsle\Laravel\Scribe\Console\MakeModelLog
 */

namespace SyberIsle\Laravel\Scribe\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Makes the {Model}Log
 */
class MakeModelLog
	extends GeneratorCommand
{
	// common properties
	protected $name        = "make:scribe:model";
	protected $description = "Add a log model to a model";
	protected $type        = 'Model';

	/**
	 * @var string
	 */
	protected $namespace;

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * Execute the console command
	 *
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	public function handle(): ?bool
	{
		// ensure the model exists...
		if (!class_exists($this->argument('model'))) {
			throw new \RuntimeException("Class does not exist: {$this->argument('model')}");
		}

		$this->namespace = $this->getNamespace($this->argument('model'));
		$this->table     = Str::snake(Str::pluralStudly(class_basename($this->getNameInput())));

		if (parent::handle() === false && !$this->option('force')) {
			return false;
		}

		if ($this->option('migration')) {
			$params = [
				'name'     => "create_{$this->table}_table",
				'--create' => $this->table,
			];

			if ($this->option('uuid')) {
				$params['--uuid'] = true;
			}

			if (in_array(HasUuids::class, class_uses_recursive($this->argument('model')))) {
				$params['--subject-uuid'] = true;
			}

			if ($this->option('causer-uuid')) {
				$params['--causer-uuid'] = true;
			}

			$this->call('make:scribe:migration', $params);
		}

		return false;
	}

	/**
	 * Overrides the parent to handle the log_table and parent_model substitutions
	 *
	 * @param string $name The name of the clas to build
	 *
	 * @return array|string|string[]
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	protected function buildClass($name)
	{
		$stub = parent::buildClass($name);
		$stub = str_replace(['DummyLogTable', '{{ log_table }}', '{{log_table}}'], $this->table, $stub);
		$stub = str_replace(['DummyParent', '{{ parent_model }}', '{{parent_model}}'], $this->argument('model'), $stub);

		return $stub;
	}

	/**
	 * @return mixed[] List of arguments to the command
	 */
	protected function getArguments()
	{
		return [
			['model', InputArgument::REQUIRED, 'The name of the Model to add Journal to'],
		];
	}

	/**
	 * @return string Takes the model name and appends Log to it
	 */
	protected function getNameInput()
	{
		return trim($this->argument('model')) . 'Log';
	}

	/**
	 * @return mixed[] List of available options for this command
	 */
	protected function getOptions()
	{
		return [
			['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the model log already exists'],
			[
				'migration',
				null,
				InputOption::VALUE_NEGATABLE,
				'Do not create the migration file for the model log',
				true
			],
			// we don't worry about subject-uuid, as we check the model
			['uuid', 'u', InputOption::VALUE_NONE, 'Use UUID as the primary key'],
			['causer-uuid', null, InputOption::VALUE_NONE, 'Use UUID for the causer']
		];
	}

	/**
	 * @param string $name The name of the model to create
	 *
	 * @return string The path to where the model should live
	 * @throws \ReflectionException
	 */
	protected function getPath($name)
	{
		$rc   = new \ReflectionClass($this->argument('model'));
		$name = Str::replaceFirst($this->rootNamespace(), '', $name);

		return dirname($rc->getFileName()) . '/' . str_replace('\\', '/', $name) . '.php';
	}

	/**
	 * @return string The stub file to use
	 */
	protected function getStub()
	{
		$file = 'model' . ($this->option('uuid') ? '.uuid' : '');

		return __DIR__ . "/../../stubs/{$file}.stub";
	}

	/**
	 * @return string The Model's namespace, may not necessarily be `App\Model`
	 */
	protected function rootNamespace()
	{
		return $this->namespace;
	}
}