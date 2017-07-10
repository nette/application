<?php

/**
 * Test: ComponentReflection annotation parser.
 */

use Nette\Application\UI\ComponentReflection as Reflection;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


/**
 * @title(value ="Johno's addendum", mode=True,) , out
 * @title
 * @title()
 * @publi - 'c' is missing intentionally
 * @privatex
 * @components(item 1)
 * @persistent(true)
 * @persistent(FALSE)
 * @persistent(null)
 * @author
 *@renderable
 * @secured(role = "admin", level = 2)
 * @Secured\User(loggedIn)
 */
class TestClass
{
}


$rc = new ReflectionClass('TestClass');

Assert::same(['value ="Johno\'s addendum"', 'mode=True', true, true], Reflection::parseAnnotation($rc, 'title'));
Assert::same(false, Reflection::parseAnnotation($rc, 'public'));
Assert::same(false, Reflection::parseAnnotation($rc, 'private'));
Assert::same(['item 1'], Reflection::parseAnnotation($rc, 'components'));
Assert::same([true, false, null], Reflection::parseAnnotation($rc, 'persistent'));
Assert::same([true], Reflection::parseAnnotation($rc, 'renderable'));
Assert::same(['loggedIn'], Reflection::parseAnnotation($rc, 'Secured\User'));
Assert::false(Reflection::parseAnnotation($rc, 'missing'));
