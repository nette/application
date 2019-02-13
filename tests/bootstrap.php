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


// output buffer level check
register_shutdown_function(function ($level): void {
	Tester\Assert::same($level, ob_get_level());
}, ob_get_level());


function test(\Closure $function): void
{
	$function();
	Mockery::close();
}


class Notes
{
	public static $notes = [];


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
