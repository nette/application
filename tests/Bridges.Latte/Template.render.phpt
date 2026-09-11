<?php declare(strict_types=1);

/**
 * Test: Template rendering with object parameters
 */

use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


function createTemplate(): DefaultTemplate
{
	$latte = new Latte\Engine;
	$latte->setLoader(new Latte\Loaders\StringLoader);
	return new DefaultTemplate($latte);
}


test('render accepts object parameters', function () {
	$template = createTemplate();
	ob_start();
	$template->render('{$message}', (object) ['message' => 'Hello']);
	Assert::same('Hello', ob_get_clean());
});


test('renderToString accepts object parameters', function () {
	$template = createTemplate();
	Assert::same('Hello', $template->renderToString('{$message}', (object) ['message' => 'Hello']));
});
