%A%<a href="%A%"%A%>n:href before n:class</a>

<a href="%A%"%A%>n:href after n:class</a>

<a href="%A%"%A%>href before n:class</a>

<a href="%A%"%A%>href after n:class</a>

';
		if ($this->global->uiPresenter->getLastCreatedRequestFlag("current")) {
			echo 'empty';
		}
		echo '

';
		if ($this->global->uiPresenter->isLinkCurrent("default")) {
			echo 'default';
		}
		echo '

<a href="%A%"%A%($this->global->fn->isLinkCurrent)('default') ? 'current' : null%A%>custom function</a>
%A%
