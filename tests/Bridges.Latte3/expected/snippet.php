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
		$this->renderBlock('', [], null, 'snippet') /* %a% */;
		echo '</div>



	';
		echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('outer')), '">';
		$this->renderBlock('outer', [], null, 'snippet') /* %a% */;
		echo '</div>



	@';
		if (true) /* %a% */ {
			echo ' Hello World @';
		}
		echo '

	';
		echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('title')), '">';
		$this->renderBlock('title', [], null, 'snippet') /* %a% */;
		echo '</div>

	';
		echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('title2')), '">';
		$this->renderBlock('title2', [], null, 'snippet') /* %a% */;
		echo '</div>';
	}


	/** {snippet} on %a% */
	public function block1(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('', 'static') /* %a% */;
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

		$this->global->snippetDriver->enter('outer', 'static') /* %a% */;
		try {
			echo '
	Outer
		';
			echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('inner')), '">';
			$this->renderBlock('inner', [], null, 'snippet') /* %a% */;
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

		$this->global->snippetDriver->enter('inner', 'static') /* %a% */;
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

		$this->global->snippetDriver->enter('title', 'static') /* %a% */;
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

		$this->global->snippetDriver->enter('title2', 'static') /* %a% */;
		try {
			echo 'Title 2';

		} finally {
			$this->global->snippetDriver->leave();
		}
	}
}
