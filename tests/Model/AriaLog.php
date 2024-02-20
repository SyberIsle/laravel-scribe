<?php

namespace SyberIsle\Laravel\Scribe\Test\Model;

use SyberIsle\Laravel\Scribe\Model\Log;

class AriaLog
	extends Log
{
	protected $table        = 'aria_logs';
	protected $subjectModel = Aria::class;
}