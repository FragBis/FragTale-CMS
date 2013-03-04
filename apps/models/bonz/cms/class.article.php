<?php
namespace Bonz\CMS;
use Bonz\Db\Table;

class Article extends Table{
	protected $_tablename = 'article';
	/**
	 * Primary key
	 * @var int
	 */
	var $aid;
	/**
	 * Reference to User
	 * @var int
	 */
	var $uid;
	/**
	 * Reference to article_category
	 * @var int
	 */
	var $catid;
	/**
	 * Its view name
	 * @var string
	 */
	var $view;
	/**
	 * Degre of accessibility: 1=Only for super-admin, 2=For administrators, etc.
	 * @var int
	 */
	var $access;
	/**
	 * Url alias
	 * @var string
	 */
	var $request_uri;
	/**
	 * Article title
	 * @var string
	 */
	var $title;
	/**
	 * Article summary (to display into news box)
	 * @var string
	 */
	var $summary;
	/**
	 * Article HTML body content
	 * @var string
	 */
	var $body;
	/**
	 * Small ending message before signature
	 * @var string
	 */
	var $greeting_text;
	/**
	 * If null, get the author name or login
	 * @var string
	 */
	var $signature;
	/**
	 * Publishing or not?
	 * @var boolean
	 */
	var $publish;
	/**
	 * Last edit date
	 * @var Timestamp
	 */
	var $edit_date;
	
	/**
	 * @return Article_Category
	 */
	function getCategory(){
		if (!$this->catid)
			return false;
		static $Category;
		if (!empty($Category->catid))
			return $Category;
		$Category = new Article_Category();
		$Category->load('catid='.$this->catid);
		return $Category;
	}
	
	/**
	 * @return boolean
	 */
	function historicize(){
		if (empty($this->aid)){
			\Bonz\Application::catchError('The article has not been loaded before its historization.', __CLASS__, __FUNCTION__, __LINE__);
			return false;
		}
		$AH = new Article_History();
		$values = (array)$this;
		foreach ($values as $key=>$value){
			if (substr($key, 0, 1)==='_' || !property_exists($AH, $key))
				unset($values[$key]);
		}
		return $AH->insert($values);
	}
	
	/**
	 * On update, check if the author has changed or if the date is a day after.
	 * @param Article $oldOne
	 * @return boolean
	 */
	function autoHistoricize(Article $oldOne){
		if (empty($this->aid) || empty($oldOne->aid)){
			\Bonz\Application::catchError('You must load the Article objects before using "autoHistoricize" method.', __CLASS__, __FUNCTION__, __LINE__);
			return false;
		}
		if ($oldOne->uid!==$this->uid)
			return $oldOne->historicize();
		if (date('Ymd')>date('Ymd', strtotime($oldOne->edit_date)))
			return $oldOne->historicize();
		return false;
	}
	
}

class Article_History extends Table{
	protected $_tablename = 'article_history';
	/**
	 * Primary key
	 * @var int
	 */
	var $ahid;
	/**
	 * Reference to Article
	 * @var int
	 */
	var $aid;
	/**
	 * Reference to User
	 * @var int
	 */
	var $uid;
	/**
	 * Reference to article_category
	 * @var int
	 */
	var $catid;
	/**
	 * Its view name
	 * @var string
	 */
	var $view;
	/**
	 * Degre of accessibility: 1=Only for super-admin, 2=For administrators, etc.
	 * @var int
	 */
	var $access;
	/**
	 * Url alias
	 * @var string
	 */
	var $request_uri;
	/**
	 * Article title
	 * @var string
	 */
	var $title;
	/**
	 * Article summary (to display into news box)
	 * @var string
	 */
	var $summary;
	/**
	 * Article HTML body content
	 * @var string
	 */
	var $body;
	/**
	 * Small ending message before signature
	 * @var string
	 */
	var $greeting_text;
	/**
	 * If null, get the author name or login
	 * @var string
	 */
	var $signature;
	/**
	 * Last edit date
	 * @var Timestamp
	 */
	var $edit_date;
}