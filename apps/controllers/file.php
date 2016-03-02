<?php
namespace FragTale\Controller;
use FragTale\Controller;

require_once LIB_ROOT.'/doc2pdf.php';

class File extends Controller{
	
	function main(){
		$Converter = new \Doc2PDF(
			TPL_ROOT.'/docs/acte.docx',
			array(
					'civility'=>'Monsieur',
					'firstname'=>'Fabrice',
					'lastname'=>'Dant',
					'nationality'=>'Française'
			),
			TPL_ROOT.'/docs/test'
		);
		$this->send($Converter->getNewPDFFilename());
	}
	
	function send($filename){
		if (!file_exists($filename)){
			
			die($filename.' does not exist');
		}
		http_send_content_disposition(end(explode('/', $filename)), true);
		http_send_content_type(mime_content_type($filename));
		http_throttle(0.1, 2048);
		http_send_file($filename);
	}
}