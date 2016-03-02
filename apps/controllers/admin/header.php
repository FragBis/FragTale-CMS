<?php
namespace FragTale\Controller\Admin;
use FragTale\Controller\Admin;

/**
 * @author fabrice
 */
class Header extends Admin{
	function main(){
		$this->_view->menu['']					= _('Dashboard');
		$this->_view->menu['user/edit?uid='. $this->getUser()->uid]	= _('My Account');
		
		if ($this->checkRules('messages'))
			$this->_view->menu['messages']		= _('Letter Box');
		if ($this->checkRules('article_categories'))
			$this->_view->menu['article_categories'] = _('Article Categories');
		if ($this->checkRules('articles'))
			$this->_view->menu['articles']		= _('Articles');
		if ($this->checkRules('users'))
			$this->_view->menu['users']			= _('Users');
		if ($this->checkRules('file_manager'))
			$this->_view->menu['file_manager']	= _('File Manager');
		if ($this->checkRules('system'))
			$this->_view->menu['system']		= _('System');

		$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$urlReplace = str_replace(array('http://', 'https://'), '', ADMIN_WEB_ROOT);
		$my_current_view = trim(str_replace($urlReplace, '', $url), '/');
		$my_current_view = explode('/', $my_current_view);
		$this->_view->current = $my_current_view[0];
		if ($this->_view->current=='article_category')
			$this->_view->current = 'article_categories';
	}
}

