<?php
namespace FragTale\Controller\Blocks;
use FragTale\Controller;
use FragTale\CMS\Files;

/**
 * @author fabrice
 */
class Picture_Selector extends Controller{
	
	static $loadCount = 0;
	
	function initialize(){
		self::$loadCount++;
		if ($this->_view->isMeta())
			$this->setLayout('clean');
	}
	
	function main(){
		if (self::$loadCount <= 1){
			$File = new Files();
			$this->_view->pictures = $File->select("mime_type LIKE 'image%'", null, 'filename');
		}
	}
}