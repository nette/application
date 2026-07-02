<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Source of submitted data for forms anchored to a presenter: detects submission via the 'submit' signal
 * and reads the application request of the presenter the form is currently attached to. The same-origin
 * CSRF check intentionally stays in Form::signalReceived(), where a failure is reported to the presenter.
 * Experimental.
 * @internal
 */
final class FormSubmissionSource implements Nette\Forms\SubmissionSource
{
	public function receiveData(Nette\Forms\Form $form): ?array
	{
		$presenter = $form->lookup(Presenter::class);
		if (!$presenter->isSignalReceiver($form, 'submit')) {
			return null;
		}

		$request = $presenter->getRequest();
		if ($request->isMethod('forward') || $request->isMethod('post') !== $form->isMethod('post')) {
			return null;
		}

		return $form->isMethod('post')
			? Nette\Utils\Arrays::mergeTree($request->getPost(), $request->getFiles())
			: $request->getParameters();
	}


	/**
	 * Installs the hidden field carrying the 'submit' signal.
	 */
	public function prepare(Nette\Forms\Form $form): void
	{
		$key = ($form->isMethod('post') ? '_' : '') . Presenter::SignalKey;
		if (!isset($form[$key]) && $form->getAction() !== '') {
			$do = $form->lookupPath(Presenter::class) . Nette\ComponentModel\IComponent::NameSeparator . 'submit';
			$form[$key] = (new Nette\Forms\Controls\HiddenField($do))->setOmitted();
		}
	}
}
