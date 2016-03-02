<?php
namespace FragTale\Controller\Blocks;
use FragTale\Controller;
use FragTale\CMS\Article_Category;

/**
 * @author fabrice
 */
class Category_Carousel extends Controller{
	function main(){
		$Category	= new Article_Category();
		$categories = !isset($this->_meta_view->category_tree) ? $Category->getTree(null, 2, true) : $this->_meta_view->category_tree;
		if (!empty($this->_meta_article->catid)){
			if ($Category->load('catid='.$this->_meta_article->catid))
				$catid = (!empty($Category->parent_catid)) ? $Category->parent_catid : $Category->catid;
			else
				$this->addUserEndMsg('ERRORS', _('Error on category load.'));
			$this->_view->subCategories = !empty($catid) && !empty($categories[$catid]['children']) ? $categories[$catid]['children'] : array();
		}
		## Give the data to global view intending to get them into another block (i.e footer)
		$this->_view->categories = $this->_meta_view->category_tree = $categories;
	}
}