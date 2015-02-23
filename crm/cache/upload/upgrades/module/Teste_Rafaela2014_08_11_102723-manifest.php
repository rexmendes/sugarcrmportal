    <?php
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


    $manifest = array (
         'acceptable_sugar_versions' => 
          array (
            
          ),
          'acceptable_sugar_flavors' =>
          array(
            'CE', 'PRO','ENT'
          ),
          'readme'=>'',
          'key'=>'RR',
          'author' => 'Rafaela Raganham',
          'description' => 'sdfsdfsdfsdfsdfsdfsdfsd',
          'icon' => '',
          'is_uninstallable' => true,
          'name' => 'Teste_Rafaela',
          'published_date' => '2014-08-11 13:27:23',
          'type' => 'module',
          'version' => '1407763643',
          'remove_tables' => 'prompt',
          );
$installdefs = array (
  'id' => 'Teste_Rafaela',
  'beans' => 
  array (
    0 => 
    array (
      'module' => 'RR_Teste_Rafaela',
      'class' => 'RR_Teste_Rafaela',
      'path' => 'modules/RR_Teste_Rafaela/RR_Teste_Rafaela.php',
      'tab' => true,
    ),
  ),
  'layoutdefs' => 
  array (
    0 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/layoutdefs/rr_teste_rafaela_contacts_Contacts.php',
      'to_module' => 'Contacts',
    ),
    1 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/layoutdefs/rr_teste_rafaela_accounts_Accounts.php',
      'to_module' => 'Accounts',
    ),
  ),
  'relationships' => 
  array (
    0 => 
    array (
      'meta_data' => '<basepath>/SugarModules/relationships/relationships/rr_teste_rafaela_contactsMetaData.php',
    ),
    1 => 
    array (
      'meta_data' => '<basepath>/SugarModules/relationships/relationships/rr_teste_rafaela_accountsMetaData.php',
    ),
  ),
  'image_dir' => '<basepath>/icons',
  'copy' => 
  array (
    0 => 
    array (
      'from' => '<basepath>/SugarModules/modules/RR_Teste_Rafaela',
      'to' => 'modules/RR_Teste_Rafaela',
    ),
  ),
  'language' => 
  array (
    0 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/RR_Teste_Rafaela.php',
      'to_module' => 'RR_Teste_Rafaela',
      'language' => 'en_us',
    ),
    1 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/RR_Teste_Rafaela.php',
      'to_module' => 'RR_Teste_Rafaela',
      'language' => 'pt_BR',
    ),
    2 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Contacts.php',
      'to_module' => 'Contacts',
      'language' => 'en_us',
    ),
    3 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Contacts.php',
      'to_module' => 'Contacts',
      'language' => 'pt_BR',
    ),
    4 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/RR_Teste_Rafaela.php',
      'to_module' => 'RR_Teste_Rafaela',
      'language' => 'en_us',
    ),
    5 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/RR_Teste_Rafaela.php',
      'to_module' => 'RR_Teste_Rafaela',
      'language' => 'pt_BR',
    ),
    6 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Accounts.php',
      'to_module' => 'Accounts',
      'language' => 'en_us',
    ),
    7 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/language/Accounts.php',
      'to_module' => 'Accounts',
      'language' => 'pt_BR',
    ),
    8 => 
    array (
      'from' => '<basepath>/SugarModules/language/application/en_us.lang.php',
      'to_module' => 'application',
      'language' => 'en_us',
    ),
    9 => 
    array (
      'from' => '<basepath>/SugarModules/language/application/pt_BR.lang.php',
      'to_module' => 'application',
      'language' => 'pt_BR',
    ),
  ),
  'vardefs' => 
  array (
    0 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/vardefs/rr_teste_rafaela_contacts_RR_Teste_Rafaela.php',
      'to_module' => 'RR_Teste_Rafaela',
    ),
    1 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/vardefs/rr_teste_rafaela_contacts_Contacts.php',
      'to_module' => 'Contacts',
    ),
    2 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/vardefs/rr_teste_rafaela_accounts_RR_Teste_Rafaela.php',
      'to_module' => 'RR_Teste_Rafaela',
    ),
    3 => 
    array (
      'from' => '<basepath>/SugarModules/relationships/vardefs/rr_teste_rafaela_accounts_Accounts.php',
      'to_module' => 'Accounts',
    ),
  ),
  'layoutfields' => 
  array (
    0 => 
    array (
      'additional_fields' => 
      array (
      ),
    ),
    1 => 
    array (
      'additional_fields' => 
      array (
      ),
    ),
  ),
);