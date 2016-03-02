<?php
namespace FragTale\Controller\Admin;
use FragTale\Controller\Admin;
use FragTale\CMS\Parameters;
use FragTale\CMS\Files;

/**
 * @author fragbis
 */
class File_Manager extends Admin{
	protected $File;
	
	function initialize(){
		$this->File = new Files();
		$this->setTitle(_('File manager'));
		$this->addJS(WEB_ROOT.'/js/admin/datagrid.js');
	}
	
	function doPostBack(){
		if (empty($_POST)) return;
		$this->deleteFile();
		$this->addFiles();
		$this->redirect($_SERVER['REQUEST_URI']);
	}
	
	function main(){
		$this->_view->files['whole_db_list'] = $this->getGridSortedResult($this->File);		
		
		$param = new Parameters();
		if ($param->load("param_key='FILES_NOT_IN_DB'"))
			$this->_view->files['not_in_db'] = @unserialize($param->param_value);
		if ($param->load("param_key='FILES_NOT_IN_DIR'"))
			$this->_view->files['not_in_dir'] = @unserialize($param->param_value);
	}
	
	/**
	 * Remove the files stored in DB and unlink the physical file
	 */
	function deleteFile(){
		if (empty($_POST['delete'])) return;
		foreach ($_POST['delete'] as $fid=>$ok){
			if (!empty($fid)){
				if ($this->File->load('fid='.$fid)){
					if ($this->File->delete('fid='.$fid)){
						if (file_exists(PUB_ROOT.$this->File->path))
							unlink(PUB_ROOT.$this->File->path);
						$this->_view->addUserEndMsg('SUCCESS', sprintf(_('File identified by ID #%s successfully removed.'), $this->File->filename));
					}
				}
			}
		}
	}
	
	function addFiles(){
		#File upload
		if (!empty($_FILES['file']))
			$this->File->store($_FILES['file']);
	}
}