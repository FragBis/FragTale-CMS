<?php
namespace FragTale\Controller\Admin;
use FragTale\CMS\Parameters;
use FragTale\Controller\Admin;
use FragTale\Controller;
use FragTale\CMS\Article;

/**
 * @author fabrice
 */
class System extends Admin{
	
	protected $params;
	protected $serializedParams;
	protected $Param;
	
	function initialize(){
		/**
		 * Define here all additionnal parameters
		 */
		$this->params = array(
			'scalar'=>array(
				_('General')=>array(
					'CONTACT_EMAIL'	=> array(
						'label'			=>_('E-mail to "Contact Us"'),
						'description'	=>_('E-mail of the person in charge of the "Contact Us" mails')
					),
					'ADMIN_EMAIL'	=> array(
						'label'			=>_('Admin e-mail'),
						'description'	=>_('The super-admin e-mail address')
					),
					'NOREPLY_EMAIL'	=> array(
						'label'			=>_('"No reply" e-mail'),
						'description'	=>_('Fake sender address for the e-mail sent by the website')
					),
				),
				'Google'	=>array(
					'GOOGLE_ANALYTICS_ID'	=> array(
						'label'			=>_('Google Analytics follower ID'),
						'description'	=>_('Type here your GA ID intending to send and collect some informations for Google')
					),
					'GOOGLE_TAGMANAGER'	=> array(
						'label'			=>_('Google Tag Manager'),
						'description'	=>_('Paste here the entire code given by Google to include the Google Tagmanager stuffs')
					),
					'GOOGLE+_PAGE_URL'	=> array(
						'label'			=>_('Google+ Page'),
						'description'	=>_('Your Google+ Page URL')
					),
				),
				'Open Graph'=>array(
					'WEBSITE_NAME'	=>array(
						'label'			=>_('Your Website name'),
						'description'	=>_('This will be used for example into the Open Graph meta tags')
					),
					'WEBSITE_IMAGE_URL'	=> array(
						'label'			=>_('My website image file URL (for Facebook)'),
						'description'	=>_('Image that will be bound to your website (on Facebook)')
					),
					'WEBSITE_VIDEO_URL'	=> array(
						'label'			=>_('My website video file URL (for Facebook)'),
						'description'	=>_('Video that will be bound to your website (on Facebook)')
					),
					'WEBSITE_AUDIO_URL'	=> array(
						'label'			=>_('My website audio file URL (for Facebook)'),
						'description'	=>_('Audio that will be bound to your website (on Facebook)')
					),
				),
				'Facebook'	=>array(
					'FACEBOOK_URL'	=> array(
						'label'			=>_('Facebook page'),
						'description'	=>_('Url of your personal facebook page')
					),
					'FACEBOOK_PAGE_ID'	=> array(
						'label'			=>_('Facebook page ID'),
						'description'	=>_('ID of your personal facebook page')
					),
				),
				'Twitter'	=>array(
					'TWITTER_URL'	=> array(
						'label'			=>_('Twitter page'),
						'description'	=>_('Url of your personal Twitter page')
					),
				),
				'PayPal'	=>array(
					'PAYPAL_EMAIL'	=> array(
						'label'			=>_('PayPal e-mail'),
						'description'	=>_('E-mail address of the PayPal business account')
					),
					'PAYPAL_SUBMISSION_URL'	=> array(
						'label'			=>_('PayPal submission URL'),
						'description'	=>_('Redirection through the PayPal payment system')
					),
				),
				'Atos'	=>array(
					'ATOS_PATHFILE'	=> array(
						'label'			=>_('Pathfile'),
						'description'	=>_('Path to pathfile')
					),
					'ATOS_MERCHANT_ID'	=> array(
							'label'			=>_('Merchant ID'),
							'description'	=>_('Your merchant ID, normally given by your bank')
					),
					'ATOS_MERCHANT_COUNTRY'	=> array(
							'label'			=>_('Merchant country'),
							'description'	=>_('Country code with 2 characters (ex.: fr)')
					),
				),
				_('SMTP server')	=>array(
					'SMTP_HOST'	=> array(
							'label'			=>_('SMTP Host'),
							'description'	=>_('Leave blank if you use "sendmail" on localhost')
					),
					'SMTP_PORT'	=> array(
							'label'			=>_('SMTP Port'),
							'description'	=>_('Leave blank if you use "sendmail" on localhost or if you use default port 25')
					),
					'SMTP_USERNAME'	=> array(
							'label'			=>_('SMTP Username'),
							'description'	=>_('Leave blank if you use "sendmail" on localhost or if you do not use any username/password')
					),
					'SMTP_PASSWORD'	=> array(
							'label'			=>_('SMTP Password'),
							'description'	=>_('Leave blank if you use "sendmail" on localhost or if you do not use any username/password')
					),
					'MAIL_NOMORE'	=> array(
							'label'			=>_('No more emails'),
							'description'	=>_('Set 1 if you want to block any email to be sent. Usually, this is useful for debug')
					)
				),
			),
			
			'table'=>array(
				_('Access rules')=>array(
					'ADMIN_PAGE_RULES'	=> array(
						'label'			=>_('Rules for the admin pages'),
						'description'	=>_('This is the rules to define for each page of the admin space.').'<br>'.
							_('The column "Role" displays a number that corresponds to the ID of the role.').'<br>'.
							_('For example, "1" is for super-admin and "2" is for admin.').'<br><br>'.
							_('The column "Admin Pages" lists every admin pages that are only accessible for the corresponding role.').'<br>'.
							sprintf(_('The fields only show the part of the URL after %s.'), ADMIN_WEB_ROOT).'<br><br>'.
							_('Example: the admin page "system" is associated to role 1. So, only the super-admins are allowed to access this page. Etc.').'<br><br>'.
							_('If you are not sure, do not change anything, or click on the "Back to defaults" button.').'<br>'.
							_('The admin pages are contained into the project code source folder. You should take a look at this tree.')
						)
				),
			)
		);
		$this->Param = new Parameters();
		$this->loadParams();
	}
	
	function main(){
		$this->setTitle(_('Server manager'));
	}
	
	function doPostBack(){
		if (isset($_POST['reset_admin_rules'])){
			$this->Param->delete("param_key='ADMIN_PAGE_RULES'");
			$this->redirectToSelf();
		}
		if (!empty($_POST['generate_sitemap'])){
			if ($this->generateSitemap())
				$this->addUserEndMsg('SUCCESS', _('The sitemap has been successfully created and located at').' '.WEB_ROOT.'/sitemap.xml');
			else
				$this->addUserEndMsg('ERROR', _('Failed to generate the sitemap, probably due to write access upon the root folder').' '.PUB_ROOT);
			$this->redirectToSelf();
		}
		if (empty($_POST['params'])){
			return false;
		}
		foreach ($_POST['params'] as $k=>$v){
			if ($this->_view->params[$k]['value'] == $v) continue;
			if (is_array($v)){
				$v = serialize($v);
			}
			if (!$this->Param->load("param_key='$k'")){
				if ($this->Param->insert(array('param_key'=>$k, 'param_value'=>$v))){
					$this->addUserEndMsg('SUCCESS', sprintf(_('A new parameter "%s" has been successfully inserted'), $k));
				}
				else{
					$this->addUserEndMsg('ERRORS', sprintf(_('Insertion failure for new parameter "%s"'), $k));
				}
			}
			elseif ($this->Param->update("param_key='$k'", array('param_value'=>$v))){
				$this->addUserEndMsg('SUCCESS', sprintf(_('The parameter "%s" has been successfully updated'), $k));
			}
		}
		$this->redirectToSelf();
	}
	
	function loadParams(){
		$this->_view->params = array();
		foreach ($this->params as $type=>$globparams){
			foreach ($globparams as $legend=>$params){
				foreach ($params as $key=>$props){
					$this->_view->params[$type][$legend][$key] = $props;
					$value = $this->Param->selectValue("param_key='$key'", 'param_value');
					$this->_view->params[$type][$legend][$key]['value'] = $type=='table' ? unserialize($value) : $value;
					if ($type=='table' && $key=='ADMIN_PAGE_RULES' && empty($value)){
						$this->_view->params[$type][$legend][$key]['value'] = $this->rules;
					}
				}
			}
		}
	}
	
	function generateSitemap(){
		$articles = $this->getArticle()->selectDistinct('publish=1', null, "position, FIELD(view, 'home', 'default'), edit_date DESC");
		$nbArticles = count($articles);
		$hasVideos = false;
		$hasImages = false;
		
		$urlset = '';
		if ($nbArticles){
			foreach ($articles as $article){
				$Art = new Article();
				$Art->load('aid='.$article['aid']);
				$CustFields = $Art->getCustomFields();
				$urlset.= '<url>'."\n";
				if ($article['request_uri'] == 'home')
					$urlset.= '	<loc>'.WEB_ROOT.'</loc>'."\n";
				else
					$urlset.= '	<loc>'.WEB_ROOT.'/'.$article['request_uri'].'</loc>'."\n";
				$urlset.= '	<lastmod>'.date('Y-m-d', strtotime($article['edit_date'])).'</lastmod>'."\n";
				$urlset.= '	<priority>'.str_replace(',', '.', round(1/$article['position'], 2)).'</priority>'."\n";
				foreach ($CustFields as $custfield){
					if ($custfield['input_type']=='media' && !empty($custfield['field_value']['type'])
							&& !empty($custfield['field_value']['src'])
							&& $custfield['field_value']['type']=='video'){
						$hasVideos = true;
						$urlset.=
						'	<video:video>'."\n".
						'		<video:content_loc>'.$custfield['field_value']['src'].'</video:content_loc>'."\n".
						'		<video:title>'.$custfield['field_name'].'</video:title>'."\n".
						'	</video:video>'."\n";
					}
					elseif ($custfield['input_type']=='image' && stripos($custfield['field_value']['src'], WEB_ROOT)!==false){
						$hasImages = true;
						$urlset.=
						'	<image:image>'."\n".
						'		<image:loc>'.$custfield['field_value']['src'].'</image:loc>'."\n".
						'		<image:caption>'.$custfield['field_name'].'</image:caption>'."\n".
						'		<image:title>'.(!empty($custfield['field_value']['title']) ? $custfield['field_value']['title'] : $custfield['field_name']).'</image:title>'."\n".
						'	</image:image>'."\n";
					}
				}
				//Find images into the body content (not supported for videos or youtube iframes)
				preg_match_all('/<img[^>]+>/i',$article['body'], $allimgs);
				if (!empty($allimgs)){
					foreach ($allimgs as $imgs){
						if (!empty($imgs)){
							foreach ($imgs as $strimg){
								$imgprops = array();
								foreach (explode(' ', $strimg) as $prop){
									if (stripos($prop, 'src')===0)
										$imgprops['src'] = str_ireplace(array('src', '=', ' ', '"'), '', $prop);
									elseif (stripos($prop, 'title')===0)
										$imgprops['title'] = str_ireplace(array('title', '=', '"'), '', $prop);
									elseif (stripos($prop, 'alt')===0)
										$imgprops['alt'] = str_ireplace(array('alt', '=', '"'), '', $prop);
								}
								if (!empty($imgprops['src'])){
									$hasImages = true;
									$urlset.= '	<image:image>'."\n";
									$urlset.= '		<image:loc>'.$imgprops['src'].'</image:loc>'."\n";
									if (!empty($imgprops['alt']))
										$urlset.= '		<image:caption>'.$imgprops['alt'].'</image:caption>'."\n";
									if (!empty($imgprops['alt']))
										$urlset.= '		<image:title>'.$imgprops['title'].'</image:title>'."\n";
									$urlset.= '	</image:image>'."\n";
								}
							}
						}
					}
				}
				$urlset.= '</url>'."\n";
			}
		}
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$xml.= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
		if ($hasVideos)
			$xml.= 'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" ';
		if ($hasImages)
			$xml.= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ';
		$xml.= '>'."\n";
		$xml.= $urlset."\n";
		$xml.= '</urlset>';
		return file_put_contents(PUB_ROOT.'/sitemap.xml', $xml);
	}
}