<?php

/**
 * Test: {control ...}
 */

use Nette\Bridges\ApplicationLatte\UIMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MockComponent
{
	function getComponent($name)
	{
		Notes::add(__METHOD__);
		Notes::add(func_get_args());
		return new MockControl;
	}

}


class MockControl
{

	function __call($name, $args)
	{
		Notes::add(__METHOD__);
		Notes::add(func_get_args());
	}

}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
UIMacros::install($latte->getCompiler());

$params['_control'] = new MockComponent;
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

{control form var1 => 5, 1, 2}
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
	'MockControl::__call', ['render', [['var1' => 5, 0 => 1, 1 => 2]]],
], Notes::fetch());
