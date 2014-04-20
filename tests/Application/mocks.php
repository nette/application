<?php


class MockSession extends Nette\Http\Session
{
	public $mockSection;

	public $sectionName;

	public function __construct() {}

	public function getSection($section, $class = 'Nette\Http\SessionSection')
	{
		return $this->mockSection;
	}

	public function hasSection($section)
	{
		return $section === $this->sectionName;
	}
}


class MockSessionSection extends Nette\Object implements \ArrayAccess
{
	public $name;
	public $value;

	public function __isset($name)
	{
		return $name === $this->name;
	}

	public function __set($name, $value)
	{
		$this->name = $name;
		$this->value = $value;
	}

	public function &__get($name)
	{
		return $this->value;
	}

	public function setExpiration($expiraton, $variables = NULL)
	{}

	public function offsetExists($name)
	{
		return $this->__isset($name);
	}

	public function offsetSet($name, $value)
	{
		$this->__set($name, $value);
	}

	public function offsetGet($name)
	{
		return $this->__get($name);
	}

	public function offsetUnset($name)
	{
		$this->__unset($name);
	}
}


class MockUser extends Nette\Security\User
{
	public function __construct()
	{}
}


class MockPresenterFactory extends Nette\Object implements Nette\Application\IPresenterFactory
{
	function getPresenterClass(& $name)
	{
		return str_replace(':', 'Module\\', $name) . 'Presenter';
	}

	function createPresenter($name)
	{}
}


class MockRouter extends Nette\Object implements Nette\Application\IRouter
{
	function match(Nette\Http\IRequest $httpRequest)
	{}

	function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl)
	{}
}


class MockHttpRequest extends Nette\Http\Request
{
	public function __construct()
	{}
}


class MockMessagesStorage extends Nette\Application\MessagesStorage
{
	public function __construct()
	{}
}


class MockTemplateFactory extends Nette\Bridges\ApplicationLatte\TemplateFactory
{
	public function __construct()
	{}
}
