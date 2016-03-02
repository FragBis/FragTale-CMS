<?php
namespace FragTale\Controller\Blocks;
use FragTale\Controller;
use FragTale\CMS\Files AS F;
use FragTale\CMS\Article_Category AS C;

/**
 * @author fabrice
 */
class Press_Review extends Controller{
	function main(){
		$C = new C();
		$F = new F();
		$C->load('catid=5');
		$query = 'SELECT '.
				'A.aid, '.
				'A.catid, '.
				'A.title, '.
				'A.request_uri, '.
				'F.path AS image_path '.
			'FROM '.$this->getArticle()->getFullTableName().' AS A '.
				'INNER JOIN '.$C->getFullTableName().' AS C ON A.catid = C.catid AND A.aid <> C.aid '.
				'INNER JOIN '.$F->getFullTableName().' AS F ON F.fid = A.fid '.
			'WHERE C.catid = 5 AND A.publish = 1 '.
			'ORDER BY A.position DESC LIMIT 0,5;';
		$this->_view->newspaper_articles = $this->getDb()->getTable($query);
		$this->_view->review_page = $this->getArticle()->selectRow('aid='.$C->aid);
	}	
}
