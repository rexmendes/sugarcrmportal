<?php /* Smarty version 2.6.11, created on 2014-11-27 11:26:41
         compiled from modules/ModuleBuilder/tpls/MBModule/Menu.tpl */ ?>
<?php echo '<?php'; ?>

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point'); 
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2011 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('<?php echo $this->_tpl_vars['moduleName']; ?>
', 'edit', true))$module_menu[]=Array("index.php?module=<?php echo $this->_tpl_vars['moduleName']; ?>
&action=EditView&return_module=<?php echo $this->_tpl_vars['moduleName']; ?>
&return_action=DetailView", $mod_strings['LNK_NEW_RECORD'],"Create<?php echo $this->_tpl_vars['moduleName']; ?>
", '<?php echo $this->_tpl_vars['moduleName']; ?>
');
<?php if ($this->_tpl_vars['showvCard']): ?>if(ACLController::checkAccess('<?php echo $this->_tpl_vars['moduleName']; ?>
', 'edit', true))$module_menu[]=Array("index.php?module=<?php echo $this->_tpl_vars['moduleName']; ?>
&action=ImportVCard", $mod_strings['LNK_IMPORT_VCARD'],"Create<?php echo $this->_tpl_vars['moduleName']; ?>
", '<?php echo $this->_tpl_vars['moduleName']; ?>
');<?php endif; ?>
if(ACLController::checkAccess('<?php echo $this->_tpl_vars['moduleName']; ?>
', 'list', true))$module_menu[]=Array("index.php?module=<?php echo $this->_tpl_vars['moduleName']; ?>
&action=index&return_module=<?php echo $this->_tpl_vars['moduleName']; ?>
&return_action=DetailView", $mod_strings['LNK_LIST'],"<?php echo $this->_tpl_vars['moduleName']; ?>
", '<?php echo $this->_tpl_vars['moduleName']; ?>
');
<?php if ($this->_tpl_vars['showimport']): ?>if(ACLController::checkAccess('<?php echo $this->_tpl_vars['moduleName']; ?>
', 'import', true))$module_menu[]=Array("index.php?module=Import&action=Step1&import_module=<?php echo $this->_tpl_vars['moduleName']; ?>
&return_module=<?php echo $this->_tpl_vars['moduleName']; ?>
&return_action=index", $app_strings['LBL_IMPORT'],"Import", '<?php echo $this->_tpl_vars['moduleName']; ?>
');<?php endif; ?>