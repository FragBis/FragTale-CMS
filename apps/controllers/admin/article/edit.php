<?php
namespace Bonz\Controller\Admin\Article;
use Bonz\Controller\Admin;
use Bonz\CMS\Article;
use Bonz\CMS\Article_Category;


/**
 * 
 * @author fragbis
 *
 */
class Edit extends Admin{
	
	protected $aid;
	
	function initialize(){
		if (empty($_GET['aid']))
			$this->redirect(ADMIN_WEB_ROOT.'/articles');
		$this->aid = (int)trim($_GET['aid']);
		if (!$this->_article->load("aid=$this->aid")){
			$msg = _('Unexpected error: unable to load article properly.');
			$this->catchError($msg, __CLASS__, __FUNCTION__, __LINE__);
			$this->addUserEndMsg('ERRORS', $msg);
		}
	}
	
	/**
	 * Process post back action
	 * @return boolean
	 */
	function doPostBack(){
		if (!isset($_POST['upd']) && isset($_POST['historicize']) && isset($_POST['upd_historicize'])) return false;
		$NewArticle = clone $this->_article;
		#Update informations
		if (isset($_POST['upd']) || isset($_POST['upd_historicize'])){
			$values = array();
			foreach ($_POST as $key=>$value){
				if (property_exists($this->_article, $key))
					$values[$key] = $value;
			}
			$values['publish'] = !empty($values['publish']) ? 1 : 0;
			$values['uid'] = $_SESSION['REG_USER']['uid'];
			if (empty($values['catid']))
				unset($values['catid']);
			
			if ($this->checkDuplicates($values)){
				if ($NewArticle->update("aid=$this->aid", $values)){
					$NewArticle->load("aid=$this->aid");
					$this->addUserEndMsg('SUCCESS', _('This article has been successfully updated.'));
					$this->redirect($_SERVER['REQUEST_URI']);
				}
			}
		}
		#Store into Article_History table
		if (isset($_POST['historicize']) || isset($_POST['upd_historicize'])){
			if ($NewArticle->historicize())
				$this->addUserEndMsg('SUCCESS', _('This article has been historicize.'));
			else
				$this->addUserEndMsg('ERRORS', _('Historization failed.'));
		}
		else
			$NewArticle->autoHistoricize($this->_article);
	}
	
	function main(){
		$this->_view->article_category = $this->_article->getCategory();
		#Include Wysiwyg
		$this->addJS(WEB_ROOT.'/js/wysiwyg.js');
		$this->setTitle(_('Article edition'));
	}
	
	/**
	 * Check on "unique" fields
	 * @param array $values
	 * @return boolean
	 */
	function checkDuplicates($values){
		#Url alias
		if (!empty($values['request_uri'])){
			if ($this->_article->request_uri!=$values['request_uri']){
				$urlAlias = $this->_article->escape($values['request_uri']);
				if ($this->_article->selectValue("request_uri='$urlAlias'", 'aid')){
					$this->addUserEndMsg('ERRORS', _('There is already a web page having the following Url alias: ').'"'.$urlAlias.'"');
					return false;
				}
			}
		}
		return true;
	}
}