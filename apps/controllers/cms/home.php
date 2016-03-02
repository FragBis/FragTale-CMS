<?php
namespace FragTale\Controller\Cms;
use FragTale\Controller;
use FragTale\JPT\User_Rating;

/**
 * @author fabrice
 */
class Home extends Controller{
	function main(){
		$this->setTitle($this->_article->title);
		
		$query =
		'SELECT A.aid, A.uid, A.catid, A.request_uri, A.title, A.signature, A.summary, A.edit_date, F.path AS image_path
		FROM article AS A
			INNER JOIN article_category AS C ON A.catid = C.catid AND A.aid  <> C.aid
			LEFT JOIN files AS F ON F.fid = C.fid
		WHERE A.publish=1 ORDER BY A.cre_date DESC, A.aid DESC LIMIT 0,10';
		$this->_view->news = $this->getDb()->getTable($query);
		$this->_view->custom_fields = $this->getArticle()->getCustomFields();
		# User rates
		$UserRating = new User_Rating();
		$this->_view->rate		= (float)$UserRating->selectValue("type='procedure'", 'AVG(rate) AS average');
		$this->_view->rate		= floor($this->_view->rate * 2) / 2;
		if (empty($this->_view->rate))
			$this->_view->rate = 2.5;
		$this->_view->nbVotes	= (int)$UserRating->count("type='procedure'");
	}
}