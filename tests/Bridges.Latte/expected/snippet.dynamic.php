<?php
%A%
final class Template%a% extends Latte\Runtime\Template
{
	public const Blocks = [
		'snippet' => ['outer' => 'blockOuter'],
	];


	public function main(array $ʟ_args): void
	{
%A%
		echo '	';
		echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('outer')), '">';
		$this->renderBlock('outer', [], null, 'snippet') /* pos %d%:2 */;
		echo '</div>';
	}


	public function prepare(): array
	{
%A%
	}


	/** {snippet outer} on %a% */
	public function blockOuter(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('outer', 'static') /* pos %d%:2 */;
		try {
			echo "\n";
			foreach ([1, 2, 3] as $id) /* pos %d%:2 */ {
				echo '		';
				echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId($ʟ_nm = "inner-{$id}")), '">';
				$this->global->snippetDriver->enter($ʟ_nm, 'dynamic') /* pos %d%:3 */;
				try {
					echo '
				#';
					echo LR\HtmlHelpers::escapeText($id) /* pos %d%:6 */;
					echo '
		';

				} finally {
					$this->global->snippetDriver->leave();
				}

				echo '</div>
';

			}

			echo '	';

		} finally {
			$this->global->snippetDriver->leave();
		}
	}
}
