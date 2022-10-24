<?php declare(strict_types=1);

/**
 * Test: Nette\Application\UI\Component::loadState() and missing parameters
 */

use Nette\Application;
use Nette\Application\Attributes\{Parameter, Persistent};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
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
	public ?array $nullableList;

	#[Parameter]
	public string $default = 'def';

	#[Persistent]
	public ?int $persistent;
}


class RequiredPersistentPresenter extends Application\UI\Presenter
{
	#[Persistent]
	public int $page;
}


test('missing parameter without default value', function () {
	$presenter = new TestPresenter;
	Assert::exception(
		fn() => $presenter->loadState([]),
		Application\BadRequestException::class,
		"Missing parameter 'required' required by presenter .",
	);
});


test('passed parameters', function () {
	$presenter = new TestPresenter;
	$presenter->loadState(['required' => 'abc']);
	Assert::same('abc', $presenter->required);
	Assert::null($presenter->nullable);
	Assert::same([], $presenter->list);
	Assert::same([], $presenter->iterable);
	Assert::null($presenter->nullableList);
	Assert::same('def', $presenter->default);
	Assert::false(isset($presenter->persistent));
	Assert::same([
		'required' => 'abc',
		'nullable' => null,
		'list' => [],
		'iterable' => [],
		'nullableList' => null,
		'default' => 'def',
		'persistent' => null,
	], $presenter->getParameters());
});


test('initialized property', function () {
	$presenter = new TestPresenter;
	$presenter->required = 'xyz';
	$presenter->loadState([]);
	Assert::same('xyz', $presenter->required);
});


test('missing persistent parameter without default value', function () {
	$presenter = new RequiredPersistentPresenter;
	Assert::exception(
		fn() => $presenter->loadState([]),
		Application\BadRequestException::class,
		"Missing parameter 'page' required by presenter .",
	);

	$presenter->loadState(['page' => '2']);
	Assert::same(2, $presenter->page);
});
