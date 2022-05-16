<?php

/** @phpVersion 8.0 */

declare(strict_types=1);

use Nette\Localization\Translator;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\TranslatorExtension(null));

Assert::contains(
	'echo LR\Filters::escapeHtmlText(($this->filters->translate)(\'var\')) /*',
	$latte->compile('{_var}'),
);

Assert::contains(
	'echo LR\Filters::escapeHtmlText(($this->filters->filter)(($this->filters->translate)(\'var\'))) /*',
	$latte->compile('{_var|filter}'),
);

Assert::contains(
	'echo LR\Filters::escapeHtmlText(($this->filters->translate)($var, 10, 20)) /* line 1 */;',
	$latte->compile('{_$var, 10, 20}'),
);


class MyTranslator implements Translator
{
	public function translate($message, ...$parameters): string
	{
		return strrev($message) . implode(',', $parameters);
	}
}

$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new Nette\Bridges\ApplicationLatte\TranslatorExtension(new MyTranslator));
Assert::contains(
	'echo LR\Filters::escapeHtmlText(($this->filters->translate)(\'a&b\', 1, 2))',
	$latte->compile('{_"a&b", 1, 2}'),
);
Assert::same(
	'b&amp;a1,2',
	$latte->renderToString('{_"a&b", 1, 2}'),
);


$latte->addExtension(new Nette\Bridges\ApplicationLatte\TranslatorExtension(new MyTranslator, 'en'));
Assert::contains(
	'echo LR\Filters::escapeHtmlText(\'b&a1,2\')',
	$latte->compile('{_"a&b", 1, 2}'),
);
Assert::same(
	'b&amp;a1,2',
	$latte->renderToString('{_"a&b", 1, 2}'),
);
