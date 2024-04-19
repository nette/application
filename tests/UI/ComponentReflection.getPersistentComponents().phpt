<?php

declare(strict_types=1);

use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Presenter;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class NonePresenter extends Presenter
{
}


/**
 * @persistent(a, b)
 */
class AnnotationPresenter extends Presenter
{
}


#[Persistent('a', 'b')]
class AttributePresenter extends Presenter
{
}


class MethodPresenter extends AttributePresenter
{
	public static function getPersistentComponents(): array
	{
		return ['c'];
	}
}


Assert::same([], NonePresenter::getReflection()->getPersistentComponents());

Assert::same([
	'a' => ['since' => AnnotationPresenter::class],
	'b' => ['since' => AnnotationPresenter::class],
], AnnotationPresenter::getReflection()->getPersistentComponents());

Assert::same([
	'a' => ['since' => AttributePresenter::class],
	'b' => ['since' => AttributePresenter::class],
], AttributePresenter::getReflection()->getPersistentComponents());

Assert::same([
	'a' => ['since' => AttributePresenter::class],
	'b' => ['since' => AttributePresenter::class],
	'c' => ['since' => MethodPresenter::class],
], MethodPresenter::getReflection()->getPersistentComponents());
