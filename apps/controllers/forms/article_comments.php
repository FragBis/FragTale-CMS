<?php
namespace FragTale\Controller\Forms;
use FragTale\Controller;
use FragTale\Application;
use FragTale\CMS\Article_Comments as CMS_COM;

class Article_Comments extends Controller{
	
	protected $Comments;
	protected $aid;
	
	function initialize(){
		$this->Comments = new CMS_COM();
		if (empty($this->_meta_article->aid)){
			$this->catchError('Article ID should not be missing.', __CLASS__, __FUNCTION__, __LINE__);
			return;
		}
		$this->aid = $this->_meta_article->aid;
	}
	
	function doPostBack(){
		if (empty($_POST)) return false;
		if (isset($_POST['post_comment']) && !empty($_POST['article_comments'])){
			$values['aid']		= $this->aid;
			$values['message']	= $_POST['article_comments'];
			$values['uid']		= $_SESSION['REG_USER']['uid'];
			if (!$this->Comments->insert($values)){
				$this->addUserEndMsg('ERRORS', _('Error occured'));
			}
		}
		elseif (!empty($_POST['delete_comment'])){
			if (!$this->Comments->delete('acid='.$_POST['delete_comment']))
				$this->addUserEndMsg('ERRORS', _('Error occured'));
		}
		elseif (!empty($_POST['upd_comment']) && !empty($_POST['article_comments'])){
			$acid = $_POST['upd_comment'];
			$values['message'] = $_POST['article_comments'];
			if (!$this->Comments->update('acid='.$acid, $values)){
				$this->addUserEndMsg('ERRORS', _('Error occured'));
			}
		}
		$this->redirectToSelf('add_comments');
	}
	
	function main(){
		$this->_view->article_comments = $this->Comments->getComments($this->aid);
	}
}