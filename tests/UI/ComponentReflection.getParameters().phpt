<?php declare(strict_types=1);

use Nette\Application\Attributes\{Parameter, Persistent};
use Nette\Application\UI\{ComponentReflection, Presenter};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class OnePresenter extends Presenter
{
	public static $no1;
	public $no2;

	#[Persistent]
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


class TypedPresenter extends Presenter
{
	#[Parameter]
	public string $required;

	#[Parameter]
	public ?string $nullable;

	#[Parameter]
	public array $list;

	#[Parameter]
	public iterable $iterable;

	#[Parameter]
	public string $default = 'def';
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
			'required' => false,
		],
	],
	(new ComponentReflection(OnePresenter::class))->getParameters(),
);

Assert::same(
	[
		'yes2' => [
			'def' => null,
			'type' => 'mixed',
			'required' => false,
		],
		'yes4' => [
			'def' => null,
			'type' => 'mixed',
			'required' => false,
		],
		'yes1' => [
			'def' => null,
			'type' => 'scalar',
			'since' => 'OnePresenter',
		],
		'yes3' => [
			'def' => null,
			'type' => 'mixed',
			'required' => false,
		],
	],
	(new ComponentReflection(TwoPresenter::class))->getParameters(),
);

Assert::same(
	[
		'required' => [
			'def' => null,
			'type' => 'string',
			'required' => true,
		],
		'nullable' => [
			'def' => null,
			'type' => '?string',
			'required' => false,
		],
		'list' => [
			'def' => [],
			'type' => 'array',
			'required' => false,
		],
		'iterable' => [
			'def' => [],
			'type' => 'iterable',
			'required' => false,
		],
		'default' => [
			'def' => 'def',
			'type' => 'string',
			'required' => false,
		],
	],
	(new ComponentReflection(TypedPresenter::class))->getParameters(),
);
