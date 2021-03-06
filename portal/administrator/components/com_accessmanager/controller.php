<?php
/**
* @package Access-Manager (com_accessmanager)
* @version 2.2.1
* @copyright Copyright (C) 2012 - 2014 Carsten Engel. All rights reserved.
* @license GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html 
* @author http://www.pages-and-items.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class accessmanagerController extends JControllerLegacy{

	public $am_config;	
	public $view;		
	public $accesslevels;	
	public $plugin_system_installed = false;
	public $plugin_system_enabled = false;
	public $am_demo_seconds_left;	
	public $version = '2.2.1';
	public $am_version_type = 'free';	//free trial or pro	
	public $is_super_user = false;
	public $backend_usergroups = array();
	public $helper;

	function display($cachable = false, $urlparams = false){		
		
		$app = JFactory::getApplication();	
		$ds = DIRECTORY_SEPARATOR;	
		
		echo '<div class="am';
		$version = new JVersion;
		if($version->RELEASE >= '3.0'){
			echo ' joomla3';
		}
		echo '">';			
				
		// Set a default view if none exists			
		if(!$this->view){							
			JRequest::setVar('view', 'panel');
			$this->view = 'panel';						
		}		
		
		if(JRequest::getVar('layout', '')!='csv'){
			$version = new JVersion;
			if($version->RELEASE >= '3.0'){
				//bootstrap selects
				JHtml::_('behavior.multiselect');
				JHtml::_('formbehavior.chosen', 'select');
			}else{	
				//make sure mootools is loaded					
				JHTML::_('behavior.mootools');
				JHtml::_('behavior.tooltip');
				//load the submenu
				$this->helper->addSubmenu(JRequest::getWord('view', 'accessmanager'), $this->am_config);
			}
			
			//display messages
			$this->display_header();
		}
		
		parent::display();	
		echo '</div>';				
	}
	
	
	function __construct(){	
	
		$database = JFactory::getDBO();
		$app = JFactory::getApplication();
		$ds = DIRECTORY_SEPARATOR;
		
		$this->am_config = $this->get_config();				
		$this->view = JRequest::getVar('view');	
		
		//get the helper	
		require_once JPATH_ADMINISTRATOR.$ds.'components'.$ds.'com_accessmanager'.$ds.'helpers'.$ds.'accessmanager.php';
		$this->helper = new accessmanagerHelper;					
		
		//check if system plugins is installed and published	
		if(file_exists(JPATH_ROOT.$ds.'plugins'.$ds.'system'.$ds.'accessmanager'.$ds.'accessmanager.php')){
			$this->plugin_system_installed = true;	
			//check if plugin is published	
			$database->setQuery("SELECT enabled FROM #__extensions WHERE type='plugin' AND element='accessmanager' AND folder='system' LIMIT 1 ");
			$rows = $database->loadObjectList();
			foreach($rows as $row){					
				if($row->enabled==1){
					$this->plugin_system_enabled = true;
				}
			}		
		}		
		
		//check if user plugins is installed and published	
		if(file_exists(JPATH_ROOT.$ds.'plugins'.$ds.'user'.$ds.'accessmanager'.$ds.'accessmanager.php')){
			$this->bot_installed_user = true;	
			//check if plugin is published			
			$database->setQuery("SELECT enabled FROM #__extensions WHERE type='plugin' AND element='accessmanager' AND folder='user' LIMIT 1 ");
			$rows = $database->loadObjectList();
			foreach($rows as $row){					
				if($row->enabled==1){
					$this->bot_published_user = true;
				}
			}		
		}
				
		//check if super user	
		$user = JFactory::getUser();			
		$user_id = $user->id;
		$database->setQuery("SELECT group_id FROM #__user_usergroup_map WHERE user_id='$user_id' AND group_id='8' LIMIT 1");
		$rows = $database->loadObjectList();		
		foreach($rows as $row){
			$this->is_super_user = true;
		}	
		
		parent::__construct();		
	}	
	
	function get_config(){	
			
		$database = JFactory::getDBO();			
		
		$database->setQuery("SELECT config "
		."FROM #__accessmanager_config "
		."WHERE id='am' "
		."LIMIT 1"
		);		
		$raw = $database->loadResult();	
		
		$registry = new JRegistry;
		$registry->loadString($raw);
		$config = $registry->toArray();
		
		//reformat redirect urls		
		$config['no_item_access_full_url'] = str_replace('[equal]','=',$config['no_item_access_full_url']);			
		$config['no_component_access_url'] = str_replace('[equal]','=',$config['no_component_access_url']);
		$config['no_menu_access_url'] = str_replace('[equal]','=',$config['no_menu_access_url']);
		$config['no_contact_access_url'] = str_replace('[equal]','=',$config['no_contact_access_url']);		
			
		return $config;			
	}			
	
	function display_header(){	
	
		$database = JFactory::getDBO();
		$ds = DIRECTORY_SEPARATOR;
		
		if($this->view=='users' && JRequest::getVar('layout', '')=='csv'){
			return true;
		}	
		
		if($this->am_version_type=='trial'){
			$this->check_demo_time_left();	
		}	
		
		echo '<script src="components/com_accessmanager/javascript/javascript.js" language="JavaScript" type="text/javascript"></script>'."\n";		
		echo '<link href="components/com_accessmanager/css/accessmanager9.css" rel="stylesheet" type="text/css" />'."\n";
				
		echo '<div id="am_header_messages">';
		//message if disabled
		if(!$this->am_config['am_enabled']){
			echo '<div style="text-align: left;" class="am_red">'.JText::_('COM_ACCESSMANAGER_DISABLED_MESSAGE').' <a href="index.php?option=com_accessmanager&view=configuration">'.JText::_('COM_ACCESSMANAGER_CONFIG').'</a>.<br/><br/></div>';
		}	
		
		//message if bot is not installed	
		if(!$this->plugin_system_installed){				
			echo '<div style="text-align: left;" class="am_red">'.JText::_('COM_ACCESSMANAGER_BOTNOTINSTALLED').' (system).<br/><br/></div>';
		}
		
		//message if bot is not published	
		if(!$this->plugin_system_enabled){				
			echo '<div style="text-align: left;"><span class="am_red">'.JText::_('COM_ACCESSMANAGER_BOTNOTPUBLISHED').' (system)';			
			echo ' <a href="index.php?option=com_accessmanager&task=enable_plugin&plugin=accessmanager&folder=system&from='.$this->view.'">'.JText::_('COM_ACCESSMANAGER_ENABLE_PLUGIN').'</a>';			
			echo '.</span><br/><br/></div>';
		}			
		
		//message if advanced module manager is installed and loaded before AM system plugin
		if(file_exists(JPATH_PLUGINS.$ds.'system'.$ds.'advancedmodules'.$ds.'advancedmodules.php')){
			
			//check if enabled and which order
			$database->setQuery("SELECT enabled, ordering "
			." FROM #__extensions "
			." WHERE element='advancedmodules' AND folder='system' "
			." LIMIT 1 "
			);
			$rows = $database->loadObjectList();
			$advanced_module_manager_published = 0;
			$advanced_module_manager_order = 0;
			foreach($rows as $row){					
				$advanced_module_manager_published = $row->enabled;
				$advanced_module_manager_order = $row->ordering;
			}
			
			if($advanced_module_manager_published){			
				
				//check which order the AM system plugin has
				$database->setQuery("SELECT ordering "
				." FROM #__extensions "
				." WHERE element='accessmanager' AND folder='system' "
				." LIMIT 1 "
				);
				$rows = $database->loadObjectList();
				$am_order = 0;
				foreach($rows as $row){					
					$am_order = $row->ordering;
				}
				
				//if advanced_module_manager is enabled AND AM is not loaded first in order, show message	
				if($am_order >= $advanced_module_manager_order){	
					echo '<div style="text-align: left;"><span class="am_red">'.JText::_('COM_ACCESSMANAGER_ADVANCEDMODULEMANAGER_D').'    Advanced-Module-Manager (by noNumber) '.JText::_('COM_ACCESSMANAGER_ADVANCEDMODULEMANAGER_B').' Access-Manager system '.JText::_('COM_ACCESSMANAGER_ADVANCEDMODULEMANAGER_C').'.</span> <a href="index.php?option=com_plugins&filter_type=system">'.JText::_('COM_ACCESSMANAGER_PLUGIN_MANAGER').'</a>.<br/><br/></div>';
				}
			}
		}	
		
		//message if MetaMod is installed and loaded before AM system plugin
		if(file_exists(JPATH_PLUGINS.$ds.'system'.$ds.'metamod'.$ds.'metamod.php')){
			
			//check if enabled and which order
			$database->setQuery("SELECT enabled, ordering "
			." FROM #__extensions "
			." WHERE element='metamod' AND folder='system' "
			." LIMIT 1 "
			);
			$rows = $database->loadObjectList();
			$metamod_published = 0;
			$metamod_order = 0;
			foreach($rows as $row){					
				$metamod_published = $row->enabled;
				$metamod_order = $row->ordering;
			}
			
			if($metamod_published){		
				
				//check which order the AM system plugin has
				$database->setQuery("SELECT ordering "
				." FROM #__extensions "
				." WHERE element='accessmanager' AND folder='system' "
				." LIMIT 1 "
				);
				$rows = $database->loadObjectList();
				$am_order = 0;
				foreach($rows as $row){					
					$am_order = $row->ordering;
				}
				
				//if advanced_module_manager is enabled AND AM is not loaded first in order, show message	
				if($am_order >= $metamod_order){	
					echo '<div style="text-align: left;"><span class="am_red">'.JText::_('COM_ACCESSMANAGER_ADVANCEDMODULEMANAGER_D').' MetaMod '.JText::_('COM_ACCESSMANAGER_ADVANCEDMODULEMANAGER_B').' Access-Manager system '.JText::_('COM_ACCESSMANAGER_ADVANCEDMODULEMANAGER_C').'.</span> <a href="index.php?option=com_plugins&filter_type=system">'.JText::_('COM_ACCESSMANAGER_PLUGIN_MANAGER').'</a>.<br/><br/></div>';
				}		
			}
		}			
			
		//message if emiIE6warning is installed
		if(file_exists(JPATH_ROOT.$ds.'plugins'.$ds.'system'.$ds.'emiIE6warning'.$ds.'emiIE6warning.php')){
			if($this->check_emi_ie6_plugin()){			
				echo '<div style="text-align: left;" class="am_red">'.JText::_('COM_ACCESSMANAGER_THE_PLUGIN').' emiIE6warning '.JText::_('COM_ACCESSMANAGER_FROM').' emi.marsteam.net '.JText::_('COM_ACCESSMANAGER_HAS_CONFLICT_WITH').' Access-Manager system plugin. <a href="http://www.pages-and-items.com/contribute/other-stuff/fix-for-plugin-emiie6warning" target="_blank">'.JText::_('COM_ACCESSMANAGER_READ_MORE').'</a><br/><br/></div>';	
			}			
		}
		
		//message if ztools is installed and enabled
		if(file_exists(JPATH_ROOT.$ds.'plugins'.$ds.'system'.$ds.'plg_ztools'.$ds.'plg_ztools'.$ds.'plg_ztools.php')){
			if($this->check_ztools_plugin()){			
				echo '<div style="text-align: left;" class="am_red">'.JText::_('COM_ACCESSMANAGER_THE_PLUGIN').' ZT Tools '.JText::_('COM_ACCESSMANAGER_FROM').' ZooTemplate '.JText::_('COM_ACCESSMANAGER_HAS_CONFLICT_WITH').'  Access-Manager system plugin. '.JText::_('COM_ACCESSMANAGER_ZTOOLS_WARNING').'. '.JText::_('COM_ACCESSMANAGER_NO_CACHE').'.  <a href="index.php?option=com_accessmanager&task=disable_ztools">'.JText::_('COM_ACCESSMANAGER_DISABLE_PLUGIN').'</a>.<br/><br/></div>';	
			}			
		}		
		
		//message if ubar is installed and contains bad code
		if(file_exists(JPATH_ROOT.$ds.'plugins'.$ds.'system'.$ds.'ubar'.$ds.'ubar.php')){
			if($this->check_ubar_plugin()){			
				echo '<div style="text-align: left;" class="am_red">'.JText::_('COM_ACCESSMANAGER_THE_PLUGIN').' System - UserToolbar (uBar) '.JText::_('COM_ACCESSMANAGER_HAS_CONFLICT_WITH').' Access-Manager system plugin. <a href="http://www.pages-and-items.com/contribute/other-stuff/fix-for-plugin-ubar" target="_blank">'.JText::_('COM_ACCESSMANAGER_READ_MORE').'</a>.<br/><br/></div>';	
			}			
		}
		
		//message if FUA system plugin is installed and enabled
		if(file_exists(JPATH_ROOT.$ds.'plugins'.$ds.'system'.$ds.'frontenduseraccess'.$ds.'frontenduseraccess.php')){
			if($this->check_fua_plugin()){			
				echo '<div style="text-align: left;" class="am_red">'.JText::_('COM_ACCESSMANAGER_THE_PLUGIN').' \'System - Frontend User Access\' '.JText::_('COM_ACCESSMANAGER_HAS_CONFLICT_WITH').' Access-Manager system plugin. <a href="index.php?option=com_accessmanager&task=disable_fua&returnview='.$this->view.'">'.JText::_('COM_ACCESSMANAGER_DISABLE_PLUGIN').'</a>.<br/><br/></div>';	
			}			
		}
		
		//message if JAT3 system plugin is installed and enabled and the cache is still on
		if(file_exists(JPATH_ROOT.$ds.'plugins'.$ds.'system'.$ds.'jat3'.$ds.'jat3.php')){			
			$templates_with_enabled_cache = $this->check_jat3_plugin_and_cache();
			if(count($templates_with_enabled_cache)){
				$template_string = implode('<br />', $templates_with_enabled_cache);			
				echo '<div style="text-align: left;" class="am_red">jat3 template cache should be disabled in template(s):<br />'.$template_string.'<br/><br/></div>';	
			}			
		}
		
		//message if T3 system plugin is installed and enabled and the cache is still on
		if(file_exists(JPATH_ROOT.$ds.'plugins'.$ds.'system'.$ds.'t3'.$ds.'t3.php')){			
			$templates_with_enabled_cache = $this->check_t3_plugin_and_cache();
			if(count($templates_with_enabled_cache)){
				$template_string = implode('<br />', $templates_with_enabled_cache);			
				echo '<div style="text-align: left;" class="am_red">t3 template cache should be disabled in template(s):<br />'.$template_string.'<br/><br/></div>';	
			}			
		}
		
		//message if zen17 does not check articles helper class before loading it
		$file = JPATH_ROOT.'/modules/mod_zentools/includes/zenj17contenthelper.php';
		if(file_exists($file)){				
			if($this->check_zen17($file)){							
				echo '<div style="text-align: left;" class="am_red">Your template is using the zen17 framework which conflicts with Access-Manager. <a href="http://www.pages-and-items.com/contribute/other-stuff/fix-for-zen17-framework" target="_blank">This is easy to fix</a></div>';	
			}			
		}
		
		//message if ACL manager is installed but disabled and system plugin is still enabled, gives error 500		
		if(file_exists(JPATH_ROOT.'/administrator/components/com_aclmanager/aclmanager.php')){				
			if($this->check_aclmanager()){							
				echo '<div style="text-align: left;" class="am_red">Your template is using the zen17 framework which conflicts with Access-Manager. <a href="http://www.pages-and-items.com/contribute/other-stuff/fix-for-zen17-framework" target="_blank">This is easy to fix</a></div>';	
			}			
		}
		
		//message if Aridatatables is installed and not altered to work with FUA
		$file = JPATH_ROOT.$ds.'plugins'.$ds.'content'.$ds.'aridatatables'.$ds.'kernel'.$ds.'Module'.$ds.'class.ModuleHelper.php';
		if(file_exists($file)){		
			if($this->check_aridatatables($file)){						
				echo '<div style="text-align: left;" class="am_red">content plugin aridatatables is installed and contains code which will cause errors <a href="http://www.pages-and-items.com/contribute/other-stuff/fix-for-aridatatables" target="_blank">'.JText::_('COM_ACCESSMANAGER_READ_MORE').'</a>.<br/><br/></div>';	
			}			
		}
		$file = JPATH_ROOT.$ds.'modules'.$ds.'mod_aridatatables'.$ds.'includes'.$ds.'kernel'.$ds.'Module'.$ds.'class.ModuleHelper.php';
		if(file_exists($file)){		
			if($this->check_aridatatables($file)){						
				echo '<div style="text-align: left;" class="am_red">module aridatatables is installed and contains code which will cause errors <a href="http://www.pages-and-items.com/contribute/other-stuff/fix-for-aridatatables" target="_blank">'.JText::_('COM_ACCESSMANAGER_READ_MORE').'</a>.<br/><br/></div>';	
			}			
		}
		
		//message to import access settings
		if($this->am_config['display_import_message'] && $this->view!='tools'){
			echo '<div style="text-align: left;">';
			echo JText::_('COM_ACCESSMANAGER_IMPORT_ACCESS_MESSAGE');
			echo ' <a href="index.php?option=com_accessmanager&view=tools">'.JText::_('COM_ACCESSMANAGER_GO_TO_TOOLSPAGE').'</a>.';
			echo ' <a href="index.php?option=com_accessmanager&task=do_not_show_import_message&returnview='.$this->view.'">'.JText::_('COM_ACCESSMANAGER_DONT_SHOW_AGAIN').' <img src="components/com_accessmanager/images/close.png" /></a>';
			echo '<br/><br/></div>';	
		}	
		
		//message if suhosin is installed and has little max fields
		$max_suhosin = ini_get('suhosin.request.max_vars');
		if($max_suhosin && $max_suhosin<1000){
			echo '<div style="text-align: left;" class="am_red">Suhosin PHP plugin is installed and maximum number of field elements is limited to '.$max_suhosin.' <a href="http://www.pages-and-items.com/extensions/access-manager/faqs?faqitem=issues_limit_posts" target="_blank">'.JText::_('COM_ACCESSMANAGER_READ_MORE').'</a>.<br/><br/></div>';	
		}	
		
		echo '</div>';		
	}					
		
	function config_save(){	
		JRequest::checkToken() or jexit('Invalid Token');		
			
		$this->am_config['am_enabled'] = JRequest::getVar('am_enabled', '0', 'post', 'int');
		$this->am_config['based_on'] = JRequest::getVar('based_on', '', 'post');		
		$this->am_config['level_sort'] = JRequest::getVar('level_sort', 'ordering', 'post');
		$this->am_config['height_multiselect'] = JRequest::getVar('height_multiselect', '', 'post');
		$this->am_config['multiselector_bootstrap'] = JRequest::getVar('multiselector_bootstrap', '0', 'post');
		$this->am_config['version_checker'] = JRequest::getVar('version_checker', '', 'post');			
		$this->rebuild_and_save_config();
		
		//redirect			
		if(JRequest::getVar('sub_task', '')=='apply'){
			$url = 'index.php?option=com_accessmanager&view=configuration';
		}else{
			$url = 'index.php?option=com_accessmanager&view=panel';
		}	
		$this->setRedirect($url, JText::_('COM_ACCESSMANAGER_CONFIGSAVED'));
	}	
	
	function rebuild_and_save_config(){		
		$database = JFactory::getDBO();		
		$registry = new JRegistry;
		$registry->loadArray($this->am_config);
		$config = $registry->toString();
		$database->setQuery( "UPDATE #__accessmanager_config SET config='$config' WHERE id='am' ");
		$database->query();
	}	

	function display_footer(){	
		
		echo '<div class="clearboth"></div>';
		echo '<div class="smallgrey" id="ua_footer">';
		echo '<table>';
		echo '<tr>';
		echo '<td class="text_right">';
		echo '<a href="http://www.pages-and-items.com/extensions/access-manager" target="_blank">Access-Manager</a>';
		echo '</td>';
		echo '<td class="five_pix">';
		echo '&copy;';
		echo '</td>';
		echo '<td>';
		echo '2012 - 2013 Carsten Engel';		
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="text_right">';
		echo $this->am_strtolower(JText::_('JVERSION'));
		echo '</td>';
		echo '<td class="five_pix">';
		echo '=';
		echo '</td>';
		echo '<td>';
		echo $this->version.' ('.$this->am_version_type.' '.$this->am_strtolower(JText::_('JVERSION')).')';
		if($this->am_version_type!='trial'){
			echo ' <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="blank">GNU/GPL License</a>';
		}
		echo '</td>';
		echo '</tr>';
		//version checker
		if($this->am_config['version_checker']){
			echo '<tr>';
			echo '<td class="text_right">';
			echo $this->am_strtolower(JText::_('COM_ACCESSMANAGER_LATEST_VERSION'));
			echo '</td>';
			echo '<td class="five_pix">';
			echo '=';
			echo '</td>';
			echo '<td>';
			$app = JFactory::getApplication();
			$latest_version_message = $app->getUserState( "com_accessmanager.latest_version_message", '');
			if($latest_version_message==''){
				$latest_version_message = JText::_('COM_ACCESSMANAGER_VERSION_CHECKER_NOT_AVAILABLE');
				$url = 'http://www.pages-and-items.com/latest_version.php?extension=accessmanager';		
				$file_object = @fopen($url, "r");		
				if($file_object == TRUE){
					$version = fread($file_object, 1000);
					$latest_version_message = $version;
					if($this->version!=$version){
						$latest_version_message .= ' <span class="am_red">'.JText::_('COM_ACCESSMANAGER_NEWER_VERSION').'</span>';
						if($this->am_version_type=='pro'){
							$download_url = 'http://www.pages-and-items.com/my-extensions';
						}elseif($this->am_version_type=='trial'){
							$download_url = 'http://engelweb.nl/trialversions/';
						}else{
							$download_url = 'http://www.pages-and-items.com/extensions/access-manager';
						}
						$latest_version_message .= ' <a href="'.$download_url.'" target="_blank">'.JText::_('COM_ACCESSMANAGER_DOWNLOAD').'</a>';
						if($this->am_version_type!='pro'){
							$latest_version_message .= ' <a href="index.php?option=com_installer&view=update">'.$this->am_strtolower(JText::_('JLIB_INSTALLER_UPDATE')).'</a>';
						}
					}else{
						$latest_version_message .= ' <span class="am_green">'.JText::_('COM_ACCESSMANAGER_IS_LATEST_VERSION').'</span>';
					}
					fclose($file_object);
				}				
				$app->setUserState( "com_accessmanager.latest_version_message", $latest_version_message );
			}
			echo $latest_version_message;
			echo '</td>';
			echo '</tr>';
		}	
		echo '<tr>';
		echo '<td class="text_right" colspan="2">';
		echo $this->am_strtolower(JText::_('COM_ACCESSMANAGER_REVIEW_B')); 
		echo '</td>';
		echo '<td>';
		if($this->am_version_type=='pro'){
			$url_jed = '23466';
		}else{
			$url_jed = '19766';
		}
		echo '<a href="http://extensions.joomla.org/extensions/access-a-security/site-access/backend-a-full-access-control/'.$url_jed.'" target="_blank">';
		echo 'Joomla! Extensions Directory</a>';
		echo '</td>';
		echo '</tr>';	
		echo '</table>';		
		echo '</div>';			
	}
	
	function access_articles_save(){		
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('item_access_hidden', null, 'post', 'array');		
		$this->save_access_rights($rights, 'article');
		
		$this->am_config['article_active'] = JRequest::getVar('article_active', '', 'post');
		$this->am_config['article_reverse_access'] = JRequest::getVar('article_reverse_access', '', 'post');
		$this->am_config['article_multigroup_access_requirement'] = JRequest::getVar('article_multigroup_access_requirement', 'one_group', 'post');
		$this->am_config['article_superadmins'] = JRequest::getVar('article_superadmins', '', 'post');
		$this->am_config['items_message_type'] = JRequest::getVar('items_message_type', 'alert', 'post');
		$no_item_access_full_url = addslashes(JRequest::getVar('no_item_access_full_url', '', 'post'));			
		$this->am_config['no_item_access_full_url'] = str_replace('=','[equal]',$no_item_access_full_url);	
		$this->am_config['truncate_article_title'] = JRequest::getVar('truncate_article_title', '', 'post');
		$this->am_config['display_category_column_article'] = JRequest::getVar('display_category_column_article', '', 'post');		
		$this->am_config['content_access_together'] = JRequest::getVar('content_access_together', 'every_group', 'post');		
		$this->am_config['article_default_access'] = JRequest::getVar('article_default_access', array(), 'post', 'array');
		$this->rebuild_and_save_config();
		
		$this->helper->clear_article_cache();
	
		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_ITEM_ACCESS_SAVED'));
		}else{				
			$this->setRedirect('index.php?option=com_accessmanager&view=articles', JText::_('COM_ACCESSMANAGER_ITEM_ACCESS_SAVED'));
		}
	}	
	
	function access_categories_save(){		
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('category_access_hidden', null, 'post', 'array');		
		$this->save_access_rights($rights, 'category');	
		
		$this->am_config['category_active'] = JRequest::getVar('category_active', '', 'post');
		$this->am_config['category_reverse_access'] = JRequest::getVar('category_reverse_access', '', 'post');
		$this->am_config['category_multigroup_access_requirement'] = JRequest::getVar('category_multigroup_access_requirement', 'one_group', 'post');	
		$this->am_config['category_superadmins'] = JRequest::getVar('category_superadmins', '', 'post');	
		$this->am_config['category_default_access'] = JRequest::getVar('category_default_access', array(), 'post', 'array');
		$this->rebuild_and_save_config();
		
		$this->helper->clear_article_cache();
		
		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_CATEGORY_ACCESS_SAVED'));
		}else{									
			$this->setRedirect("index.php?option=com_accessmanager&view=categories", JText::_('COM_ACCESSMANAGER_CATEGORY_ACCESS_SAVED'));
		}
	}	
	
	function modules_save(){
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('module_access_hidden', null, 'post', 'array');
		$this->save_access_rights($rights, 'module');
		
		$this->am_config['module_active'] = JRequest::getVar('module_active', '', 'post');
		$this->am_config['module_reverse_access'] = JRequest::getVar('module_reverse_access', '', 'post');		
		$this->am_config['module_multigroup_access_requirement'] = JRequest::getVar('module_multigroup_access_requirement', 'one_group', 'post');	
		$this->am_config['module_superadmins'] = JRequest::getVar('module_superadmins', '', 'post');
		$this->am_config['module_default_access'] = JRequest::getVar('module_default_access', array(), 'post', 'array');
		$this->rebuild_and_save_config();
		
		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_MODULE_ACCESS_SAVED'));
		}else{				
			$this->setRedirect("index.php?option=com_accessmanager&view=modules", JText::_('COM_ACCESSMANAGER_MODULE_ACCESS_SAVED'));
		}
	}	
	
	function components_save(){
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('components_access_hidden', null, 'post', 'array');		
		$this->save_access_rights($rights, 'component');
		
		$this->am_config['component_active'] = JRequest::getVar('component_active', '', 'post');
		$this->am_config['component_reverse_access'] = JRequest::getVar('component_reverse_access', '', 'post');
		$this->am_config['component_multigroup_access_requirement'] = JRequest::getVar('component_multigroup_access_requirement', 'one_group', 'post');
		$this->am_config['component_superadmins'] = JRequest::getVar('component_superadmins', '', 'post');
		$this->am_config['components_message_type'] = JRequest::getVar('components_message_type', 'alert', 'post');				
		$this->am_config['no_component_access_url'] = addslashes(str_replace('=','[equal]',JRequest::getVar('no_component_access_url', '', 'post')));
		$this->rebuild_and_save_config();

		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_COMPONENT_ACCESS_SAVED'));
		}else{		
			$this->setRedirect("index.php?option=com_accessmanager&view=components", JText::_('COM_ACCESSMANAGER_COMPONENT_ACCESS_SAVED'));
		}
	}
	
	function menuaccess_save(){	
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('menu_access_hidden', null, 'post', 'array');			
		$this->save_access_rights($rights, 'menuitem');	

		$this->am_config['menuitem_active'] = JRequest::getVar('menuitem_active', '', 'post');	
		$this->am_config['menumodule_override'] = JRequest::getVar('menumodule_override', '', 'post');		
		$this->am_config['menuitem_reverse_access'] = JRequest::getVar('menuitem_reverse_access', '', 'post');
		$this->am_config['menuitem_multigroup_access_requirement'] = JRequest::getVar('menuitem_multigroup_access_requirement', 'one_group', 'post');
		$this->am_config['menuitem_superadmins'] = JRequest::getVar('menuitem_superadmins', '', 'post');	
		$this->am_config['menuaccess_message_type'] = JRequest::getVar('menuaccess_message_type', 'text', 'post');
		$this->am_config['no_menu_access_url'] = addslashes(str_replace('=','[equal]',JRequest::getVar('no_menu_access_url', '', 'post')));		
		$this->am_config['menuitem_default_access'] = JRequest::getVar('menuitem_default_access', array(), 'post', 'array');			
		$this->rebuild_and_save_config();
		
		$this->helper->clear_cache('com_modules');
		$this->helper->clear_cache('mod_menu');
		
		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_MENU_ACCESS_SAVED'));
		}else{				
			$this->setRedirect("index.php?option=com_accessmanager&view=menuaccess", JText::_('COM_ACCESSMANAGER_MENU_ACCESS_SAVED'));
		}
	}
	
	function access_parts_save(){
		JRequest::checkToken() or jexit('Invalid Token');
		$rights = JRequest::getVar('part_access_hidden', null, 'post', 'array');			
		$this->save_access_rights($rights, 'part');	
		
		$this->am_config['part_active'] = JRequest::getVar('part_active', '', 'post');
		$this->am_config['part_reverse_access'] = JRequest::getVar('part_reverse_access', '', 'post');
		$this->am_config['part_multigroup_access_requirement'] = JRequest::getVar('part_multigroup_access_requirement', 'one_group', 'post');
		$this->am_config['part_superadmins'] = JRequest::getVar('part_superadmins', '', 'post');
		$this->am_config['parts_not_active'] = JRequest::getVar('parts_not_active', '', 'post');
		$this->rebuild_and_save_config();
		
		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_PART_ACCESS_SAVED'));
		}else{			
			$this->setRedirect("index.php?option=com_accessmanager&view=parts", JText::_('COM_ACCESSMANAGER_PART_ACCESS_SAVED'));
		}
	}
	
	function save_access_rights($rights, $type){
	
		$database = JFactory::getDBO();
		
		$based_on = $this->am_config['based_on'];		
		
		$rights_to_delete = array();
		
		//write item access		
		for($n = 0; $n < count($rights); $n++){
			$value = $rights[$n];
			$value_array = explode('__',$value);
			
			$item = $value_array[0];
			$grouplevel = $value_array[1];			
			$selected = $value_array[3];
			
			//check if right is set yet
			$database->setQuery("SELECT id FROM #__accessmanager_rights WHERE item='$item' AND `$based_on`='$grouplevel' AND type='$type' ");
			$rows = $database->loadObjectList();
			$right_id = 0;			
			foreach($rows as $row){	
				$right_id = $row->id;	
			}
			
			//if right is now selected, but was not in table, do insert
			if($selected && !$right_id){
				$database->setQuery( "INSERT INTO #__accessmanager_rights SET item='$item', `$based_on`='$grouplevel', type='$type' ");
				$database->query();
			}
			
			//if right is not selected, but was in table, take it out
			if(!$selected && $right_id){				
				$rights_to_delete[] = $right_id;
			}
		}
		
		if(count($rights_to_delete)){
			$rights_to_delete = implode(',', $rights_to_delete);
			$database->setQuery("DELETE FROM #__accessmanager_rights WHERE id IN ($rights_to_delete) ");
			$database->query();
		}			
	}	
	
	function modulesbackend_save(){
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('access_item', null, 'post', 'array');	
		$this->save_access_rights_backend($rights, 'modulebackend');
		
		$this->am_config['modulebackend_active'] = JRequest::getVar('modulebackend_active', '', 'post');
		$this->am_config['modulebackend_default'] = JRequest::getVar('modulebackend_default', '', 'post');
		$this->am_config['modulebackend_multigroup_access_requirement'] = JRequest::getVar('modulebackend_multigroup_access_requirement', 'one_group', 'post');
		$this->am_config['modulebackend_author_access'] = JRequest::getVar('modulebackend_author_access', '', 'post');
		$this->rebuild_and_save_config();
		
		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_MODULE_ACCESS_SAVED'));
		}else{				
			$this->setRedirect("index.php?option=com_accessmanager&view=modulesbackend", JText::_('COM_ACCESSMANAGER_MODULE_ACCESS_SAVED'));
		}
	}
	
	function componentsbackend_save(){
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('access_item', null, 'post', 'array');		
		$this->save_access_rights_backend($rights, 'componentbackend');		
		
		$this->am_config['componentbackend_active'] = JRequest::getVar('componentbackend_active', '', 'post');
		$this->am_config['componentbackend_default'] = JRequest::getVar('componentbackend_default', '', 'post');
		$this->am_config['componentbackend_multigroup_access_requirement'] = JRequest::getVar('componentbackend_multigroup_access_requirement', '', 'post');
		$this->rebuild_and_save_config();		

		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_COMPONENT_ACCESS_SAVED'));
		}else{		
			$this->setRedirect("index.php?option=com_accessmanager&view=componentsbackend", JText::_('COM_ACCESSMANAGER_COMPONENT_ACCESS_SAVED'));
		}
	}
	
	function menuitemsbackend_save(){
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('access_item', null, 'post', 'array');		
		$this->save_access_rights_backend($rights, 'menuitembackend');		
		
		$this->am_config['menuitembackend_active'] = JRequest::getVar('menuitembackend_active', '', 'post');
		$this->am_config['menuitembackend_default'] = JRequest::getVar('menuitembackend_default', '', 'post');
		$this->am_config['menuitembackend_multigroup_access_requirement'] = JRequest::getVar('menuitembackend_multigroup_access_requirement', '', 'post');
		$this->am_config['menuitembackend_author_access'] = JRequest::getVar('menuitembackend_author_access', '', 'post');
		$this->rebuild_and_save_config();		

		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_MENUITEM_ACCESS_SAVED'));
		}else{		
			$this->setRedirect("index.php?option=com_accessmanager&view=menuitemsbackend", JText::_('COM_ACCESSMANAGER_MENUITEM_ACCESS_SAVED'));
		}
	}
	
	function pluginsbackend_save(){
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('access_item', null, 'post', 'array');		
		$this->save_access_rights_backend($rights, 'pluginbackend');		
		
		$this->am_config['pluginbackend_active'] = JRequest::getVar('pluginbackend_active', '', 'post');
		$this->am_config['pluginbackend_default'] = JRequest::getVar('pluginbackend_default', '', 'post');
		$this->am_config['pluginbackend_multigroup_access_requirement'] = JRequest::getVar('pluginbackend_multigroup_access_requirement', '', 'post');		
		$this->rebuild_and_save_config();		

		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_PLUGIN_ACCESS_SAVED'));
		}else{		
			$this->setRedirect("index.php?option=com_accessmanager&view=pluginsbackend", JText::_('COM_ACCESSMANAGER_PLUGIN_ACCESS_SAVED'));
		}
	}
		
	function save_access_rights_backend($rights, $type){
	
		$database = JFactory::getDBO();			
		
		$rights_to_delete = array();
		
		//write item access		
		for($n = 0; $n < count($rights); $n++){			
			$row = each($rights);			
			$key = $row['key'];	
			$key = str_replace("'", '', $key);	
			$key = str_replace("\\", '', $key);		
			$key_array = explode('__',$key);			
			$item = $key_array[0];
			$group = $key_array[1];			
			$access = $row['value'];			
			//check if right is set yet
			$database->setQuery("SELECT id FROM #__accessmanager_rights WHERE item='$item' AND `group`='$group' AND type='$type' ");			
			$rows_rights = $database->loadObjectList();
			$right_id = 0;			
			foreach($rows_rights as $row_right){	
				$right_id = $row_right->id;	
			}
			
			//if right is not in table AND new access is not 'inherit' do insert
			if(!$right_id && $access!=''){
				$database->setQuery( "INSERT INTO #__accessmanager_rights SET item='$item', `group`='$group', type='$type', access='$access' ");
				$database->query();
			}
			
			//if right is in table, update it
			if($right_id){
				//if new access is 'inherit' take it out				
				if($access==''){
					$rights_to_delete[] = $right_id;
				}else{
					//update
					$database->setQuery( "UPDATE #__accessmanager_rights SET access='$access' WHERE id='$right_id' ");
					$database->query();
				}
			}
		}		
		if(count($rights_to_delete)){
			$rights_to_delete = implode(',', $rights_to_delete);
			$database->setQuery("DELETE FROM #__accessmanager_rights WHERE id IN ($rights_to_delete) ");
			$database->query();
		}	
	}	
	
	function loop_accesslevels($accesslevels){
		foreach($accesslevels as $accesslevel){				
			$title = stripslashes($accesslevel->title);			
			$title = str_replace('"','&quot;',$title);
			echo '<th style="text-align:center; width: 10%; vertical-align: top;">';	
			echo $title;
			echo '</th>';
		}
	}	
	
	function reverse_access_warning($which){
		echo '<p class="reverse_warning">';
		echo '<input type="checkbox" name="am_legend_box" id="am_legend_box" value="" checked="checked" onclick="this.checked=true" onfocus="if(this.blur)this.blur();" class="checkbox" /> = ';
		if($this->am_config[$which]){				
			echo JText::_('COM_ACCESSMANAGER_USERGROUP_HAS_NO_ACCESS');
			echo '<img src="components/com_accessmanager/images/notice.png" class="warning_img" alt="be carefull" />'.JText::_('COM_ACCESSMANAGER_REVERSE_ACCESS_WARNING');
		}else{
			echo JText::_('COM_ACCESSMANAGER_USERGROUP_HAS_ACCESS');
		}
		echo '</p>';
	}
	
	function not_in_free_version(){
		if($this->am_version_type=='free'){
			echo '<p class="am_red">'.JText::_('COM_ACCESSMANAGER_NOT_IN_FREE_VERSION').'.</p>';
		}
	}
	
	function accesslevel_selector($backend_only = 0, $include_superadmin = 0, $only_groups = 0){	
		
		if (isset($_COOKIE["am_selected_grouplevels"])) {
			$am_selected_grouplevels = $_COOKIE["am_selected_grouplevels"];				
			$accesslevel_array = array();
			$accesslevel_array = explode(',',$am_selected_grouplevels);
			$cookie = 1;			
		}else{			
			$cookie = 0;				
		}		
		
		$accesslevels = $this->get_grouplevels($backend_only, $include_superadmin, $only_groups);	
			
		$html = '<select name="accesslevel_selector[]" id="accesslevel_selector" multiple="multiple" class="inputbox';
		if(!$this->am_config['multiselector_bootstrap']){
			$html .= ' chzn-done';
		}		
		$html .= '" size="8">';			
		$html .= '<option value="all" onclick="select_all_usergroups();">'.JText::_('JALL').'</option>';
		foreach($accesslevels as $accesslevel){
			$html .= '<option value="'.$accesslevel->id.'"';
			if(!$cookie || ($cookie && in_array($accesslevel->id,$accesslevel_array))){
				$html .= ' selected="selected"';
			}
			$html .= '>';
			if(($this->am_config['based_on']=='group' || $only_groups) && !$this->am_config['multiselector_bootstrap']){	
				$html .= str_repeat('- ',$accesslevel->hyrarchy);	
			}						
			$html .= $accesslevel->title;						
			$html .= '</option>';
		}
		$html .= '</select>';
		if(!$this->am_config['multiselector_bootstrap']){
			$html .= '<script>';
			$html .= "var JNC_jQuery = jQuery.noConflict();";
			$html .= "JNC_jQuery(function() {";
			$html .= "JNC_jQuery(accesslevel_selector).chosen('destroy');";	
			$html .= "});";
			$html .= '</script>';
		}		
		
		return $html;
	}	
	
	function get_grouplevels($backend_only = 0, $include_superadmin = 0, $only_groups = 0, $only_selected = 0){
		
		$db = JFactory::getDBO();
		
		//cookie
		$accesslevel_array = array();		
		if (isset($_COOKIE["am_selected_grouplevels"])) {
			$am_selected_grouplevels = $_COOKIE["am_selected_grouplevels"];			
			$accesslevel_array = explode(',',$am_selected_grouplevels);							
		}
		
		//query
		$query = $db->getQuery(true);	
		if($this->am_config['based_on']=='level' && !$only_groups){				
			$query->select('a.id, a.title');
			$query->from('#__viewlevels AS a');
			$query->order($this->am_config['level_sort']);
		}else{								
			$query->select('a.id as id, a.title as title, a.parent_id as parent_id, COUNT(DISTINCT b.id) AS hyrarchy');
			$query->from('#__usergroups AS a');
			$query->leftJoin('#__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt');
			if($backend_only){
				if($include_superadmin){
					$this->backend_usergroups[] = '8';
				}
				$this->get_backend_usergroups();
				$backend_usergroups = implode(',', $this->backend_usergroups);
				$query->where('a.id in ('.$backend_usergroups.')');			
			}	
			if(!$include_superadmin){
				$query->where('a.id <> '.$db->Quote('8'));	
			}						
			$query->group('a.id');
			$query->order('a.lft');									
		}
		if($only_selected && isset($accesslevel_array[0])){
			if($accesslevel_array[0]!=''){
				$query->where('a.id in ('.$am_selected_grouplevels.')');
			}
		}	
		$grouplevels = $db->setQuery((string)$query);	
		$grouplevels = $db->loadObjectList();	
			
		return $grouplevels;
	}
	
	function truncate_string($string, $length){
		$dots='...';
		$string = trim($string);		
		if(strlen($string)<=$length){
			return $string;	
		}

		if(!strstr($string," ")){
			return substr($string,0,$length).$dots;
		}	
		$lengthf = create_function('$string','return substr($string,0,strrpos($string," "));');	
		$string = substr($string,0,$length);	
		$string = $lengthf($string);
		while(strlen($string)>$length){
			$string=$lengthf($string);
		}	
		return $string.$dots;
	}
	
	function array_to_csv($array){	
		$return = '';	
		for($n = 0; $n < count($array); $n++){
			if($n){
				$return .= ',';
			}
			$row = each($array);
			$value = $row['value'];
			if(is_string($value)){
				$value = addslashes($value);
			}	
			$return .= '"'.$value.'"';		
		}		
		return $return;
	}
	
	function csv_to_array($csv){		
		$array = array();
		$temp = explode(',', $csv);
		for($n = 0; $n < count($temp); $n++){
			$value = str_replace('"','',$temp[$n]);
			$array[] = $value;
		}
		return $array;
	}
	
	function enable_plugin(){
		$database = JFactory::getDBO();
		$ds = DIRECTORY_SEPARATOR;
		$plugin = JRequest::getVar('plugin');
		$folder = JRequest::getVar('folder', '');
		$allowed_plugins = array('system-accessmanager', 'search-contentaccessmanager', 'search-categoriesaccessmanager');
		if(in_array($folder.'-'.$plugin, $allowed_plugins)){			
			if(!file_exists(JPATH_ROOT.$ds.'plugins'.$ds.$folder.$ds.$plugin.$ds.$plugin.'.php')){
				$message = JText::_('COM_ACCESSMANAGER_BOTNOTINSTALLED').' '.JText::_('COM_ACCESSMANAGER_BOTNOTPUBLISHED');
			}else{
				$database = JFactory::getDBO();
				$database->setQuery( "UPDATE #__extensions SET enabled='1' WHERE element='$plugin' AND folder='$folder' AND type='plugin' "	);
				$database->query();
				$message = $folder.' - '.$plugin.' '.JText::_('COM_ACCESSMANAGER_PLUGIN_ENABLED');
			}
		}else{
			$message = JText::_('COM_ACCESSMANAGER_BOTNOTINSTALLED').' '.JText::_('COM_ACCESSMANAGER_BOTNOTPUBLISHED');
		}	
		
		$from = JRequest::getVar('from', 'configuration');
		$app = JFactory::getApplication();
		$url = 'index.php?option=com_accessmanager&view='.$from;
		$app->redirect($url, $message);
	}	
	
	function disable_plugin(){
		$database = JFactory::getDBO();
		$plugin = JRequest::getVar('plugin');
		$folder = JRequest::getVar('folder', '');
		$allowed_plugins = array('search-content', 'search-categories');
		$message = 'can not disable that plugin';
		if(in_array($folder.'-'.$plugin, $allowed_plugins)){
			$database->setQuery( "UPDATE #__extensions SET enabled='0' WHERE element='$plugin' AND folder='$folder' AND type='plugin' "	);
			$database->query();
			$message = $folder.' - '.$plugin.' '.JText::_('COM_ACCESSMANAGER_PLUGIN_DISABLED');			
		}
		
		$from = JRequest::getVar('from', 'configuration');
		$app = JFactory::getApplication();
		$url = 'index.php?option=com_accessmanager&view='.$from;
		$app->redirect($url, $message);
	}
	
	function clean_search_string($search){
		if (strpos($search, '"') !== false) {
			$search = str_replace(array('=', '<'), '', $search);
		}
		$search = JString::$this->am_strtolower($search);
		return $search;
	}
	
	function ajax_version_checker(){
		$message = JText::_('COM_ACCESSMANAGER_VERSION_CHECKER_NOT_AVAILABLE');	
		$url = 'http://www.pages-and-items.com/latest_version.php?extension=accessmanager';		
		$file_object = @fopen($url, "r");		
		if($file_object == TRUE){
			$version = fread($file_object, 1000);
			$message = JText::_('COM_ACCESSMANAGER_LATEST_VERSION').' = '.$version;
			if($this->version!=$version){
				$message .= '<div><span class="am_red">'.JText::_('COM_ACCESSMANAGER_NEWER_VERSION').'</span>.</div>';
				if($this->am_version_type=='pro'){
					$download_url = 'http://www.pages-and-items.com/my-extensions';
				}else{
					$download_url = 'http://www.pages-and-items.com/extensions/access-manager';
				}
				$message .= '<div><a href="'.$download_url.'" target="_blank">'.JText::_('COM_ACCESSMANAGER_DOWNLOAD').'</a></div>';
			}else{
				$message .= '<div><span class="am_green">'.JText::_('COM_ACCESSMANAGER_IS_LATEST_VERSION').'</span>.</div>';
			}
			fclose($file_object);
		}
		
		//reset version checker session
		$app = JFactory::getApplication();
		$app->setUserState( "com_accessmanager.latest_version_message", '' );
		
		echo $message;
		exit;
	}
	
	function part_save(){	
	
		$database = JFactory::getDBO();		
			
		// Check for request forgeries 
		JRequest::checkToken() or jexit('Invalid Token');
		
		//get vars
		$id = intval(JRequest::getVar('id', 0, 'post'));
		$name = strip_tags(JRequest::getVar('name', '', 'post'));
		$description = strip_tags(JRequest::getVar('description', '', 'post'));		
		$name = addslashes($name);
		$description = addslashes($description);		
		
		if($id==0){
			//new part
			$database->setQuery( "INSERT INTO #__accessmanager_parts SET name='$name', description='$description' ");
			$database->query();
		}else{
			//edit part
			$database->setQuery( "UPDATE #__accessmanager_parts SET name='$name', description='$description' WHERE id='$id' ");
			$database->query();
		}	
		
		$this->setRedirect("index.php?option=com_accessmanager&view=parts", JText::_('COM_ACCESSMANAGER_PART_SAVED'));
	}	
	
	function part_delete(){	
	
		$database = JFactory::getDBO();
		
		// Check for request forgeries 
		JRequest::checkToken() or jexit('Invalid Token');			
		
		$cid = JRequest::getVar('cid', null, 'post', 'array');		
		
		if (!is_array($cid) || count($cid) < 1) {
			echo "<script> alert(".JText::_('COM_ACCESSMANAGER_SELECT_ITEM_TO_DELETE')."); window.history.go(-1);</script>";
			exit();
		}
		
		if (count($cid)){						
			
			//update rows in partsaccess table of part which stops existing
			$part_access_to_delete = array();
			foreach($cid as $part_id){	
				$database->setQuery("SELECT id, part_group "
				."FROM #__accessmanager_partsaccess "
				."WHERE part_group LIKE '%".$part_id."_%' "				
				);
				$rows = $database->loadObjectList();
				foreach($rows as $row){	
					$part_access = $row->part_group;
					if(strpos($part_access, $part_id.'_')==0){
						$part_access_to_delete[] = $row->id;
					}					
				}
			}
			$part_access_to_delete = implode(',', $part_access_to_delete);
			//delete parts
			$database->setQuery("DELETE FROM #__accessmanager_partsaccess WHERE id IN ($part_access_to_delete)");
			$database->query();
			
			//delete parts
			$ids = implode(',', $cid);
			$database->setQuery("DELETE FROM #__accessmanager_parts WHERE id IN ($ids)");
			$database->query();
		}
		
		$this->setRedirect("index.php?option=com_accessmanager&view=parts", JText::_('COM_ACCESSMANAGER_PART_DELETED'));
	}
	
	function am_strtolower($string){
		if(function_exists('mb_strtolower')){			
			$string = mb_strtolower($string, 'UTF-8');
		}
		return $string;
	}
	
	function check_emi_ie6_plugin(){
		$ds = DIRECTORY_SEPARATOR;
		$return = 1;
		$file = JPATH_ROOT.$ds.'plugins'.$ds.'system'.$ds.'emiIE6warning.php';	
		if ($fp = @fopen($file, "rb")){	
			$null = NULL;		
			$file_string = file_get_contents($file, $null, $null, 10, 2000);			
			fclose ($fp);					
			if(strpos($file_string, 'plgSystememiIE6warning')){			
				$return = 0;
			}			
		}			
		return $return;
	}
	
	function reorder_system_plugin(){
	
		$database = JFactory::getDBO();
		
		$database->setQuery("SELECT element, ordering "
		." FROM #__extensions "
		." WHERE type='plugin' AND folder='system' "
		." ORDER BY ordering ASC "
		);
		$rows = $database->loadObjectList();
		
		$order_element_array = array();		
		$am_current_ordering = 0;
		$am_index_order = 0;		
		$am_in_table = 0;
		$order_index = 0;
		foreach($rows as $row){				
			$element = $row->element;	
			$order = $row->ordering;	
			if($row->element=='accessmanager'){
				$am_current_ordering = $row->ordering;
				$am_index_order = $order_index;
				$am_in_table = 1;
			}
			$order_element_array[] = array($order, $element);			
			$order_index++;
		}		
		
		if($am_in_table || $am_current_ordering==0){
		
			//default for if no ordering was set
			$new_order = '-29000';
			
			//if AM is second or later 
			if($am_index_order!=0){
				//check if first is Akeeba
				$first_akeeba = 0;
				$first_order = $order_element_array[0][0];
				if($order_element_array[0][1]=='oneclickaction' || $order_element_array[0][1]=='admintools'){
					$first_akeeba = 1;					
				}
				//check if second is Akeeba
				$second_akeeba = 0;
				$second_order = $order_element_array[1][0];
				if($order_element_array[1][1]=='oneclickaction' || $order_element_array[1][1]=='admintools'){
					$second_akeeba = 1;					
				}
				//if first is not Akeeba, make AM first if -29000 if not negative enough already
				if(!$first_akeeba && $first_order<=-29000){
					$new_order = $first_order-1;
				//if first is Akeeba, but second is not, get in between
				}elseif($first_akeeba && !$second_akeeba){
					$new_order = $second_order-1;				
				}				
			}			
		
			$database->setQuery( "UPDATE #__extensions SET ordering='$new_order' WHERE element='accessmanager' AND folder='system' AND type='plugin' "	);
			$database->query();
			$message = JText::_('COM_ACCESSMANAGER_PLUGIN_REORDERED');
		
		}else{
			$message = JText::_('COM_ACCESSMANAGER_BOTNOTINSTALLED');
		}			
		
		$url = 'index.php?option=com_accessmanager&view=configuration';
		$this->setRedirect($url, $message);
	}	
	
	function check_ztools_plugin(){
		$database = JFactory::getDBO();		
		$database->setQuery("SELECT enabled "
		." FROM #__extensions "
		." WHERE element='plg_ztools' AND type='plugin' AND folder='system' "		
		);
		$rows = $database->loadObjectList();
		$enabled = 0;
		foreach($rows as $row){
			$enabled = $row->enabled;
		}
		return $enabled;
	}
	
	function disable_ztools(){
		$database = JFactory::getDBO();		
		$database->setQuery( "UPDATE #__extensions SET enabled='0' WHERE element='plg_ztools' AND type='plugin' AND folder='system' ");
		$database->query();
		$url = 'index.php?option=com_accessmanager';
		$message = JText::_('COM_ACCESSMANAGER_PLUGIN_DISABLED');
		$this->setRedirect($url, $message);
	}
	
	function clean_article_access_table(){
	
		$database = JFactory::getDBO();
		
		//get array of article ids
		$database->setQuery("SELECT id "
		." FROM #__content "
		." WHERE state<>'-2' "
		);
		$content_article_ids = $database->loadColumn();
		
		//get item-rights
		$database->setQuery("SELECT id, itemid_groupid "
		." FROM #__accessmanager_articles "
		);
		$rows = $database->loadObjectList();
		
		//check if article still exists
		foreach($rows as $row){	
			$temp = $row->itemid_groupid;
			$temp_array = explode('__', $temp);
			$temp_item_id = $temp_array[0];	
			if(!in_array($temp_item_id, $content_article_ids)){	
				//articles does no longer exist, so delete right for it	
				$row_id = $row->id;		
				$database->setQuery("DELETE FROM #__accessmanager_articles WHERE id='$row_id'");
				$database->query();
			}			
		}
	}
	
	function clean_category_access_table(){
	
		$database = JFactory::getDBO();
		
		//get array of category ids
		$database->setQuery("SELECT id "
		." FROM #__categories "
		." WHERE published<>'-2' "
		);
		$category_ids = $database->loadColumn();
		
		//get category-rights
		$database->setQuery("SELECT id, category_groupid "
		." FROM #__accessmanager_categories "
		);
		$rows = $database->loadObjectList();
		
		//check if category still exists
		foreach($rows as $row){	
			$temp = $row->category_groupid;
			$temp_array = explode('__', $temp);
			$temp_cat_id = $temp_array[0];	
			if(!in_array($temp_cat_id, $category_ids)){	
				//category does no longer exist, so delete right for it	
				$row_id = $row->id;		
				$database->setQuery("DELETE FROM #__accessmanager_categories WHERE id='$row_id'");
				$database->query();
			}
			
		}
	}
	
	function clean_menu_access_table(){
	
		$database = JFactory::getDBO();
		
		//get array of menuitem ids
		$database->setQuery("SELECT id "
		." FROM #__menu "
		." WHERE published<>'-2' "
		);
		$menuitem_ids = $database->loadColumn();
		
		//get menuitem-rights
		$database->setQuery("SELECT id, menuid_groupid "
		." FROM #__accessmanager_menuaccess "
		);
		$rows = $database->loadObjectList();
		
		//check if menuitem still exists
		foreach($rows as $row){	
			$temp = $row->menuid_groupid;
			$temp_array = explode('_', $temp);
			$temp_menu_id = $temp_array[0];	
			if(!in_array($temp_menu_id, $menuitem_ids)){	
				//menuitem does no longer exist, so delete right for it	
				$row_id = $row->id;		
				$database->setQuery("DELETE FROM #__accessmanager_menuaccess WHERE id='$row_id'");
				$database->query();
			}			
		}
	}
	
	function clean_module_access_table(){
	
		$database = JFactory::getDBO();
		
		//get array of module ids
		$database->setQuery("SELECT id "
		." FROM #__modules "
		." WHERE published<>'-2' "
		);
		$module_ids = $database->loadColumn();
		
		//get module-rights
		$database->setQuery("SELECT id, module_groupid "
		." FROM #__accessmanager_modules "
		);
		$rows = $database->loadObjectList();
		
		//check if module still exists
		foreach($rows as $row){	
			$temp = $row->module_groupid;
			$temp_array = explode('__', $temp);
			$temp_module_id = $temp_array[0];	
			if(!in_array($temp_module_id, $module_ids)){	
				//module does no longer exist, so delete right for it	
				$row_id = $row->id;		
				$database->setQuery("DELETE FROM #__accessmanager_modules WHERE id='$row_id'");
				$database->query();
			}			
		}
	}
	
	function check_ubar_plugin(){
		$database = JFactory::getDBO();	
		$ds = DIRECTORY_SEPARATOR;
		//check if plugin is enabled	
		$database->setQuery("SELECT enabled "
		." FROM #__extensions "
		." WHERE element='ubar' AND type='plugin' AND folder='system' "		
		);
		$rows = $database->loadObjectList();
		$enabled = 0;
		foreach($rows as $row){
			$enabled = $row->enabled;
		}
		
		//check if the bad code is in the plugin
		$bad_code = 0;
		$file = JPATH_ROOT.$ds.'plugins'.$ds.'system'.$ds.'ubar'.$ds.'ubar.php';	
		if ($fp = @fopen($file, "rb")){	
			$null = NULL;		
			$file_string = file_get_contents($file, $null, $null, 10, 3000);				
			fclose ($fp);					
			if(strpos($file_string, '$this->_doc')){			
				$bad_code = 1;
			}			
		}
		
		$return = 0;
		if($enabled && $bad_code){
			$return = 1;
		}
		return $return;
	}
	
	function check_fua_plugin(){
		$database = JFactory::getDBO();				
		$database->setQuery("SELECT enabled "
		." FROM #__extensions "
		." WHERE element='frontenduseraccess' AND type='plugin' AND folder='system' "		
		);
		$rows = $database->loadObjectList();
		$enabled = 0;
		foreach($rows as $row){
			$enabled = $row->enabled;
		}
		return $enabled;
	}
	
	function get_backend_usergroups(){
		
		//get main asset
		$database = JFactory::getDBO();	
		$database->setQuery("SELECT rules FROM #__assets WHERE id=1 ");	
		$asset = $database->loadResult();
		
		//make into array
		$registry = new JRegistry;
		$registry->loadString($asset);
		$asset_array = $registry->toArray();
		
		//get configured backend groups	
		$temp = $asset_array['core.login.admin'];		
		for($n = 0; $n < count($temp); $n++){
			$row = each($temp);
			if($row['value']=='1'){							
				$this->set_backend_usergroup($row['key']);
			}
		}
	}
	
	function set_backend_usergroup($group){
	
		$database = JFactory::getDBO();	
			
		$this->backend_usergroups[] = $group;	

		//get child groups
		$database->setQuery("SELECT id "
		." FROM #__usergroups "
		." WHERE parent_id='$group' "		
		);
		$rows = $database->loadObjectList();
		foreach($rows as $row){	
			//recurse to get all children
			$this->set_backend_usergroup($row->id);
		}
	}
	
	function disable_fua(){
	
		$database = JFactory::getDBO();
		$app = JFactory::getApplication();			
		
		$database->setQuery( "UPDATE #__extensions SET enabled='0' WHERE element='frontenduseraccess' AND type='plugin' AND folder='system' ");
		$database->query();			
		$app->redirect('index.php?option=com_accessmanager&view='.JRequest::getVar('returnview', ''));
	}
	
	function check_jat3_plugin_and_cache(){
		
		$database = JFactory::getDBO();
		$ds = DIRECTORY_SEPARATOR;
		
		$database->setQuery("SELECT enabled "
		." FROM #__extensions "
		." WHERE element='jat3' AND type='plugin' AND folder='system' "		
		);
		$rows = $database->loadObjectList();
		$plugin_enabled = 0;
		foreach($rows as $row){
			$plugin_enabled = $row->enabled;
		}		
		$templates_with_enabled_cache = array();
		if($plugin_enabled){
			jimport( 'joomla.filesystem.folder' );
			$templates = JFolder::folders(JPATH_ROOT.$ds.'templates');
			foreach($templates as $template){
				$file = JPATH_ROOT.$ds.'templates'.$ds.$template.$ds.'params.ini';	
				if ($fp = @fopen($file, "rb")){	
					$null = NULL;		
					$file_string = file_get_contents($file, $null, $null, 0, 300);				
					fclose ($fp);					
					if(strpos($file_string, 'cache="1"')){			
						$templates_with_enabled_cache[] = $template;					
					}			
				}
			}	
		}
		return $templates_with_enabled_cache;
	}
	
	function check_aridatatables($file){	
		$bad_code = 0;			
		if ($fp = @fopen($file, "rb")){	
			$null = NULL;		
			$file_string = file_get_contents($file, $null, $null, 10, 3000);				
			fclose ($fp);					
			if(!strpos($file_string, 'if(!class_exists(\'JModuleHelper\')){')){			
				$bad_code = 1;
			}			
		}		
		return $bad_code;
	}
	
	function import_access_settings(){
		JRequest::checkToken() or jexit('Invalid Token');	
		$app = JFactory::getApplication();
		if(JRequest::getVar('import_article_access', '')){			
			$this->do_import_access_settings('article', 'content', 'state');
		}
		if(JRequest::getVar('import_category_access', '')){			
			$this->do_import_access_settings('category', 'categories', 'published');
		}
		if(JRequest::getVar('import_module_access', '')){			
			$this->do_import_access_settings('module', 'modules', 'published');
		}
		if(JRequest::getVar('import_menuitem_access', '')){			
			$this->do_import_access_settings('menuitem', 'menu', 'published');
		}
		$app->redirect('index.php?option=com_accessmanager&view=tools', JText::_('COM_ACCESSMANAGER_ACCESS_IMPORTED'));
	}
	
	protected function do_import_access_settings($type, $table, $column_enabled){
		$database = JFactory::getDBO();		
		
		//only these types
		$types = array('article', 'category', 'module', 'menuitem', 'adminmenumanager');
		if(!in_array($type, $types)){
			return;
		}
		
		//get import modus
		$import_rights_to = JRequest::getVar('import_rights_to', '');
		if($type=='adminmenumanager'){
			$import_rights_to = JRequest::getVar('import_rights_to_amm', '');
		}
		
		//if group, make level-groups array
		if($import_rights_to=='group'){
			$accesslevels_array = array();
			$database->setQuery("SELECT id, title, rules "
			."FROM #__viewlevels "
			);
			$accesslevels = $database->loadObjectList();				
			foreach($accesslevels as $accesslevel){				
				$rules = $accesslevel->rules;
				$rules = str_replace('[','',$rules);
				$rules = str_replace(']','',$rules);
				$level_id = $accesslevel->id;							
				$usergroups_array = explode(',',$rules);			
				$accesslevels_array[$level_id] = $usergroups_array;						
			}			
		}
		
		//clean all rights of access type
		$database->setQuery("DELETE FROM #__accessmanager_rights WHERE type='$type' ");
		$database->query();			
		
		$access_column = 'access';
		if($type=='adminmenumanager'){
			if($import_rights_to=='group'){
				$access_column = 'accessgroup';
			}else{
				$access_column = 'accesslevel';
			}
		}	
		
		//get all items
		$database->setQuery("SELECT id, $access_column "
		." FROM #__$table "
		." WHERE $column_enabled<>'-2' "
		);
		$items = $database->loadObjectList();
		
		//loop all items and insert rights
		foreach($items as $item){			
			if($type=='adminmenumanager'){
				if($import_rights_to=='group'){					
					$access = $item->accessgroup;
				}else{					
					$access = $item->accesslevel;
				}
			}else{
				$access = $item->access;
			}
			if($access){
				if($import_rights_to=='level'){	
					$database->setQuery( "INSERT INTO #__accessmanager_rights SET item='$item->id', `level`='$access', type='$type' ");
					$database->query();	
				}else{	
					if($type=='adminmenumanager'){
						$database->setQuery( "INSERT INTO #__accessmanager_rights SET item='$item->id', `group`='$access', type='$type' ");
						$database->query();	
					}else{				
						$groups_array = $accesslevels_array[$access];								
						for($n = 0; $n < count($groups_array); $n++){	
							$group = $groups_array[$n];
							if($group!=8){
								$database->setQuery( "INSERT INTO #__accessmanager_rights SET item='$item->id', `group`='$group', type='$type' ");
								$database->query();	
							}
						}
					}
				}
			}
		}	
		
		//set the access type to not-reversed
		$this->am_config[$type.'_reverse_access'] = '';
		$this->am_config['display_import_message'] = '';
		$this->rebuild_and_save_config();
		
	}
	
	function do_not_show_import_message(){
		$app = JFactory::getApplication();
		$returnview = JRequest::getVar('returnview', '');
		$this->am_config['display_import_message'] = '';
		$this->rebuild_and_save_config();
		$app->redirect('index.php?option=com_accessmanager&view='.$returnview);
	}
	
	function adminmenumanager_save(){
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('menu_access_hidden', null, 'post', 'array');
		$this->save_access_rights($rights, 'adminmenumanager');		
		
		$this->am_config['adminmenumanager_active'] = JRequest::getVar('adminmenumanager_active', '', 'post');
		$this->am_config['adminmenumanager_reverse_access'] = JRequest::getVar('adminmenumanager_reverse_access', '', 'post');
		$this->am_config['adminmenumanager_multigroup_access_requirement'] = JRequest::getVar('adminmenumanager_multigroup_access_requirement', '', 'post');
		$this->am_config['adminmenumanager_default_access'] = JRequest::getVar('adminmenumanager_default_access', array(), 'post', 'array');
		$this->rebuild_and_save_config();		

		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_MENUITEM_ACCESS_SAVED'));
		}else{		
			$this->setRedirect("index.php?option=com_accessmanager&view=adminmenumanager", JText::_('COM_ACCESSMANAGER_MENUITEM_ACCESS_SAVED'));
		}
	}
	
	function give_super_admin_module_rights_back(){
		$app = JFactory::getApplication();
		$this->am_config['module_superadmins'] = 'true';
		$this->rebuild_and_save_config();
		$url = 'index.php?option=com_accessmanager&view=modules';
		$message = JText::_('COM_ACCESSMANAGER_MODULE_ACCESS_SAVED');
		$app->redirect($url, $message);
	}
	
	function import_access_settings_amm(){
		JRequest::checkToken() or jexit('Invalid Token');	
		$app = JFactory::getApplication();
		$ds = DIRECTORY_SEPARATOR;
		
		if(file_exists(JPATH_ROOT.$ds.'administrator'.$ds.'components'.$ds.'com_adminmenumanager'.$ds.'controller.php')){		
			$this->do_import_access_settings('adminmenumanager', 'adminmenumanager_menuitems', 'published');
			$message = JText::_('COM_ACCESSMANAGER_ACCESS_IMPORTED');
		}else{
			$message = 'component Access Manager '.JText::_('COM_ACCESSMANAGER_IS_NOT_INSTALLED');
		}
		
		$app->redirect('index.php?option=com_accessmanager&view=tools', $message);
	}
	
	function import_access_rights_fua(){
	
		JRequest::checkToken() or jexit('Invalid Token');	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		
		$fua_groups = JRequest::getVar('fua_groups', null, 'post', 'array');
		$groups_levels = JRequest::getVar('grouplevels_select', null, 'post', 'array');
		
		//rework array
		$fua_groupslevels = array();		
		for($n = 0; $n < count($groups_levels); $n++){			
			if($groups_levels[$n]!=0){
				$fua_group = $fua_groups[$n];
				$fua_groupslevels[$fua_group] = $groups_levels[$n];
			}
		}
		
		//articles
		if(JRequest::getVar('import_article_access_fua', '')){						
			$this->import_access_rights_fua_process('items', 'itemid_groupid', 'article', $fua_groupslevels);
		}	
		
		//categories
		if(JRequest::getVar('import_category_access_fua', '')){						
			$this->import_access_rights_fua_process('categories', 'category_groupid', 'category', $fua_groupslevels);
		}
		
		//modules
		if(JRequest::getVar('import_module_access_fua', '')){						
			$this->import_access_rights_fua_process('modules_two', 'module_groupid', 'module', $fua_groupslevels);
		}
		
		//components
		if(JRequest::getVar('import_component_access_fua', '')){						
			$this->import_access_rights_fua_process('components', 'component_groupid', 'component', $fua_groupslevels);
		}
		
		//menuitems
		if(JRequest::getVar('import_menuitem_access_fua', '')){						
			$this->import_access_rights_fua_process('menuaccess', 'menuid_groupid', 'menuitem', $fua_groupslevels);
		}
		
		//parts
		if(JRequest::getVar('import_part_access_fua', '')){						
			$this->import_access_rights_fua_process('partsaccess', 'part_group', 'part', $fua_groupslevels);
		}	
		
		//the parts itself
		if(JRequest::getVar('import_parts_fua', '')){						
			//delete existing parts			
			$query = $db->getQuery(true);
			$query->delete();
			$query->from('#__accessmanager_parts');
			$query->where('id!='.$db->q('0'));
			$db->setQuery((string)$query);
			$db->query();
			
			//get fua parts			
			$query = $db->getQuery(true);
			$query->select('name, description');
			$query->from('#__fua_parts');			
			$rows = $db->setQuery($query);				
			$parts = $db->loadObjectList();
			
			//insert them to am table
			foreach($parts as $part){
				$query = $db->getQuery(true);
				$query->insert('#__accessmanager_parts');
				$query->set('name='.$db->q($part->name));							
				$query->set('description='.$db->q($part->description));								
				$db->setQuery((string)$query);
				$db->query();
			}
		}		
		
		$app->redirect('index.php?option=com_accessmanager&view=tools', JText::_('COM_ACCESSMANAGER_ACCESS_IMPORTED'));
	}
	
	function import_access_rights_fua_process($fua_table, $column, $type, $fua_groupslevels){	
	
		$db = JFactory::getDBO();
		
		//delete all rights for this restriction type
		$query = $db->getQuery(true);
		$query->delete();
		$query->from('#__accessmanager_rights');
		$query->where('type='.$db->q($type));
		$db->setQuery((string)$query);
		$db->query();
		
		//get all fua rights for this restriction type		
		$query = $db->getQuery(true);
		$query->select($db->quoteName($column));
		$query->from('#__fua_'.$fua_table);		
		$rows = $db->setQuery($query);				
		$rights_array = $db->loadColumn();			
		
		$separator = '__';
		if($type=='menuitem'){
			$separator = '_';
		}	
		
		//insert rights
		foreach($rights_array as $right){				
			$temp = explode($separator, $right);
			$item = $temp[0];
			$fua_group = $temp[1];	
			if(isset($fua_groupslevels[$fua_group])){	
				$query = $db->getQuery(true);
				$query->insert('#__accessmanager_rights');
				$query->set('item='.$db->q($item));	
				$grouplevel = (int)$fua_groupslevels[$fua_group];
				$query->set($db->quoteName($this->am_config['based_on']).'='.$fua_groupslevels[$fua_group]);				
				$query->set('type='.$db->q($type));	
				$query->set('access=0');			
				$db->setQuery((string)$query);
				$db->query();
			}			
		}		
	}
	
	function modulesadmin_save(){
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('modulesadmin_access_hidden', null, 'post', 'array');
		$this->save_access_rights($rights, 'modulesadmin');
		
		$this->am_config['modulesadmin_active'] = JRequest::getVar('modulesadmin_active', '', 'post');
		$this->am_config['modulesadmin_reverse_access'] = JRequest::getVar('modulesadmin_reverse_access', '', 'post');		
		$this->am_config['modulesadmin_multigroup_access_requirement'] = JRequest::getVar('modulesadmin_multigroup_access_requirement', 'one_group', 'post');	
		$this->am_config['modulesadmin_superadmins'] = JRequest::getVar('modulesadmin_superadmins', '', 'post');				
		$this->am_config['modulesadmin_default_access'] = JRequest::getVar('modulesadmin_default_access', array(), 'post', 'array');
		$this->rebuild_and_save_config();
		
		$this->helper->clear_cache('com_modules');
		$this->helper->clear_cache('mod_menu');
		
		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', JText::_('COM_ACCESSMANAGER_MODULE_ACCESS_SAVED'));
		}else{				
			$this->setRedirect("index.php?option=com_accessmanager&view=modulesadmin", JText::_('COM_ACCESSMANAGER_MODULE_ACCESS_SAVED'));
		}
	}
	
	function assign_users_from_fua_groups(){
	
		JRequest::checkToken() or jexit('Invalid Token');	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		
		$fua_group = JRequest::getVar('fuagroup_select', '');	
		$joomla_group = JRequest::getVar('joomlagroup_select', '');	
		
		if($fua_group && $joomla_group){
			//get all users from this fua group
			$query = $db->getQuery(true);
			$query->select('user_id');
			$query->from('#__fua_userindex');
			$query->where('group_id LIKE "%\"'.$fua_group.'\"%"');
			$rows = $db->setQuery($query);				
			$user_ids = $db->loadColumn();			
			
			//check which of these users are already assigned to this joomla group
			$query = $db->getQuery(true);
			$query->select('user_id');
			$query->from('#__user_usergroup_map');			
			$query->where('user_id IN ('.implode(',', $user_ids).')');
			$query->where('group_id='.$db->q($joomla_group));			
			$rows = $db->setQuery($query);				
			$user_ids_already_assigned = $db->loadColumn();	
			
			//if user is not already assigned to group, do insert
			for($n = 0; $n < count($user_ids); $n++){
				if(!in_array($user_ids[$n], $user_ids_already_assigned)){
					$query = $db->getQuery(true);
					$query->insert('#__user_usergroup_map');
					$query->set('user_id='.$db->q($user_ids[$n]));
					$query->set('group_id='.$db->q($joomla_group));				
					$db->setQuery((string)$query);
					$db->query();
				}
			}			
			
			$lang = JFactory::getLanguage();
			$lang->load('com_templates', JPATH_ADMINISTRATOR, null, false);	
			$message = JText::_('COM_ACCESSMANAGER_ONE_GROUP').' '.$this->am_strtolower(JText::_('COM_TEMPLATES_HEADING_ASSIGNED'));
		}else{
			$message = JText::_('COM_ACCESSMANAGER_NEEDTOSELECTBOTH');
		}		
		
		$app->redirect('index.php?option=com_accessmanager&view=tools', $message);
	}
	
	function contacts_save(){		
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('item_access_hidden', null, 'post', 'array');		
		$this->save_access_rights($rights, 'contact');
		
		$this->am_config['contact_active'] = JRequest::getVar('contact_active', '', 'post');
		$this->am_config['contact_reverse_access'] = JRequest::getVar('contact_reverse_access', '', 'post');
		$this->am_config['contact_multigroup_access_requirement'] = JRequest::getVar('contact_multigroup_access_requirement', 'one_group', 'post');
		$this->am_config['contact_superadmins'] = JRequest::getVar('contact_superadmins', '', 'post');
		$this->am_config['contact_message_type'] = JRequest::getVar('contact_message_type', 'alert', 'post');
		$no_contact_access_url = addslashes(JRequest::getVar('no_contact_access_url', '', 'post'));			
		$this->am_config['no_contact_access_url'] = str_replace('=','[equal]',$no_contact_access_url);			
		$this->am_config['contact_default_access'] = JRequest::getVar('contact_default_access', array(), 'post', 'array');
		$this->rebuild_and_save_config();
		
		$this->helper->clear_cache('com_contact');
	
		//include languages. Reuse or die ;-)#
		$lang = JFactory::getLanguage();
		$lang->load('com_contact', JPATH_ADMINISTRATOR, null, false);
		$message = $this->am_strtolower(JText::_('COM_CONTACT_SUBMENU_CONTACTS'));
		$message .= ' '.$this->am_strtolower(JText::_('JFIELD_ACCESS_LABEL'));
		$message .= ' '.$this->am_strtolower(JText::_('COM_ACCESSMANAGER_SAVED'));
		
		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', $message);
		}else{				
			$this->setRedirect('index.php?option=com_accessmanager&view=contacts', $message);
		}
	}
	
	function weblinks_save(){		
		JRequest::checkToken() or jexit('Invalid Token');		
		$rights = JRequest::getVar('item_access_hidden', null, 'post', 'array');		
		$this->save_access_rights($rights, 'weblink');
		
		$this->am_config['weblink_active'] = JRequest::getVar('weblink_active', '', 'post');
		$this->am_config['weblink_reverse_access'] = JRequest::getVar('weblink_reverse_access', '', 'post');
		$this->am_config['weblink_multigroup_access_requirement'] = JRequest::getVar('weblink_multigroup_access_requirement', 'one_group', 'post');
		$this->am_config['weblink_superadmins'] = JRequest::getVar('weblink_superadmins', '', 'post');
		$this->am_config['weblink_message_type'] = JRequest::getVar('weblink_message_type', 'alert', 'post');
		$no_weblink_access_url = addslashes(JRequest::getVar('no_weblink_access_url', '', 'post'));			
		$this->am_config['no_weblink_access_url'] = str_replace('=','[equal]',$no_weblink_access_url);			
		$this->am_config['weblink_default_access'] = JRequest::getVar('weblink_default_access', array(), 'post', 'array');
		$this->rebuild_and_save_config();
		
		$this->helper->clear_cache('com_weblinks');
	
		//include languages. Reuse or die ;-)#
		$lang = JFactory::getLanguage();
		$lang->load('com_weblinks', JPATH_ADMINISTRATOR, null, false);
		$message = $this->am_strtolower(JText::_('COM_WEBLINKS'));
		$message .= ' '.$this->am_strtolower(JText::_('JFIELD_ACCESS_LABEL'));
		$message .= ' '.$this->am_strtolower(JText::_('COM_ACCESSMANAGER_SAVED'));
		
		if(JRequest::getVar('save_and_close', '')){
			$this->setRedirect('index.php?option=com_accessmanager&view=panel', $message);
		}else{				
			$this->setRedirect('index.php?option=com_accessmanager&view=weblinks', $message);
		}
	}
	
	function check_t3_plugin_and_cache(){
		
		$database = JFactory::getDBO();
		$ds = DIRECTORY_SEPARATOR;
		
		$database->setQuery("SELECT enabled "
		." FROM #__extensions "
		." WHERE element='t3' AND type='plugin' AND folder='system' "		
		);
		$rows = $database->loadObjectList();
		$plugin_enabled = 0;
		foreach($rows as $row){
			$plugin_enabled = $row->enabled;
		}		
		$templates_with_enabled_cache = array();
		if($plugin_enabled){
			jimport( 'joomla.filesystem.folder' );
			$templates = JFolder::folders(JPATH_ROOT.$ds.'templates');
			foreach($templates as $template){
				$file = JPATH_ROOT.$ds.'templates'.$ds.$template.$ds.'params.ini';	
				if ($fp = @fopen($file, "rb")){	
					$null = NULL;		
					$file_string = file_get_contents($file, $null, $null, 0, 300);				
					fclose ($fp);					
					if(strpos($file_string, 'cache="1"')){			
						$templates_with_enabled_cache[] = $template;					
					}			
				}
			}	
		}
		return $templates_with_enabled_cache;
	}
	
	function check_zen17($file){	
		$bad_code = 0;			
		if ($fp = @fopen($file, "rb")){	
			$null = NULL;		
			$file_string = file_get_contents($file, $null, $null, 10, 3000);				
			fclose ($fp);					
			if(!strpos($file_string, 'if(!in_array(\'ContentModelArticles\', get_declared_classes()))')){			
				$bad_code = 1;
			}			
		}		
		return $bad_code;
	}
	
	function check_aclmanager(){
	
		$db = JFactory::getDBO();
				
		//check if acl manager component is enabled
		$query = $db->getQuery(true);
		$query->select('enabled');
		$query->from('#__extensions');		
		$query->where('element='.$db->q('com_aclmanager'));		
		$query->where('type='.$db->q('component'));	
		$query->where('enabled='.$db->q('1'));
		$rows = $db->setQuery($query);				
		$rows = $db->loadObjectList();
		$component_enabled = 0;	
		foreach($rows as $row){		
			$component_enabled = 1;	
		}
		
		//check if acl manager system plugin is enabled
		$query = $db->getQuery(true);
		$query->select('enabled');
		$query->from('#__extensions');		
		$query->where('element='.$db->q('com_aclmanager'));		
		$query->where('type='.$db->q('plugin'));	
		$query->where('folder='.$db->q('system'));
		$query->where('enabled='.$db->q('1'));
		$rows = $db->setQuery($query);				
		$rows = $db->loadObjectList();
		$plugin_enabled = 0;	
		foreach($rows as $row){		
			$plugin_enabled = 1;	
		}
		
		$problem = 0;
		if(!$component_enabled && $plugin_enabled){
			$problem = 1;
		}	
		
		return $problem;
	}
	
	
	
}
?>