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
		$this->renderBlock('outer', [], null, 'snippet') /* pos %d%:20 */;
		echo '</div>

	<div';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('gallery')), '"';
		echo LR\HtmlHelpers::formatListAttribute(' class', 'class') /* pos %d%:36 */;
		echo '>';
		$this->renderBlock('gallery', [], null, 'snippet') /* pos %d%:7 */;
		echo '</div>

	<script';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('script')), '"';
		echo '>';
		$this->renderBlock('script', [], null, 'snippet') /* pos %d%:10 */;
		echo '</script>

	<script';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('hello')), '"';
		echo '>';
		$this->renderBlock('hello', [], null, 'snippet') /* pos %d%:10 */;
		echo '</script>
';
	}


	/** n:snippet="outer" on %a% */
	public function blockOuter(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('outer', 'static') /* pos %d%:20 */;
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

		$this->global->snippetDriver->enter('gallery', 'static') /* pos %d%:7 */;
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

		$this->global->snippetDriver->enter('script', 'static') /* pos %d%:10 */;
		try {
			echo LR\Helpers::escapeJs('x') /* pos %d%:29 */;

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

		$this->global->snippetDriver->enter('hello', 'static') /* pos %d%:10 */;
		try {
			echo LR\Helpers::escapeJs('y') /* pos %d%:32 */;

		} finally {
			$this->global->snippetDriver->leave();
		}
	}
}