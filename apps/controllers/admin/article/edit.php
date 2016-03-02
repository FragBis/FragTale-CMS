<?php
namespace FragTale\Controller\Admin\Article;
use FragTale\Controller\Admin;
use FragTale\CMS\Article;
use FragTale\CMS\Article_Category;
use FragTale\CMS\Files;
//use FragTale\CMS\Article_Custom_Fields;


/**
 * 
 * @author fragbis
 *
 */
class Edit extends Admin{
	
	protected $aid;
	//protected $custom_fields = array();
	
	function initialize(){
		if (empty($_GET['aid']))
			$this->redirect(ADMIN_WEB_ROOT.'/articles');
		$this->aid = (int)trim($_GET['aid']);
		if (!$this->_article->load("aid=$this->aid")){
			$msg = _('Unexpected error: unable to load article properly.');
			$this->catchError($msg, __CLASS__, __FUNCTION__, __LINE__);
			$this->addUserEndMsg('ERRORS', $msg);
		}
		
		#Check whether if it's an article category
		$Cat = $this->getArticle()->getCategory();
		if (!empty($Cat->aid) && ($this->getArticle()->aid == $Cat->aid)){
			$this->redirect(ADMIN_WEB_ROOT.'/article_category/edit?catid='.$this->getArticle()->getCategory()->catid);
		}
		
		// if ALERT_SENT == false, display the sending button
		/*$this->custom_fields['ALERT_SENT'] = array(
				'input_type'=>'checkbox',
				'field_name' => _('Send an email alert')
		);*/
	}
	
	/**
	 * Process post back action
	 * @return boolean
	 */
	function doPostBack(){
		#Delete article
		if (isset($_POST['delete'])){
			if ($this->_article->delete("aid=$this->aid")){
				$this->addUserEndMsg('SUCCESS', _('Article successfully removed'));
				$this->redirect(ADMIN_WEB_ROOT.'/articles?order=aid&desc=1');
			}
			else{
				$this->addUserEndMsg('ERRORS', _('Error occured'));
				$this->redirectToSelf();
			}
		}
		if (!isset($_POST['upd']) && !isset($_POST['historicize']) && !isset($_POST['upd_historicize'])) return false;
		$NewArticle = clone $this->_article;
		#Update informations
		if (isset($_POST['upd'])){
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
			$values['uid'] = $this->getUser()->uid;
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
		$this->setTitle(_('Article edition'));
		
		# Get custom fields
		/*$ArticleCustomFields = new Article_Custom_Fields();
		$custom_fields = array();
		if ($result = $ArticleCustomFields->select('aid='.$this->aid))
			foreach ($result as $row){
				$custom_fields[$row['field_key']] = $row;
			}
		foreach ($this->custom_fields as $field_key=>$values){
			if (!isset($custom_fields[$field_key])){
				$custom_fields[$field_key] = $values;
			}
		}
		if (!isset($custom_fields['ALERT_SENT']['field_value']))
			$custom_fields['ALERT_SENT']['field_value'] = false;
		$this->_view->custom_fields = $custom_fields;*/
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