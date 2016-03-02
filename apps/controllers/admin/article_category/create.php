<?php
namespace FragTale\Controller\Admin\Article_Category;
use FragTale\Controller\Admin;
use FragTale\CMS\Article_Category as CMS_CAT;
use FragTale\CMS\Article as CMS_ART;
use FragTale\CMS\Files;


/**
 * 
 * @author fragbis
 *
 */
class Create extends Admin{
	var $Category;
	var $Article;
	
	function initialize(){
		$this->Category = new CMS_CAT();
		$this->Article	= new CMS_ART();
	}
	
	function doPostBack(){
		if (!isset($_POST['register']))
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
					$keys = array_keys($files);
					$values['fid'] = reset($keys);
				}
			}
		}
		else
			$values['fid'] = $_POST['category']['selected_fid'];
		
		if (empty($values['parent_catid']))
			unset($values['parent_catid']);
		$values['upd_uid'] = $this->getUser()->uid;
		$values['cre_uid'] = $this->getUser()->uid;
		$values['cre_date'] = date('Y-m-d H:i:s');
		#Insert new category
		$this->getDb()->exec('LOCK TABLE '.$this->Category->getFullTableName().';');
		if ($this->Category->insert($values)){
			$catid = $this->Category->selectMax('catid');
			if ($this->Category->load("catid='$catid'")){
				$this->_view->addUserEndMsg('SUCCESS', _('New category successfully added.'));
				#Insert new article bound to this category.
				$values = $_POST['article'];
				$values['catid']	= $this->Category->catid;
				$values['publish']	= isset($values['publish']) ? 1 : 0;
				$values['uid']		= $this->getUser()->uid;
				$values['owner_id'] = $this->getUser()->uid;
				$values['cre_date'] = date('Y-m-d H:i:s');
				$values['position'] = (int)$this->Article->selectMax('position') + 1;
				$this->Article->insert($values);
				$urlAlias = $this->Article->escape($values['request_uri']);
				if ($this->Article->load("request_uri='$urlAlias'")){
					if ($this->Category->update('catid='.$this->Category->catid, array('aid'=>$this->Article->aid)))
						$this->addUserEndMsg('SUCCESS', _('New article successfully added and linked to this category.'));
					else
						$this->addUserEndMsg('ERRORS', _('Unexpected error while linking article to category.'));
				}
				else{
					$this->addUserEndMsg('ERRORS', _('Failed to add new article.'));
				}
				$this->getDb()->exec('UNLOCK TABLES;');
				$this->redirect(ADMIN_WEB_ROOT.'/article_category/edit?catid='.$this->Category->catid);
			}
			else{
				$this->addUserEndMsg('ERRORS', _('Error while loading new category.'));
			}
		}
		if (!empty($values['catid']))
			$this->Category->delete('catid='.$values['catid']);
		$this->addUserEndMsg('ERRORS', _('Failed to add new category.'));
		return false;
	}
	
	function main(){
		$this->_view->article_category = new CMS_CAT();
		$this->_view->setTitle(_('Add a new category of articles'));
		#Include ckeditor
		$this->_view->addJS(WEB_ROOT.'/js/ckeditor/ckeditor.js');
	}
	
	/**
	 * Check if certain values already exist into Category and Article tables.
	 * @param array $values $_POST
	 * @return boolean
	 */
	function checkDuplicates($values){
		#Check category name
		$catname = $this->Category->escape($values['category']['name']);
		if ($this->Category->load("name='$catname'")){
			$this->addUserEndMsg('ERRORS', sprintf(_('There is already a category named "%s"'), $values['category']['name']));
			return false;
		}
		#Check duplicates for article fields
		#Url alias
		if (!empty($values['article']['request_uri'])){
			$urlAlias = $this->Article->escape($values['article']['request_uri']);
			if ($this->Article->load("request_uri='$urlAlias'")){
				$this->addUserEndMsg('ERRORS', _('There is already a web page with this Url alias: ').'"'.$urlAlias.'"');
				return false;
			}
		}
		return true;
	}
}