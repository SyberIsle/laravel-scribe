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
}