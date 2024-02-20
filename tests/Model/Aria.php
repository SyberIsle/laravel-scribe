<?php

namespace SyberIsle\Laravel\Scribe\Test\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use SyberIsle\Laravel\Scribe\Model\HasLogs;

class Aria
	extends Model
{
	use HasUuids;
	use HasLogs;

	protected $table    = 'arias';
	protected $fillable = ['name'];
}