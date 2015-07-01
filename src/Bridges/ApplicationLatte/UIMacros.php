<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Latte;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\CompileException;
use Nette\Utils\Strings;


/**
 * Macros for Nette\Application\UI.
 *
 * - {link destination ...} control link
 * - {plink destination ...} presenter link
 * - {snippet ?} ... {/snippet ?} control snippet
 */
class UIMacros extends Latte\Macros\MacroSet
{

	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('control', array($me, 'macroControl'));

		$me->addMacro('href', NULL, NULL, function (MacroNode $node, PhpWriter $writer) use ($me) {
			return ' ?> href="<?php ' . $me->macroLink($node, $writer) . ' ?>"<?php ';
		});
		$me->addMacro('plink', array($me, 'macroLink'));
		$me->addMacro('link', array($me, 'macroLink'));
		$me->addMacro('ifCurrent', array($me, 'macroIfCurrent'), '}'); // deprecated; use n:class="$presenter->linkCurrent ? ..."
	}


	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		$prolog = '
// snippets support
if (empty($_l->extends) && !empty($_control->snippetMode)) {
	return Nette\Bridges\ApplicationLatte\UIRuntime::renderSnippets($_control, $_b, get_defined_vars());
}';
		return array($prolog, '');
	}


	/********************* macros ****************d*g**/


	/**
	 * {control name[:method] [params]}
	 */
	public function macroControl(MacroNode $node, PhpWriter $writer)
	{
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException('Missing control name in {control}');
		}
		$name = $writer->formatWord($words[0]);
		$method = isset($words[1]) ? ucfirst($words[1]) : '';
		$method = Strings::match($method, '#^\w*\z#') ? "render$method" : "{\"render$method\"}";
		$param = $writer->formatArray();
		if (!Strings::contains($node->args, '=>')) {
			$param = substr($param, $param[0] === '[' ? 1 : 6, -1); // removes array() or []
		}
		return ($name[0] === '$' ? "if (is_object($name)) \$_l->tmp = $name; else " : '')
			. '$_l->tmp = $_control->getComponent(' . $name . '); '
			. 'if ($_l->tmp instanceof Nette\Application\UI\IRenderable) $_l->tmp->redrawControl(NULL, FALSE); '
			. ($node->modifiers === '' ? "\$_l->tmp->$method($param)" : $writer->write("ob_start(); \$_l->tmp->$method($param); echo %modify(ob_get_clean())"));
	}


	/**
	 * {link destination [,] [params]}
	 * {plink destination [,] [params]}
	 * n:href="destination [,] [params]"
	 */
	public function macroLink(MacroNode $node, PhpWriter $writer)
	{
		$node->modifiers = preg_replace('#\|safeurl\s*(?=\||\z)#i', '', $node->modifiers);
		return $writer->using($node, $this->getCompiler())
			->write('echo %escape(%modify(' . ($node->name === 'plink' ? '$_presenter' : '$_control') . '->link(%node.word, %node.array?)))');
	}


	/**
	 * {ifCurrent destination [,] [params]}
	 */
	public function macroIfCurrent(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write($node->args
			? 'if ($_presenter->isLinkCurrent(%node.word, %node.array?)) {'
			: 'if ($_presenter->getLastCreatedRequestFlag("current")) {'
		);
	}


	/** @deprecated */
	public static function renderSnippets(Nette\Application\UI\Control $control, \stdClass $local, array $params)
	{
		UIRuntime::renderSnippets($control, $local, $params);
	}

}
