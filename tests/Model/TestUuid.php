<?php

namespace SyberIsle\Laravel\Scribe\Test\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TestUuid
	extends Model
{
	use HasUuids;
}