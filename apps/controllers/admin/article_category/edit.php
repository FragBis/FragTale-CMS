<?php
namespace FragTale\Controller\Admin\Article_Category;
use FragTale\Controller\Admin;
use FragTale\CMS\Article_Category as CMS_CAT;
use FragTale\CMS\Article as CMS_ART;
use FragTale\CMS\Files;
use FragTale\Debug;

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
		/* Delete first  */
		if (isset($_POST['delete'])){
			$this->Category->load('catid='.$this->catid);
			if (!empty($this->Category->aid)){
				/* Delete associated article */
				$Art = new CMS_ART();
				$Art->delete('aid='.$this->Category->aid);
			}
			/* Delete category */
			$this->Category->delete('catid='.$this->catid);
			$this->_view->addUserEndMsg('SUCCESS', _('Category successfully deleted.'));
			$this->redirect(WEB_ROOT.'/admin/article_categories');
		}
		/* Check if it's POST */
		if (!isset($_POST['update']) && !isset($_POST['submit_category'])
				&& !isset($_POST['submit_article']) && !isset($_POST['historicize']))
			return false;
		if (!$this->checkDuplicates($_POST))
			return false;
		/* Proceed */
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

		$values['upd_uid'] = $this->getUser()->uid;
		if (empty($values['parent_catid']))
			unset($values['parent_catid']);

		#Update category
		if (isset($_POST['update']) || isset($_POST['submit_category'])){
			if ($this->Category->update('catid='.$this->catid, $values))
				$this->_view->addUserEndMsg('SUCCESS', _('Category successfully updated.'));
		}
		if ($this->Category->load('catid='.$this->catid)){
			$oldArticle = new CMS_ART();
			$oldArticle->load('aid='.$this->Category->aid);
			
			#Update article bound to this category.
			if (isset($_POST['update']) || isset($_POST['submit_article'])){
				$values				= $_POST['article'];
				$values['uid']		= $this->getUser()->uid;
				$values['publish']	= isset($values['publish']) ? 1 : 0;
				if ($this->_article->update('aid='.$this->Category->aid, $values)){
					if ($this->_article->load('aid='.$this->Category->aid)){
						$this->addUserEndMsg('SUCCESS', _('Article successfully updated.'));
						$this->_article->autoHistoricize($oldArticle);
					}
					else
						$this->addUserEndMsg('ERRORS', _('Unexpected error while loading the article of category.'));
				}
			}
			
			#Store into Article_History table
			if (isset($_POST['historicize'])){
				if ($oldArticle->historicize())
					$this->addUserEndMsg('SUCCESS', _('This article has been historicize.'));
				else
					$this->addUserEndMsg('ERRORS', _('Historization failed.'));
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
		#Include CKEditor
		$this->addJS(WEB_ROOT.'/js/ckeditor/ckeditor.js');
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
		$aid			= $this->Category->aid;
		$catname		= $this->_article->escape($values['category']['name']);
		if ($initialCatName != $catname && $this->Category->load("name='$catname'")){
			$this->_view->addUserEndMsg('ERRORS', sprintf(_('There is already a category named "%s"'), $values['category']['name']));
			return false;
		}
		#Check duplicates for article fields
		#Url alias
		if (!empty($values['article']['request_uri'])){
			$this->_article->load('aid='.$aid);
			$initialUrlAlias= $this->_article->escape($this->_article->request_uri);
			$urlAlias		= $this->_article->escape($values['article']['request_uri']);
			if ($initialUrlAlias != $urlAlias && $this->_article->load("request_uri='$urlAlias'")){
				$this->_view->addUserEndMsg('ERRORS', sprintf(_('There is already a web page with the Url alias "%s"'), $urlAlias));
				return false;
			}
		}
		return true;
	}
}