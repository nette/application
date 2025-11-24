%A%
		echo '<a href="';
		echo LR\HtmlHelpers::escapeAttr($this->global->uiControl->link('default')) /* pos 1:4 */;
		echo '"';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\HtmlHelpers::escapeAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* pos 1:21 */;
		echo '>n:href before n:class</a>

<a href="';
		echo LR\HtmlHelpers::escapeAttr($this->global->uiControl->link('default')) /* pos 3:52 */;
		echo '"';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\HtmlHelpers::escapeAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* pos 3:4 */;
		echo '>n:href after n:class</a>

<a href="';
		echo LR\HtmlHelpers::escapeAttr($this->global->uiControl->link('default')) /* pos 5:10 */;
		echo '"';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\HtmlHelpers::escapeAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* pos 5:26 */;
		echo '>href before n:class</a>

<a';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\HtmlHelpers::escapeAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* pos 7:4 */;
		echo ' href="';
		echo LR\HtmlHelpers::escapeAttr($this->global->uiControl->link('default')) /* pos 7:58 */;
		echo '">href after n:class</a>

<a href="';
		echo LR\HtmlHelpers::escapeAttr($this->global->uiControl->link('default')) /* pos 9:49 */;
		echo '"';
		echo ($ʟ_tmp = array_filter([($this->global->fn->isLinkCurrent)($this, 'default') ? 'current' : null])) ? ' class="' . LR\HtmlHelpers::escapeAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* pos 9:4 */;
		echo '>custom function</a>
';
%A%
