<?php

/**
 * @file
 * Contains \SyberIsle\Laravel\Scribe\Model\HasLogs
 */

namespace SyberIsle\Laravel\Scribe\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * Trait to allow models to retrieve their logs and make a log entry
 */
trait HasLogs
{
	/**
	 * Logs the message
	 *
	 * @param string $message The message to log
	 * @param int    $level   The level of the message.
	 *
	 * @return Log
	 */
	public function log(string $message, int $level = LOG_INFO, $context = null): Log
	{
		$class = $this->resolveLogModel();
		$log   = new $class;

		$log->level   = $level;
		$log->message = $message;
		if ($context) {
			$log->context = $context;
		}

		if (Auth::hasUser()) {
			$log->causer()->associate(Auth::user());
		}

		$log->subject()->associate($this);
		$log->save();

		return $log;
	}

	/**
	 * @return HasMany The associated logs
	 */
	public function logs(): HasMany
	{
		return $this->hasMany($this->resolveLogModel(), 'subject_id');
	}

	/**
	 * @return string The resolved name of the log model in use
	 */
	private function resolveLogModel(): string
	{
		return $this->logModel ?? ($this->logModel = self::class . 'Log');
	}
}