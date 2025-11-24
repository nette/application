<?php
%A%
final class Template%a% extends Latte\Runtime\Template
{
	public const Blocks = [
		'snippet' => ['' => 'block1', 'outer' => 'blockOuter', 'inner' => 'blockInner', 'title' => 'blockTitle', 'title2' => 'blockTitle2'],
	];


	public function main(array $ʟ_args): void
	{
%A%
		echo '	';
		echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('')), '">';
		$this->renderBlock('', [], null, 'snippet') /* pos %d%:2 */;
		echo '</div>



	';
		echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('outer')), '">';
		$this->renderBlock('outer', [], null, 'snippet') /* pos %d%:2 */;
		echo '</div>



	@';
		if (true) /* pos %d%:3 */ {
			echo ' Hello World @';
		}
		echo '

	';
		echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('title')), '">';
		$this->renderBlock('title', [], null, 'snippet') /* pos %d%:2 */;
		echo '</div>

	';
		echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('title2')), '">';
		$this->renderBlock('title2', [], null, 'snippet') /* pos %d%:2 */;
		echo '</div>';
	}


	/** {snippet} on %a% */
	public function block1(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('', 'static') /* pos %d%:2 */;
		try {
			echo '

	';

		} finally {
			$this->global->snippetDriver->leave();
		}
	}


	/** {snippet outer} on %a% */
	public function blockOuter(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('outer', 'static') /* pos %d%:2 */;
		try {
			echo '
	Outer
		';
			echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('inner')), '">';
			$this->renderBlock('inner', [], null, 'snippet') /* pos %d%:3 */;
			echo '</div>
	/Outer
	';

		} finally {
			$this->global->snippetDriver->leave();
		}
	}


	/** {snippet inner} on %a% */
	public function blockInner(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('inner', 'static') /* pos %d%:3 */;
		try {
			echo 'Inner';

		} finally {
			$this->global->snippetDriver->leave();
		}
	}


	/** {snippet title} on %a% */
	public function blockTitle(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('title', 'static') /* pos %d%:2 */;
		try {
			echo 'Title 1';

		} finally {
			$this->global->snippetDriver->leave();
		}
	}


	/** {snippet title2} on %a% */
	public function blockTitle2(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('title2', 'static') /* pos %d%:2 */;
		try {
			echo 'Title 2';

		} finally {
			$this->global->snippetDriver->leave();
		}
	}
}
