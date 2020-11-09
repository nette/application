<?php

/**
 * Rules for Nette Coding Standard
 * https://github.com/nette/coding-standard
 */

declare(strict_types=1);


return function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
	$containerConfigurator->import(PRESET_DIR . '/php71.php');

	$parameters = $containerConfigurator->parameters();

	$parameters->set('skip', [
		// fails on union type
		PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer::class => [
			'tests/UI/ComponentReflection.combineArgs.php80.phpt',
		],
	]);
};
