<?php
namespace Bonz\Controller\Admin\Forms;
use Bonz\Controller\Admin;
use Bonz\CMS\Role;
use Bonz\CMS\Article_Category;
use Bonz\CMS\User;

/**
 * @author fabrice
 */
class Article extends Admin{
	function main(){
		$Category = new Article_Category();
		$this->_view->article_categories = $Category->select();
		$this->_view->cms_views = $this->listOfViews();
		$this->_view->author = new User();
		if (!empty($this->_meta_article->uid))
			$this->_view->author->load('uid='.$this->_meta_article->uid);
	}
	
	/**
	 * @desc Get all template files placed in apps/templates/views/cms
	 * Note: there must be no other subfolder in this directory. All template files must be directly placed in it.
	 * @return array
	 */
	function listOfViews(){
		$dir = TPL_ROOT.'/views/cms';
		$views = array();
		if ($handle = \opendir($dir)){
			while ($content = \readdir($handle)) {
				if (!in_array($content, array('.', '..', '.svn'))){
					$f = $dir.'/'.$content;
					if (is_dir($f)){
						$_SESSION['USER_END_MSGS']['ERRORS'][] = _('It should not exist subdirectory in ').$dir.' ('.$f.').';
					}
					elseif (substr($content, -6) == '.phtml')
						$views[] = str_replace('.phtml', '', $content);
				}
			}
			closedir($handle);
			return $views;
		}
		else{
			\Bonz\Application::catchError('Missing required directory '.$dir, __CLASS__, __FUNCTION__, __LINE__);
			return array();
		}
	}
}