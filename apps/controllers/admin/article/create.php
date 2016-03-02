<?php
namespace FragTale\Controller\Admin\Article;
use FragTale\Controller\Admin;
use FragTale\Controller;
use FragTale\CMS\Article;
use FragTale\CMS\Article_Category;
use FragTale\CMS\Files;

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
		
		if (empty($_POST['article']['selected_fid'])){
			#File upload
			$Files = new Files();
			if (!empty($_FILES['article'])){
				if ($files = $Files->store($_FILES['article'])){
					$keys = array_keys($files);
					$values['fid'] = reset($keys);
				}
			}
		}
		else
			$values['fid'] = $_POST['article']['selected_fid'];
		unset($values['article']);
		
		$values['publish'] = !empty($values['publish']) ? 1 : 0;
		$values['uid']		= $this->getUser()->uid;
		$values['owner_id'] = $this->getUser()->uid;
		$values['cre_date'] = date('Y-m-d H:i:s');
		$values['position'] = (int)$this->getArticle()->selectMax('position') + 1;
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
		#Include ckeditor
		$this->addJS(WEB_ROOT.'/js/ckeditor/ckeditor.js');
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