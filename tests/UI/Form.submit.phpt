<?php declare(strict_types=1);

/**
 * Test: Nette\Application\UI\Form submission through a presenter.
 */

use Nette\{Application, Http};
use Nette\Application\UI;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class SubmitPresenter extends UI\Presenter
{
	public ?Closure $setup = null;
	public array $success = [];
	public array $errors = [];
	public int $csrf = 0;
	public string $html = '';


	protected function createComponentForm(): UI\Form
	{
		$form = new UI\Form;
		$form->setAction('/submit');
		$form->addText('name')->setRequired();
		$form->addUpload('file');
		$form->addSubmit('send');
		if ($this->setup) {
			($this->setup)($form);
		}

		$form->onSuccess[] = function (UI\Form $form, $values): void {
			$this->success[] = $values;
		};
		$form->onError[] = function (UI\Form $form): void {
			$this->errors[] = $form->getErrors();
		};
		return $form;
	}


	public function detectedCsrf(): void
	{
		$this->csrf++;
	}


	public function renderDefault(): void
	{
		ob_start();
		$this['form']->render();
		$this->html = ob_get_clean();
		$this->terminate();
	}
}


function submit(
	array $post,
	string $site = 'same-origin',
	?Closure $setup = null,
	bool $signal = true,
	string $method = 'POST',
	array $files = [],
): SubmitPresenter
{
	$params = ['action' => 'default'];
	if ($signal) {
		$params['do'] = 'form-submit';
	}

	$isPost = $method === 'POST';
	if ($isPost && $signal) {
		$post += ['_do' => 'form-submit'];
	}

	$url = 'http://localhost/' . ($isPost ? '' : '?' . http_build_query($post + $params));
	$httpRequest = new Http\Request(
		new Http\UrlScript($url),
		post: $isPost ? $post : [],
		headers: ['Sec-Fetch-Site' => $site],
		method: $method,
	);

	$presenter = new SubmitPresenter;
	$presenter->setup = $setup;
	$presenter->autoCanonicalize = false;
	$presenter->injectPrimary($httpRequest, new Http\Response);
	$presenter->run(new Application\Request('Submit', $method, $isPost ? $params : $post + $params, $isPost ? $post : [], $files));
	return $presenter;
}


function createUpload(): Http\FileUpload
{
	$file = tempnam(getTempDir(), 'upl');
	file_put_contents($file, 'content');
	return new Http\FileUpload(['name' => 'a.txt', 'size' => 7, 'tmp_name' => $file, 'error' => UPLOAD_ERR_OK]);
}


test('valid same-origin POST calls onSuccess once with values and file', function () {
	$presenter = submit(['name' => 'John'], files: ['file' => createUpload()]);

	Assert::count(0, $presenter->errors);
	Assert::count(1, $presenter->success);
	$values = $presenter->success[0];
	Assert::same('John', $values->name);
	Assert::type(Http\FileUpload::class, $values->file);
	Assert::true($values->file->isOk());
	Assert::same('a.txt', $values->file->getUntrustedName());
	Assert::same(0, $presenter->csrf);
});


test('invalid same-origin POST calls onError and the control has the error', function () {
	$presenter = submit(['name' => '']);

	Assert::count(0, $presenter->success);
	Assert::count(1, $presenter->errors);
	Assert::count(1, $presenter['form']['name']->getErrors());
});


test('controls added before attaching to the presenter receive submitted values', function () {
	$presenter = submit(['name' => 'John']);

	Assert::same('John', $presenter['form']['name']->getValue());
	Assert::true($presenter['form']->isSubmitted() !== false);
});


test('rendering after submission has the signal field and keeps the value', function () {
	$presenter = submit(['name' => 'John']);

	Assert::contains('name="_do" value="form-submit"', $presenter->html);
	Assert::contains('value="John"', $presenter->html);
});


test('cross-site POST with protection on triggers CSRF handling, not onSuccess', function () {
	$presenter = submit(['name' => 'John'], 'cross-site');

	Assert::count(0, $presenter->success);
	Assert::count(0, $presenter->errors);
	Assert::same(1, $presenter->csrf);
});


test('cross-site POST after allowCrossOrigin() calls onSuccess', function () {
	$presenter = submit(['name' => 'John'], 'cross-site', fn(UI\Form $form) => $form->allowCrossOrigin());

	Assert::count(1, $presenter->success);
	Assert::same('John', $presenter->success[0]->name);
	Assert::same(0, $presenter->csrf);
});


test('request without the signal does not submit the form', function () {
	$presenter = submit(['name' => 'John'], signal: false);

	Assert::count(0, $presenter->success);
	Assert::count(0, $presenter->errors);
	Assert::same(0, $presenter->csrf);
});


test('GET form is submitted through query parameters', function () {
	$presenter = submit(
		['name' => 'John'],
		setup: fn(UI\Form $form) => $form->setMethod('get'),
		method: 'GET',
	);

	Assert::count(1, $presenter->success);
	Assert::same('John', $presenter->success[0]->name);
});


if (class_exists(Nette\Forms\Repeater::class)) {
	test('repeater inside the form receives posted items', function () {
		$presenter = submit(
			['name' => 'John', 'items' => [['x' => 'a'], ['x' => 'b']]],
			setup: function (UI\Form $form): void {
				$form->addRepeater('items', fn($container) => $container->addText('x'));
			},
		);

		Assert::count(1, $presenter->success);
		Assert::same(
			[['x' => 'a'], ['x' => 'b']],
			$presenter['form']->getValues('array')['items'],
		);
	});
}
