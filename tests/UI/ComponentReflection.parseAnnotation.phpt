<?php

/**
 * Test: ComponentReflection annotation parser.
 */

declare(strict_types=1);

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
class TestClass {}


$rc = new ReflectionClass('TestClass');

Assert::same(['value ="Johno\'s addendum"', 'mode=True', TRUE, TRUE], Reflection::parseAnnotation($rc, 'title'));
Assert::same(FALSE, Reflection::parseAnnotation($rc, 'public'));
Assert::same(FALSE, Reflection::parseAnnotation($rc, 'private'));
Assert::same(['item 1'], Reflection::parseAnnotation($rc, 'components'));
Assert::same([TRUE, FALSE, NULL], Reflection::parseAnnotation($rc, 'persistent'));
Assert::same([TRUE], Reflection::parseAnnotation($rc, 'renderable'));
Assert::same(['loggedIn'], Reflection::parseAnnotation($rc, 'Secured\User'));
Assert::false(Reflection::parseAnnotation($rc, 'missing'));
