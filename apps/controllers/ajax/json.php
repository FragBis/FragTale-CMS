<?php 
namespace Bonz\Controller\Ajax;
use Bonz\Controller;

/**
 * 
 * @author fragbis
 *
 */
class Json extends Controller{
	function initialize(){
		#Since JSON data don't need any layout, we set the "clean" one.
		$this->_view->setLayoutScript(TPL_ROOT.'/pages/clean.layout.phtml');
	}
	function main(){
		#Calling precisely object & method to execute
		if (isset($_REQUEST['model']) && isset($_REQUEST['class']) && isset($_REQUEST['method'])){
			$fullClassName = '\\'.$_REQUEST['model'].'\\'.$_REQUEST['class'];
			if (!class_exists($fullClassName))
				$fullClassName = '\\Bonz\\'.$_REQUEST['model'].'\\'.$_REQUEST['class'];
			if (class_exists($fullClassName)){
				$obj = new $fullClassName();
				$method = $_REQUEST['method'];
				if (method_exists($obj, $method)){
					$this->_view->results = $obj->$method($_REQUEST);
					return;
				}
			}
		}
		#Else, send error
		header('HTTP/1.0 404 Not Found');
		$this->_view->results = false;
	}
}