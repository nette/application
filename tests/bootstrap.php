<?php

declare(strict_types=1);

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}


// configure environment
Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');
Mockery::setLoader(new Mockery\Loader\RequireLoader(getTempDir()));


// output buffer level check
register_shutdown_function(function ($level): void {
	Tester\Assert::same($level, ob_get_level());
}, ob_get_level());


function getTempDir(): string
{
	$dir = __DIR__ . '/tmp/' . getmypid();

	if (empty($GLOBALS['\\lock'])) {
		// garbage collector
		$GLOBALS['\\lock'] = $lock = fopen(__DIR__ . '/lock', 'w');
		if (rand(0, 100)) {
			flock($lock, LOCK_SH);
			@mkdir(dirname($dir));
		} elseif (flock($lock, LOCK_EX)) {
			Tester\Helpers::purge(dirname($dir));
		}

		@mkdir($dir);
	}

	return $dir;
}


function test(string $title, Closure $function): void
{
	$function();
	Mockery::close();
}


class Notes
{
	public static array $notes = [];


	public static function add($message): void
	{
		self::$notes[] = $message;
	}


	public static function fetch(): array
	{
		$res = self::$notes;
		self::$notes = [];
		return $res;
	}
}
