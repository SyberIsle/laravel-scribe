<?php

/**
 * @file
 * Contains \SyberIsle\Laravel\Scribe\Model\Log
 */

namespace SyberIsle\Laravel\Scribe\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

/**
 * @property int|Uuid           $id
 * @property Model              $causer
 * @property int                $causerId
 * @property string             $causerType
 * @property mixed              $context
 * @property \DateTimeImmutable $createdOn
 * @property int                $level
 * @property string             $message
 * @property Model              $subject
 * @property int                $subjectId
 */
abstract class Log
	extends Model
{
	const CREATED_AT = 'created_on';

	/**
	 * @var string[] List of casts
	 */
	protected $casts = [
		'created_on' => 'immutable_datetime',
	];

	/**
	 * @var string[] List of studly case to snake case that could be used.
	 */
	private $keyMap = [
		'createdOn'  => 'created_on',
		'subjectId'  => 'subject_id',
		'causerId'   => 'causer_id',
		'causerType' => 'causer_type'
	];

	/**
	 * @var string
	 */
	protected $subjectModel;

	/**
	 * Overrides the parent to map the studly keys to their snake counterparts
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get($key)
	{
		return parent::__get($this->keyMap[$key] ?? $key);
	}

	/**
	 * @return MorphTo The causer
	 */
	public function causer(): MorphTo
	{
		return $this->morphTo('causer');
	}

	/**
	 * Override the parent to block updates.
	 *
	 * @param Builder $query
	 *
	 * @return mixed
	 */
	final protected function performUpdate(Builder $query)
	{
		throw new \RuntimeException("Updating logs is not allowed");
	}

	/**
	 * Overrides the parent to prevent setting the updatedAt Column
	 *
	 * @param mixed $value The value to set
	 *
	 * @return $this|Log
	 */
	final public function setUpdatedAt($value)
	{
		return $this;
	}

	/**
	 * Perform a query on the models ID
	 *
	 * @param Builder $query
	 * @param Model   $subject
	 *
	 * @return Builder
	 */
	public function scopeForSubject(Builder $query, Model $subject): Builder
	{
		return $query->where('subject_id', $subject->getKey());
	}

	/**
	 * @return BelongsTo The subject this log belogs to
	 */
	public function subject(): BelongsTo
	{
		return $this->belongsTo($this->subjectModel, 'subject_id');
	}

	/**
	 * Writes the log for the given subject
	 *
	 * @param Model                 $subject The subject to make the log for
	 * @param string                $message The log message
	 * @param int                   $level   The log level
	 * @param ?array<string, mixed> $context Associated Context
	 *
	 * @return $this
	 */
	final public function write(Model $subject, string $message, int $level = LOG_INFO, ?array $context = []): static
	{
		if (!($subject instanceof $this->subjectModel)) {
			throw new \BadMethodCallException("Subject must be {$this->subjectModel}");
		}

		$this->level   = $level;
		$this->message = $message;
		if ($causer = $context['causer'] ?? Auth::user()) {
			$this->causer()->associate($causer);
		}
		unset($context['causer']);

		if (!empty($context)) {
			$this->context = $context;
		}

		$this->subject()->associate($subject);
		if ($this->save()) {
			return $this;
		}

		throw new \RuntimeException("Unable to save the log");
	}
}