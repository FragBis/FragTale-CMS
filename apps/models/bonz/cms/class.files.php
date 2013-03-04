<?php
namespace Bonz\CMS;
use \Bonz\Db\Table;

class Files extends Table{
	protected $_tablename = 'files';

	/**
	 * Primary key
	 * @var int
	 */
	var $fid;
	/**
	 * Relative path from website
	 * @var string
	 */
	var $path;
	/**
	 * File name (must be unique)
	 * @var string
	 */
	var $filename;
	/**
	 * MIME-TYPE
	 * @var string
	 */
	var $mime_type;
	/**
	 * File size in octet
	 * @var int
	 */
	var $size;
	
	
	function get($values){
		$conditions = '';
		if (isset($values['search'])){
			$search = $values['search'];
			$conditions = "filename LIKE '$search%' ORDER BY filename";
		}
		return $this->select($conditions);
	}
	
	
	function store($_files){
		if (empty($_files)) return;
		$files = array();
		foreach($_files['name'] as $key=>$filenames){
			if (is_array($filenames)){
				foreach ($filenames as $i=>$filename){
					$type		= $_files['type'][$key][$i];
					$tmp_name	= $_files['tmp_name'][$key][$i];
					$error		= $_files['error'][$key][$i];
					$size		= $_files['size'][$key][$i];
					$this->processingFile($filename, $type, $tmp_name, $error, $size, $files);
				}
			}
			else{
				$filename	= $filenames;
				$type		= $_files['type'][$key];
				$tmp_name	= $_files['tmp_name'][$key];
				$error		= $_files['error'][$key];
				$size		= $_files['size'][$key];
				$this->processingFile($filename, $type, $tmp_name, $error, $size, $files);
			}
		}
		return $files;
	}
	
	function processingFile($filename, $type, $tmp_name, $error, $size, &$files){
		if (empty($filename))
			return;
		if ($error){
			$_SESSION['USER_END_MSGS']['ERRORS'][] = _('Unable to upload ').$filename.' : '.$error;
			return;
		}
		
		$upDir = PUB_ROOT.'/uploads/';
		#Check permissions
		if (!is_writeable($upDir)){
			$msg = _('You must allow Apache to have recursive permissions to write upon ').$upDir;
			if (empty($_SESSION['USER_END_MSGS']['ERRORS']) || !in_array($msg, $_SESSION['USER_END_MSGS']['ERRORS']))
				$_SESSION['USER_END_MSGS']['ERRORS'][] = $msg;
		}
		#Create folder if not exist
		$toMkdir = explode('/', $type);
		$concatFolder = '';
		foreach ($toMkdir as $folder){
			$concatFolder.= $folder.'/';
			$tmpFolder = $upDir.$concatFolder;
			if (!is_dir($tmpFolder))
				mkdir($tmpFolder);
		}
		$filepath = $upDir.$type.'/'.$filename;
		$relativepath = str_replace(PUB_ROOT, '', $filepath);
		#Insert
		if ($this->load("path='".$this->escape($relativepath)."'")){
			$_SESSION['USER_END_MSGS']['WARNINGS'][] = $filepath._(': this file already exists. You should delete it first or rename your file. Anyway, the item will be linked with the existing file.');
			$files[$this->fid] = $filename;
			return;
		}
		if (file_exists($filepath))
			unlink($filepath);
		if (!copy($tmp_name, $filepath)){
			$_SESSION['USER_END_MSGS']['ERRORS'][] = $filepath._(': error occured while transferring this file into the uploads directory.');
			return;
		}
		if ($this->insert(array('path'=>$relativepath, 'filename'=>$filename, 'mime_type'=>$type, 'size'=>$size))){
			$_SESSION['USER_END_MSGS']['SUCCESS'][] = $filepath._(': file successfully transferred in server file system and its informations stored into the database.');
			$this->load("path='".$this->escape($relativepath)."'");
			$files[$this->fid] = $filename;
		}

	}
}