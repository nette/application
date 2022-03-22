<?php

/**
 * Test: {control ...}
 * @phpVersion 8.0
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


class MockComponent
{
	public function getComponent($name)
	{
		Notes::add(__METHOD__);
		Notes::add(func_get_args());
		return new MockControl;
	}
}


class MockControl
{
	public function __call($name, $args)
	{
		Notes::add(__METHOD__);
		Notes::add(func_get_args());
	}
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));
$latte->addProvider('uiControl', new MockComponent);

$params['form'] = new MockControl;
$params['name'] = 'form';

$latte->renderToString('
{control \'name\'}

{control form}

{control form:test}

{control $form:test}

{control $name:test}

{control $name:$name}

{control form var1}

{control form var1, 1, 2}

{control form wrap => 5, 1, 2}

{control form [var1 => 5], 1, 2}

{control form 5, 1, var1: 2}
', $params);

Assert::same([
	'MockComponent::getComponent', ['name'],
	'MockControl::__call', ['render', []],
	'MockComponent::getComponent', ['form'],
	'MockControl::__call', ['render', []],
	'MockComponent::getComponent', ['form'],
	'MockControl::__call', ['renderTest', []],
	'MockControl::__call', ['renderTest', []],
	'MockComponent::getComponent', ['form'],
	'MockControl::__call', ['renderTest', []],
	'MockComponent::getComponent', ['form'],
	'MockControl::__call', ['renderform', []],
	'MockComponent::getComponent', ['form'],
	'MockControl::__call', ['render', ['var1']],
	'MockComponent::getComponent', ['form'],
	'MockControl::__call', ['render', ['var1', 1, 2]],
	'MockComponent::getComponent', ['form'],
	'MockControl::__call', ['render', [['wrap' => 5, 1, 2]]],
	'MockComponent::getComponent', ['form'],
	'MockControl::__call', ['render', [['var1' => 5], 1, 2]],
	'MockComponent::getComponent', ['form'],
	'MockControl::__call', ['render', [5, 1, 'var1' => 2]],
], Notes::fetch());
