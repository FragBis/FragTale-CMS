<?php
namespace FragTale\Controller\Admin\Blocks;
use FragTale\CMS\Article;
use FragTale\Controller\Admin;
use FragTale\CMS\Article_Custom_Fields;
use FragTale\Debug;

/**
 * @author fabrice
 */
class Custom_Fields extends Admin{
	
	protected $input_types;
	
	function initialize(){
		$this->input_types = array(
			'text'	=>array(
				'label'	=>_('Text'),
				'desc'	=>_('Text Input')
				),
			'html'	=>array(
				'label'	=>_('HTML'),
				'desc'	=>_('HTML Content In Wysiwyg')
				),
			'link'	=>array(
				'label'	=>_('Link'),
				'desc'	=>_('Hyperlink')
				),
			'image'	=>array(
				'label'	=>_('Image'),
				'desc'	=>_('Picture')
				),
			/*'video'	=>array(
				'label'	=>_('Video'),
				'desc'	=>_('Any video file')
				),
			'audio'	=>array(
				'label'	=>_('Audio'),
				'desc'	=>_('Any sound file')
				),*/
			'media'	=>array(
				'label'	=>_('Media'),
				'desc'	=>_('Media File'),
				'types'	=>array(
					'video'	=>_('Video'),
					'audio'	=>_('Audio'),
					'pdf'	=>_('PDF'),
					'flash'	=>_('Flash'),
					'iframe'=>_('Frame'),
					'txt'	=>_('Text')
					)
				),
			/*'date'	=>array(
				'label'	=>_('Date'),
				'desc'	=>_('Pickup or enter a date')
				),
			'text_group'	=>array(
				'label'	=>_('Text group'),
				'desc'	=>_('Multiple text input')
				),
			'image_group'	=>array(
				'label'	=>_('Image group'),
				'desc'	=>_('Multiple pictures selection')
				),
			'media_group'	=>array(
				'label'	=>_('Media group'),
				'desc'	=>_('Multiple medias selection')
				)*/
		);
		$this->_view->input_types = $this->input_types;
	}
	
	function main(){
		$this->_view->custom_fields = $this->_meta_article->getCustomFields();
		## Adding default meta custom fields
		foreach (array(
				'meta_description'=>_('Meta description of webpage'),
				'meta_keywords'=>_('Keywords for search engine'),
				'meta_title'=>_('Meta title of webpage (if this field is empty, meta title is article title)'),
			) as $key=>$name){
			$found = false;
			foreach ($this->_view->custom_fields as $field){
				if ($field['field_key']==$key){
					$found = true;
					break;
				}
			}
			if (!$found){
				array_unshift($this->_view->custom_fields, array(
					'input_type'	=>'text',
					'field_key'		=>$key,
					'field_name'	=>$name,
					'field_value'	=>''
				));
			}
		}
			
		$this->_meta_view->custom_fields = $this->_view->custom_fields;
		if (!empty($_REQUEST['refresh'])){
			$this->refresh();
		}
	}
	
	function doPostBack(){
		if (empty($_POST['custom_field'])) return false;
		## First: delete all existing custom fields
		$CustField = new Article_Custom_Fields();
		$CustField->delete('aid='.$this->_meta_article->aid);
		## Insert new ones
		foreach ($_POST['custom_field'] as $field){
			if (empty($field['field_key']) || empty($field['field_name']) || empty($field['input_type']))
				continue;
			$field['field_key'] = preg_replace('/[^a-zA-Z0-9]+/', '_', $field['field_key']);
			$values = array(
				'aid'		=>$this->_meta_article->aid,
				'field_key'	=>$field['field_key'],
				'field_name'=>$field['field_name'],
				'input_type'=>$field['input_type'],
				'position'	=>$field['position']
			);
			$values['field_value'] = is_array($field['field_value']) ?
				@serialize($field['field_value']) : $field['field_value'];
			$CustField->insert($values);
		}
		$this->addUserEndMsg('SUCCESS', _('Custom fields changes saved'));
		$this->redirectToSelf('custom_fieldset');
	}
	
	function refresh(){
		die(json_encode($value));
	}
}