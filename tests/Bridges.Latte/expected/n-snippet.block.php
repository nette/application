<?php
%A%
final class Template%a% extends Latte\Runtime\Template
{
	public const Blocks = [
		0 => ['block1' => 'blockBlock1', 'block2' => 'blockBlock2'],
		'snippet' => ['snippet' => 'blockSnippet', 'outer' => 'blockOuter'],
	];


	public function main(array $ʟ_args): void
	{
%A%
		$this->renderBlock('block1', get_defined_vars()) /* %a% */;
		echo '

';
		echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('outer')), '">';
		$this->renderBlock('outer', [], null, 'snippet') /* %a% */;
		echo '</div>';
	}


	/** n:block="block1" on %a% */
	public function blockBlock1(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('snippet')), '"';
		echo '>';
		$this->renderBlock('snippet', [], null, 'snippet') /* %a% */;
		echo '</div>
';
	}


	/** n:snippet="snippet" on %a% */
	public function blockSnippet(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('snippet', 'static') /* %a% */;
		try {
			echo '
		static
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
begin
';
			$this->renderBlock('block2', get_defined_vars()) /* %a% */;
			echo 'end
';

		} finally {
			$this->global->snippetDriver->leave();
		}
	}


	/** n:block="block2" on %a% */
	public function blockBlock2(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<div';
		echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId($ʟ_nm = "inner-{$id}")), '"';
		echo '>';
		$this->global->snippetDriver->enter($ʟ_nm, 'dynamic') /* %a% */;
		try {
			echo '
		dynamic
';

		} finally {
			$this->global->snippetDriver->leave();
		}
		echo '</div>
';
	}
}
