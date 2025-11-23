<?php

declare(strict_types=1);

use Nette\Application\Attributes\Parameter;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\ComponentReflection;
use Nette\Application\UI\Presenter;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class OnePresenter extends Presenter
{
	public static $no1;
	public $no2;

	/** @persistent */
	public $yes1;

	#[Persistent, Parameter]
	public $yes2;

	#[Parameter]
	public $yes3;
}


class TwoPresenter extends OnePresenter
{
	#[Parameter]
	public $yes2;
	public $yes3;

	#[Parameter]
	public $yes4;
}


Assert::same(
	[
		'yes1' => [
			'def' => null,
			'type' => 'scalar',
			'since' => 'OnePresenter',
		],
		'yes2' => [
			'def' => null,
			'type' => 'scalar',
			'since' => 'OnePresenter',
		],
		'yes3' => [
			'def' => null,
			'type' => 'mixed',
		],
	],
	(new ComponentReflection(OnePresenter::class))->getParameters(),
);

Assert::same(
	[
		'yes2' => [
			'def' => null,
			'type' => 'mixed',
		],
		'yes4' => [
			'def' => null,
			'type' => 'mixed',
		],
		'yes1' => [
			'def' => null,
			'type' => 'scalar',
			'since' => 'OnePresenter',
		],
		'yes3' => [
			'def' => null,
			'type' => 'mixed',
		],
	],
	(new ComponentReflection(TwoPresenter::class))->getParameters(),
);
