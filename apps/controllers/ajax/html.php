<?php 
namespace FragTale\Controller\Ajax;
use FragTale\Controller;

/**
 * 
 * @author fragbis
 *
 */
class Html extends Controller{
	function main(){
		try{
			if (!empty($_REQUEST['block']) &&
					(file_exists(APP_ROOT.'/controllers/'.$_REQUEST['block'].'.php') ||
					file_exists(TPL_ROOT.'/views/'.$_REQUEST['block'].'.phtml'))
				){
				$vars = array();
				foreach ($_REQUEST as $k=>$v){
					if ($k!='block') $vars[$k] = $v;
				}
				die($this->_view->getBlock($_REQUEST['block'], $vars));
			}
		}
		catch(\Exception $e){
			$this->throwError($e->getMessage());
		}
		$this->throwError(_('Bad Request'));
	}
	function throwError($message){
		header('HTTP/1.0 404 Not Found');
		die($message);
	}
}