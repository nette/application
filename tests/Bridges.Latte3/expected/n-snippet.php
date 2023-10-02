<?php
%A%
final class Template%a% extends Latte\Runtime\Template
{
	public const Blocks = [
		'snippet' => ['outer' => 'blockOuter', 'gallery' => 'blockGallery', 'script' => 'blockScript', 'hello' => 'blockHello'],
	];


	public function main(array $ʟ_args): void
	{
%A%
		echo '	<div class="test"';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('outer')), '"';
		echo '>';
		$this->renderBlock('outer', [], null, 'snippet') /* line %d% */;
		echo '</div>

	<div';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('gallery')), '"';
		echo ' class="';
		echo LR\Filters::escapeHtmlAttr('class') /* line %d% */;
		echo '">';
		$this->renderBlock('gallery', [], null, 'snippet') /* line %d% */;
		echo '</div>

	<script';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('script')), '"';
		echo '>';
		$this->renderBlock('script', [], null, 'snippet') /* line %d% */;
		echo '</script>

	<script';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('hello')), '"';
		echo '>';
		$this->renderBlock('hello', [], null, 'snippet') /* line 9 */;
		echo '</script>
';
	}


	/** n:snippet="outer" on line %d% */
	public function blockOuter(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('outer', 'static') /* line %d% */;
		try {
			echo '
	<p>Outer</p>
	';

		} finally {
			$this->global->snippetDriver->leave();
		}
	}


	/** n:snippet="gallery" on line %d% */
	public function blockGallery(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('gallery', 'static') /* line %d% */;
		try {
		} finally {
			$this->global->snippetDriver->leave();
		}
	}


	/** n:snippet="script" on line %d% */
	public function blockScript(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('script', 'static') /* line %d% */;
		try {
			echo LR\Filters::escapeJs('x') /* line %d% */;

		} finally {
			$this->global->snippetDriver->leave();
		}
	}


	/** n:snippet="Test::Foo" on line %d% */
	public function blockHello(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('hello', 'static') /* line %d% */;
		try {
			echo LR\Filters::escapeJs('y') /* line %d% */;

		} finally {
			$this->global->snippetDriver->leave();
		}
	}
}