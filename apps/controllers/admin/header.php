<?php
namespace Bonz\Controller\Admin;
use Bonz\Controller\Admin;

/**
 * @author fabrice
 */
class Header extends Admin{
	function main(){
		$this->_view->menu['']					= _('Dashboard');
		$this->_view->menu['article_categories'] = _('Article categories');
		$this->_view->menu['articles']			= _('Articles');
		$this->_view->menu['users']				= _('Users');
		$this->_view->menu['file_manager']		= _('File manager');
		if ($this->_view->userIsSuperAdmin())
			$this->_view->menu['system']		= _('System');
		$this->_view->menu['logout']				= _('Logout');

		$my_current_view = !empty($_GET['my_current_view']) ? trim(str_replace('admin', '', $_GET['my_current_view']), '/') : '';
		$my_current_view = explode('/', $my_current_view);
		$this->_view->current = $my_current_view[0];
		if ($this->_view->current=='article_category')
			$this->_view->current = 'article_categories';
	}
}

