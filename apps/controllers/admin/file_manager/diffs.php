<?php
namespace FragTale\Controller\Admin\File_Manager;
use FragTale\Controller\Admin;
use FragTale\CMS\Parameters;
use FragTale\CMS\Files;

/**
 * @author fabrice
 */
class Diffs extends Admin{
	
	protected $File;
	
	function initialize(){
		$this->File = new Files();
		$this->setTitle(_('Check file diffs'));
	}
	
	function doPostBack(){
		if (empty($_POST)) return;
		$this->deleteFile();
		$this->erasePhysicalFiles();
		$this->redirect($_SERVER['REQUEST_URI']);
	}
	
	function main(){
		#Get files
		$this->_view->files['whole_db_list'] = $this->File->select();
		
		#Get file names placed into the upload folder
		if (empty($this->_view->files['scanned_in_dir']))
			$this->_view->files['scanned_in_dir']= $this->scan_upload_dir();
		
		#Match all unexisting files that are into the file system but not into the database
		$this->_view->files['not_in_db'] = array();
		foreach ($this->_view->files['scanned_in_dir'] as $path=>$filename){
			$in = false;
			foreach ($this->_view->files['whole_db_list'] as $file){
				if ($file['filename']==$filename){
					$in = true;
					break;
				}
			}
			if (!$in)
				$this->_view->files['not_in_db'][$path] = $filename;
		}
		
		#Match all unexisting files that are into the database but not into the file system
		$this->_view->files['not_in_dir'] = array();
		foreach ($this->_view->files['whole_db_list'] as $file){
			if (!array_search($file['filename'], $this->_view->files['scanned_in_dir']))
				$this->_view->files['not_in_dir'][] = $file;
		}
		
		$param = new Parameters();
		if (!$param->load("param_key='FILES_NOT_IN_DB'"))
			$param->insert(array('param_key'=>'FILES_NOT_IN_DB', 'param_value'=>serialize($this->_view->files['not_in_db'])));
		else
			$param->update("param_key='FILES_NOT_IN_DB'", array('param_value'=>serialize($this->_view->files['not_in_db'])));
		if (!$param->load("param_key='FILES_NOT_IN_DIR'"))
			$param->insert(array('param_key'=>'FILES_NOT_IN_DIR', 'param_value'=>serialize($this->_view->files['not_in_dir'])));
		else
			$param->update("param_key='FILES_NOT_IN_DIR'", array('param_value'=>serialize($this->_view->files['not_in_dir'])));
	}
	
	/**
	 * Returns all file names placed into the uploads folder (recursive search).
	 * Optionnally, if "$files_to_unlink" is passed, we check the file path and unlink the specified file.
	 * @param string	$path
	 * @param array		$files_to_unlink
	 * @return array
	 */
	function scan_upload_dir($path='', $files_to_unlink=array()){
		if (empty($path))
			$path = PUB_ROOT.'/uploads';
		$files = array();
		$handle = opendir($path);
		while ($subpath = readdir($handle)){
			if (in_array($subpath, array('.', '..', '.svn')))
				continue;
			if (is_dir($path.'/'.$subpath))
				$files = array_merge($files, $this->scan_upload_dir($path.'/'.$subpath, $files_to_unlink));
			elseif(isset($files_to_unlink[$subpath])){
				unlink($path.'/'.$subpath);
				$this->_view->addUserEndMsg('SUCCESS', $path.'/'.$subpath.' '._('successfully removed'));
			}
			else{
				$fullpath = realpath($path.'/'.$subpath);
				$file = escapeshellarg($fullpath);
				$mime = shell_exec('file -bi '.$file);
				$mime = explode(';', $mime);
				$files[$mime[0].' '.str_replace(PUB_ROOT, '', $fullpath)] = $subpath;
			}
		}
		return $files;
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
	/**
	 * Definitively erase files from upload folder
	 */
	function erasePhysicalFiles(){
		if (!empty($_POST['erase']))
			$this->_view->files['scanned_in_dir']= $this->scan_upload_dir(null, $_POST['erase']);
	}
}