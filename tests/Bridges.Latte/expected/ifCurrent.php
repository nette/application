%A%
		if ($this->global->uiPresenter->getLastCreatedRequestFlag("current")) {
			echo 'empty';
		}
		echo '

';
		if ($this->global->uiPresenter->isLinkCurrent('default')) {
			echo 'default';
		}
%A%
