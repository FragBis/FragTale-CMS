<?php
namespace Bonz\Controller\Forms;
use Bonz\Controller;
use Bonz\Application;
use Bonz\CMS\Article_Comments as CMS_COM;

class Article_Comments extends Controller{
	
	protected $Comments;
	protected $aid;
	
	function initialize(){
		$this->Comments = new CMS_COM();
	}
	
	function doPostBack(){
		if (!isset($_POST['post_comment']))
			return false;
		$values['aid']		= $this->aid;
		$values['message']	= $_POST['article_comments'];
		$values['uid']		= $_SESSION['REG_USER']['uid'];
		$this->Comments->insert($values);
		return true;
	}
	
	function main(){
		if (empty($this->_meta_article->aid)){
			$this->catchError('Article ID should not be missing.', __CLASS__, __FUNCTION__, __LINE__);
			return;
		}
		$this->aid = $this->_meta_article->aid;
		$this->_view->article_comments = $this->Comments->getComments($this->aid);
	}
}