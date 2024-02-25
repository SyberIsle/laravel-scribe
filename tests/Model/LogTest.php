<?php

namespace SyberIsle\Laravel\Scribe\Test\Model;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use SyberIsle\Laravel\Scribe\Test\TestCase;

class LogTest
	extends TestCase
{
	use RefreshDatabase;

	protected $a1;
	protected $a2;

	public function setUpDatabase()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->timestamps();
		});
		Schema::create('arias', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->string('name');
			$table->timestamps();
		});
		Schema::create('aria_logs', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->timestamp('created_on');
			$table->integer('level')->default(LOG_INFO);
			$table->uuid('subject_id');
			$table->nullableMorphs('causer', 'causer');
			$table->string('message');
			$table->json('context')->nullable();
		});

		$this->a1 = Aria::create(['name' => 'kakaw']);
		$this->a1->log('started');
		$this->a1->log('done');

		$this->a2 = Aria::create(['name' => 'poi']);
		$this->a2->log('started');
		$this->a2->log('rejected');
	}

	public function testLog()
	{
		self::assertCount(4, AriaLog::all());
	}

	public function testScopeForSubject()
	{
		self::assertCount(2, AriaLog::forSubject($this->a1)->get());
	}

	public function testSubject()
	{
		self::assertSame($this->a1->id, AriaLog::find(1)->subject->id);
	}

	public function testCauserIsPulledFromAuthSetUser()
	{
		$uc = new class
			extends User {
			protected $fillable = ['name'];
			protected $table    = 'users';
		};

		Auth::setUser($uc::create(['name' => 'test']));

		$a3 = Aria::create(['name' => 'poi']);
		$a3->log('started');

		$l3 = AriaLog::forSubject($a3)->get();
		self::assertEquals('test', $l3[0]->causer->name);

		// done here as we already have it, and the others are null
		return [$uc, $l3[0]];
	}

	/**
	 * @depends testCauserIsPulledFromAuthSetUser
	 */
	public function testCauserAttributesWithoutUnderscore($args)
	{
		[$userClass, $log] = $args;
		self::assertEquals(1, $log->causerId);
		self::assertEquals(get_class($userClass), $log->causerType);
	}

	public function testCreatedOnAttributeWithoutUnderscore()
	{
		self::assertInstanceOf(\DateTimeImmutable::class, AriaLog::forSubject($this->a1)->get()[0]->createdOn);
	}

	public function testSubjectIdAttributeWithoutUnderscore()
	{
		self::assertEquals($this->a1->id, AriaLog::forSubject($this->a1)->get()[0]->subjectId);
	}

	public function testWriteThrowsWithBadLogModel()
	{
		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage("Subject must be " . Aria::class);
		AriaLog::make()->write(new Test(), "will throw", LOG_EMERG);
	}

	public function testWriteLogs()
	{
		$subject     = new Aria();
		$subject->id = 1;
		self::assertInstanceOf(
			AriaLog::class,
			AriaLog::make()->write($subject, "kakaw")
		);
	}

	public function testWriteThrowsWhenCouldNotSave()
	{
		AriaLog::saving(function () {
			return false;
		});

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage("Unable to save the log");

		AriaLog::make()->write(Aria::make(), "kakaw");
	}
}