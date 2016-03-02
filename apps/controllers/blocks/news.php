<?php
namespace FragTale\Controller\Blocks;
use FragTale\Controller;
use FragTale\CMS\Article_Category AS CAT;

/**
 * @author fabrice
 */
class News extends Controller{
	function main(){
		$this->_view->isCategoryList = 1;
		$aid = $this->_meta_article->aid;
		$query =
		"SELECT A.aid, A.uid, A.catid, A.request_uri, A.title, A.signature, A.summary, A.edit_date, F.path AS image_path
			FROM article AS A
				INNER JOIN article_category AS C ON A.aid = C.aid
				LEFT JOIN files AS F ON F.fid = C.fid
		WHERE A.publish=1 AND A.aid <> '$aid' ";
		if (empty($this->_meta_article->catid))
			#Get any articles
			$query .= 'ORDER BY A.cre_date DESC, A.aid DESC LIMIT 0,10';
		else{
			$catid = $this->_meta_article->catid;
			$category = new CAT();
			$category->load('catid='.$catid);
			if (empty($category->parent_catid))
				#Get the sub categories of the top category
				$query .= "AND C.parent_catid = '$catid' ORDER BY A.edit_date DESC, A.aid DESC";
			else{
				$this->_view->isCategoryList = 0;
				#Get only the articles that belong to the sub category
				$query = str_replace('ON A.aid = C.aid', 'ON A.catid = C.catid', $query)."AND C.catid='$catid' AND A.aid <> C.aid ORDER BY A.edit_date DESC, A.aid DESC LIMIT 0,10";
			}
		}
		$this->_view->news = $this->getDb()->getTable($query);
	}	
}
