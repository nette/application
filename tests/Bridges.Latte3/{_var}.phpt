<?php

/** @phpVersion 8.0 */

declare(strict_types=1);

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
