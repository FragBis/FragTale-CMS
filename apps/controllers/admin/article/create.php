<?php
namespace Bonz\Controller\Admin\Article;
use Bonz\Controller\Admin;
use Bonz\Controller;
use Bonz\CMS\Article;
use Bonz\CMS\Article_Category;

/**
 * 
 * @author fragbis
 *
 */
class Create extends Admin{
	
	function initialize(){
		$this->_view->article_category = new Article_Category();
	}
	
	function doPostBack(){
		if (!isset($_POST['register'])) return false;
		#Save informations
		$values = array();
		foreach ($_POST as $key=>$value){
			if (property_exists($this->_article, $key))
				$values[$key] = $value;
		}
		$values['publish'] = !empty($values['publish']) ? 1 : 0;
		$values['uid'] = $_SESSION['REG_USER']['uid'];
		if (empty($values['catid']))
			unset($values['catid']);
		
		if ($this->checkDuplicates($values) && $this->_article->insert($values)){
			$_SESSION['USER_END_MSGS']['SUCCESS'][] = _('This article has been successfully registered.');
			$urlAlias = $this->_article->escape($values['request_uri']);
			$this->_article->load("request_uri='$urlAlias'");
			if ($this->_article->historicize())
				$this->redirect(ADMIN_WEB_ROOT.'/article/edit?aid='.$this->_article->aid);
			else
				$this->addUserEndMsg('ERRORS', _('Article not registered.'));
		}
		else
			$this->addUserEndMsg('ERRORS', _('Article not registered.'));
	}
	
	function main(){
		#Include Wysiwyg
		$this->addJS(WEB_ROOT.'/js/wysiwyg.js');
		$this->setTitle(_('Add a new article'));
	}
	
	/**
	 * "Unique" fields must have a check before a new article can be inserted.
	 * @param array		$values
	 * @return boolean
	 */
	function checkDuplicates($values){
		#Url alias
		if (!empty($values['request_uri'])){
			$urlAlias = $this->_article->escape($values['request_uri']);
			if ($this->_article->load("request_uri='$urlAlias'")){
				$this->addUserEndMsg('ERRORS', _('There is already a web page with this Url alias: ').'"'.$urlAlias.'"');
				return false;
			}
		}
		return true;
	}
}