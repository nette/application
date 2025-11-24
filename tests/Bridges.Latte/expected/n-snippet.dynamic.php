<?php
%A%
final class Template%a% extends Latte\Runtime\Template
{
	public const Blocks = [
		'snippet' => ['outer1' => 'blockOuter1'],
	];


	public function main(array $ʟ_args): void
	{
%A%
		echo '	';
		echo '<div id="', htmlspecialchars($this->global->snippetDriver->getHtmlId('outer1')), '">';
		$this->renderBlock('outer1', [], null, 'snippet') /* pos 1:2 */;
		echo '</div>';
	}


	public function prepare(): array
	{
%A%
	}


	/** {snippet outer1} on %a% */
	public function blockOuter1(array $ʟ_args): void
	{
		extract($this->params);
		extract($ʟ_args);
		unset($ʟ_args);

		$this->global->snippetDriver->enter('outer1', 'static') /* pos 1:2 */;
		try {
			echo "\n";
			foreach ([1, 2, 3] as $id) /* pos 2:2 */ {
				echo '		<div';
				echo ' id="', htmlspecialchars($this->global->snippetDriver->getHtmlId($ʟ_nm = "inner-{$id}")), '"';
				echo '>';
				$this->global->snippetDriver->enter($ʟ_nm, 'dynamic') /* pos 3:8 */;
				try {
					echo '
				#';
					echo LR\HtmlHelpers::escapeText($id) /* pos 4:6 */;
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
