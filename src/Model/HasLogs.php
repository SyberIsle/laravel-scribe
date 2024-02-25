<?php

/**
 * @file
 * Contains \SyberIsle\Laravel\Scribe\Model\HasLogs
 */

namespace SyberIsle\Laravel\Scribe\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait to allow models to retrieve their logs and make a log entry
 */
trait HasLogs
{
	/**
	 * Allows usage of the $logModel property in the composed class while maintaining a simple cache of the log model
	 * name without interfering with HasAttributes
	 *
	 * @var string
	 */
	private string $__logModel; // phpcs:ignore -- deliberately using __ to indicate it's a special trait property

	/**
	 * Interfaces with the HasAttributes trait
	 *
	 * @return string The log model class
	 */
	public function getLogModelAttribute(): string
	{
		return $this->logModel();
	}

	/**
	 * Logs the message
	 *
	 * @param string                $message The message to log
	 * @param int                   $level   The level of the message
	 * @param ?array<string, mixed> $context Additional context
	 *
	 * @return Log
	 */
	public function log(string $message, int $level = LOG_INFO, ?array $context = []): Log
	{
		return ($this->logModel())::make()->write($this, $message, $level, $context);
	}

	/**
	 * @return string The Log model to use
	 */
	public function logModel(): string
	{
		return $this->__logModel ??= property_exists($this, 'logModel') ? $this->logModel : self::class . 'Log';
	}

	/**
	 * @return HasMany The associated logs
	 */
	public function logs(): HasMany
	{
		return $this->hasMany($this->logModel(), 'subject_id');
	}
}