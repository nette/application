<?php


class MockSession extends Nette\Http\Session
{
	public $mockSection;
	public $mockFlashSection;
	public $mockFlashId;

	public function getSection($section, $class = 'Nette\Http\SessionSection')
	{
		return $this->mockSection;
	}

	public function getFlashId()
	{
		return $this->mockFlashId;
	}

	public function getFlashSection($section)
	{
		$this->mockFlashId = 'x';
		return $this->mockFlashSection;
	}

}


class MockSessionSection extends Nette\Object implements \ArrayAccess
{
	public $data;

	public function __isset($name)
	{
		return isset($this->data[$name]);
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function &__get($name)
	{
		return $this->data[$name];
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
	public $mockIdentity;

	public function __construct()
	{}

	public function getIdentity()
	{
		return $this->mockIdentity;
	}
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


class MockResponse extends \Nette\Http\Response
{
	public function __construct() {}
}
