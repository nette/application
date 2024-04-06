%A%
		echo '<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 1 */;
		echo '"';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 1 */;
		echo '>n:href before n:class</a>

<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 3 */;
		echo '"';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 3 */;
		echo '>n:href after n:class</a>

<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 5 */;
		echo '"';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 5 */;
		echo '>href before n:class</a>

<a';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 7 */;
		echo ' href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 7 */;
		echo '">href after n:class</a>

<a href="';
		echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link('default')) /* line 9 */;
		echo '"';
		echo ($ʟ_tmp = array_filter([($this->global->fn->isLinkCurrent)(%a%'default') ? 'current' : null])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 9 */;
		echo '>custom function</a>
';
%A%
