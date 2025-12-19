%A%
		echo '<a href="';
		echo LR\%a%Attr($this->global->uiControl->link('default')) /* %a% */;
		echo '"';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\%a%Attr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* %a% */;
		echo '>n:href before n:class</a>

<a href="';
		echo LR\%a%Attr($this->global->uiControl->link('default')) /* %a% */;
		echo '"';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\%a%Attr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* %a% */;
		echo '>n:href after n:class</a>

<a href="';
		echo LR\%a%Attr($this->global->uiControl->link('default')) /* %a% */;
		echo '"';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\%a%Attr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* %a% */;
		echo '>href before n:class</a>

<a';
		echo ($ʟ_tmp = array_filter([$presenter->isLinkCurrent() ? 'current' : null])) ? ' class="' . LR\%a%Attr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* %a% */;
		echo ' href="';
		echo LR\%a%Attr($this->global->uiControl->link('default')) /* %a% */;
		echo '">href after n:class</a>

<a href="';
		echo LR\%a%Attr($this->global->uiControl->link('default')) /* %a% */;
		echo '"';
		echo ($ʟ_tmp = array_filter([($this->global->fn->isLinkCurrent)(%a%'default') ? 'current' : null])) ? ' class="' . LR\%a%Attr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* %a% */;
		echo '>custom function</a>
';
%A%
