<?php
namespace Bonz\Controller\Admin\Article_Category;
use Bonz\Controller\Admin;
use Bonz\CMS\Article_Category as CMS_CAT;
use Bonz\CMS\Article as CMS_ART;
use Bonz\CMS\Files;

class Edit extends Admin{

	protected $catid;
	protected $Category;
	
	function initialize(){
		if (empty($_GET['catid']))
			$this->redirect(ADMIN_WEB_ROOT.'/article_categories');
		$this->catid	= $_GET['catid'];
		$this->Category = new CMS_CAT();
	}
	
	function doPostBack(){
		if (!isset($_POST['update']) && !isset($_POST['submit_category']) && !isset($_POST['submit_article']))
			return false;
		if (!$this->checkDuplicates($_POST))
			return false;
		$values = $_POST['category'];
		unset($values['selected_fid']);
		if (empty($_POST['category']['selected_fid'])){
			#File upload
			$Files = new Files();
			if (!empty($_FILES['category'])){
				if ($files = $Files->store($_FILES['category'])){
					if (is_array($files))
						$values['fid'] = reset(array_keys($files));
				}
			}
		}
		else
			$values['fid'] = $_POST['category']['selected_fid'];

		$values['upd_uid'] = $_SESSION['REG_USER']['uid'];
		if (empty($values['parent_catid']))
			unset($values['parent_catid']);

		#Update category
		if (isset($_POST['update']) || isset($_POST['submit_category'])){
			if ($this->Category->update('catid='.$this->catid, $values))
				$this->_view->addUserEndMsg('SUCCESS', _('Category successfully updated.'));
		}
		if ($this->Category->load('catid='.$this->catid)){
			#Update article bound to this category.
			if (isset($_POST['update']) || isset($_POST['submit_article'])){
				$values				= $_POST['article'];
				$values['uid']		= $_SESSION['REG_USER']['uid'];
				$values['publish']	= isset($values['publish']) ? 1 : 0;
				$oldArticle = new CMS_ART();
				$oldArticle->load('aid='.$this->Category->aid);
				if ($this->_article->update('aid='.$this->Category->aid, $values)){
					if ($this->_article->load('aid='.$this->Category->aid)){
						$this->addUserEndMsg('SUCCESS', _('Article successfully updated.'));
						$this->_article->autoHistoricize($oldArticle);
					}
					else
						$this->addUserEndMsg('ERRORS', _('Unexpected error while loading the article of category.'));
				}
			}
		}
		else
			$this->addUserEndMsg('ERRORS', _('Unexpected error: unable to load category.'));
		$this->redirect($_SERVER['REQUEST_URI']);
	}

	function main(){
		$this->_meta_view->article_category	= $this->Category;
		$this->_meta_view->article_category->load("catid='$this->catid'");
		$this->_meta_article->load('aid='.$this->_meta_view->article_category->aid);
		#Include Wysiwyg
		$this->addJS(WEB_ROOT.'/js/wysiwyg.js');
		$this->setTitle(_('Category edition'));
	}

	/**
	 * Check if certain values already exist into Category and Article tables.
	 * @param array $values $_POST
	 * @return boolean
	 */
	function checkDuplicates($values){
		#Check category name
		$this->Category->load('catid='.$this->catid);
		$initialCatName = $this->Category->name;
		$catname = $this->_article->escape($values['category']['name']);
		if ($initialCatName != $catname && $this->Category->load("name='$catname'")){
			$this->_view->addUserEndMsg('ERRORS', sprintf(_('There is already a category named "%s"'), $values['category']['name']));
			return false;
		}
		#Check duplicates for article fields
		#Url alias
		if (!empty($values['article']['request_uri'])){
			$this->_article->load('aid='.$this->Category->aid);
			$initialUrlAlias = $this->_article->escape($this->_article->request_uri);
			$urlAlias = $this->_article->escape($values['article']['request_uri']);
			if ($initialUrlAlias != $urlAlias && $this->_article->load("request_uri='$urlAlias'")){
				$_SESSION['USER_END_MSGS']['ERRORS'][] = _('There is already a web page with this Url alias: ').'"'.$urlAlias.'"';
				return false;
			}
		}
		return true;
	}
}