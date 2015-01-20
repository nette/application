<?php

/**
 * Test: PresenterComponentReflection annotation parser.
 */

use Nette\Application\UI\PresenterComponentReflection as Reflection,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


/**
 * @title(value ="Johno's addendum", mode=True,) , out
 * @title
 * @title()
 * @components(item 1)
 * @persistent(true)
 * @persistent(FALSE)
 * @persistent(null)
 * @author
 * @renderable
 * @secured(role = "admin", level = 2)
 */
class TestClass {}


$rc = new ReflectionClass('TestClass');

Assert::same(array('value ="Johno\'s addendum"', 'mode=True', TRUE, TRUE), Reflection::parseAnnotation($rc, 'title'));
Assert::same(array('item 1'), Reflection::parseAnnotation($rc, 'components'));
Assert::same(array('true', 'FALSE', 'null'), Reflection::parseAnnotation($rc, 'persistent'));
Assert::same(array(TRUE), Reflection::parseAnnotation($rc, 'renderable'));
Assert::false(Reflection::parseAnnotation($rc, 'missing'));
