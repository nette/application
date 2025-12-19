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
		$this->renderBlock('outer', [], null, 'snippet') /* %a% */;
		echo '</div>

	<div';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('gallery')), '"';
		%A%
		$this->renderBlock('gallery', [], null, 'snippet') /* %a% */;
		echo '</div>

	<script';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('script')), '"';
		echo '>';
		$this->renderBlock('script', [], null, 'snippet') /* %a% */;
		echo '</script>

	<script';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('hello')), '"';
		echo '>';
		$this->renderBlock('hello', [], null, 'snippet') /* %a% */;
		echo '</script>
';
	}


	/** n:snippet="outer" on %a% */
	public function blockOuter(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('outer', 'static') /* %a% */;
		try {
			echo '
	<p>Outer</p>
	';

		} finally {
			$this->global->snippetDriver->leave();
		}
	}


	/** n:snippet="gallery" on %a% */
	public function blockGallery(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('gallery', 'static') /* %a% */;
		try {
		} finally {
			$this->global->snippetDriver->leave();
		}
	}


	/** n:snippet="script" on %a% */
	public function blockScript(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('script', 'static') /* %a% */;
		try {
			echo LR\%a%::escapeJs('x') /* %a% */;

		} finally {
			$this->global->snippetDriver->leave();
		}
	}


	/** n:snippet="Test::Foo" on %a% */
	public function blockHello(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('hello', 'static') /* %a% */;
		try {
			echo LR\%a%::escapeJs('y') /* %a% */;

		} finally {
			$this->global->snippetDriver->leave();
		}
	}
}