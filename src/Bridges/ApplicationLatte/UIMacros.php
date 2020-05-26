<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\ApplicationLatte;

use Latte;
use Latte\CompileException;
use Latte\MacroNode;
use Latte\PhpWriter;
use Nette\Utils\Strings;


/**
 * Macros for Nette\Application\UI.
 *
 * - {link destination ...} control link
 * - {plink destination ...} presenter link
 * - {snippet ?} ... {/snippet ?} control snippet
 * - n:nonce
 */
final class UIMacros extends Latte\Macros\MacroSet
{
	/** @var bool|string */
	private $extends;

	/** @var string|null */
	private $printTemplate;


	public static function install(Latte\Compiler $compiler): void
	{
		$me = new static($compiler);
		$me->addMacro('control', [$me, 'macroControl']);

		$me->addMacro('href', null, null, function (MacroNode $node, PhpWriter $writer) use ($me): string {
			return ' ?> href="<?php ' . $me->macroLink($node, $writer) . ' ?>"<?php ';
		});
		$me->addMacro('plink', [$me, 'macroLink']);
		$me->addMacro('link', [$me, 'macroLink']);
		$me->addMacro('ifCurrent', [$me, 'macroIfCurrent'], '}'); // deprecated; use n:class="$presenter->linkCurrent ? ..."
		$me->addMacro('extends', [$me, 'macroExtends']);
		$me->addMacro('layout', [$me, 'macroExtends']);
		$me->addMacro('nonce', null, null, 'echo $this->global->uiNonce ? " nonce=\"{$this->global->uiNonce}\"" : "";');
		$me->addMacro('templatePrint', [$me, 'macroTemplatePrint'], null, null, self::ALLOWED_IN_HEAD);
	}


	/**
	 * Initializes before template parsing.
	 */
	public function initialize(): void
	{
		$this->extends = false;
	}


	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		if ($this->printTemplate) {
			return ["Nette\\Bridges\\ApplicationLatte\\UIRuntime::printClass(\$this, $this->printTemplate); exit;"];
		}
		return [$this->extends . 'Nette\Bridges\ApplicationLatte\UIRuntime::initialize($this, $this->parentName, $this->blocks);'];
	}


	/********************* macros ****************d*g**/


	/**
	 * {control name[:method] [params]}
	 */
	public function macroControl(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers) {
			trigger_error('Modifiers are deprecated in ' . $node->getNotation(), E_USER_DEPRECATED);
		}
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException('Missing control name in {control}');
		}
		$name = $writer->formatWord($words[0]);
		$method = ucfirst($words[1] ?? '');
		$method = Strings::match($method, '#^\w*$#D')
			? "render$method"
			: "{\"render$method\"}";

		$tokens = $node->tokenizer;
		$pos = $tokens->position;
		$param = $writer->formatArray();
		$tokens->position = $pos;
		while ($tokens->nextToken()) {
			if ($tokens->isCurrent('=>') && !$tokens->depth) {
				$wrap = true;
				break;
			}
		}
		if (empty($wrap) && $param[0] === '[') {
			$param = substr($param, 1, -1); // removes array() or []
		}
		return "/* line $node->startLine */ "
			. ($name[0] === '$' ? "if (is_object($name)) \$_tmp = $name; else " : '')
			. '$_tmp = $this->global->uiControl->getComponent(' . $name . '); '
			. 'if ($_tmp instanceof Nette\Application\UI\IRenderable) $_tmp->redrawControl(null, false); '
			. ($node->modifiers === ''
				? "\$_tmp->$method($param);"
				: $writer->write("ob_start(function () {}); \$_tmp->$method($param); echo %modify(ob_get_clean());")
			);
	}


	/**
	 * {link destination [,] [params]}
	 * {plink destination [,] [params]}
	 * n:href="destination [,] [params]"
	 */
	public function macroLink(MacroNode $node, PhpWriter $writer)
	{
		$node->modifiers = preg_replace('#\|safeurl\s*(?=\||$)#Di', '', $node->modifiers);
		return $writer->using($node, $this->getCompiler())
			->write(
				'echo %escape(%modify('
				. ($node->name === 'plink' ? '$this->global->uiPresenter' : '$this->global->uiControl')
				. '->link(%node.word, %node.array?)))'
			);
	}


	/**
	 * {ifCurrent destination [,] [params]}
	 */
	public function macroIfCurrent(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers) {
			throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
		}
		return $writer->write(
			$node->args
				? 'if ($this->global->uiPresenter->isLinkCurrent(%node.word, %node.array?)) {'
				: 'if ($this->global->uiPresenter->getLastCreatedRequestFlag("current")) {'
		);
	}


	/**
	 * {extends auto}
	 */
	public function macroExtends(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers || $node->parentNode || $node->args !== 'auto') {
			return $this->extends = false;
		}
		$this->extends = $writer->write('$this->parentName = $this->global->uiPresenter->findLayoutTemplateFile();');
	}


	/**
	 * {templatePrint [parentClass | default]}
	 */
	public function macroTemplatePrint(MacroNode $node): void
	{
		if ($node->modifiers) {
			throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
		}
		$this->printTemplate = var_export($node->tokenizer->fetchWord() ?: null, true);
	}
}
