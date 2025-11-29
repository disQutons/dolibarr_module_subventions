<?php
/* Copyright (C) 2004-2018	Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019	Nicolas ZABOURI				<info@inovea-conseil.com>
 * Copyright (C) 2019-2024	Frédéric France				<frederic.france@free.fr>
 * Copyright (C) 2025		François Brichart			<francois@disqutons.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   subventions     Module Subventions
 *  \brief      Subventions module descriptor.
 *
 *  \file       htdocs/subventions/core/modules/modSubventions.class.php
 *  \ingroup    subventions
 *  \brief      Description and activation file for module Subventions
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module Subventions
 */
class modSubventions extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs;

		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 173321; // Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'subventions';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "financial";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleSubventionsName' not found (Subventions is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// DESCRIPTION_FLAG
		// Module description, used if translation string 'ModuleSubventionsDesc' not found (Subventions is name of module).
		$this->description = "SubventionsDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "SubventionsDescription";

		// Author
		$this->editor_name = 'disQutons';
		$this->editor_url = 'https://www.disqutons.fr';		// Must be an external online web site
		$this->editor_squarred_logo = 'logo_disQutons.png@subventions';					// Must be image filename into the module/img directory followed with @modulename. Example: 'myimage.png@subventions'

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated', 'experimental_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where SUBVENTIONS is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'fa-hand-holding-heart';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 0,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 1,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/subventions/css/subventions.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/subventions/js/subventions.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			/* BEGIN MODULEBUILDER HOOKSCONTEXTS */
			'hooks' => array('data' => array('thirdpartycard','projectOverview',),'entity' => '0',),
			
									
			/*'hooks' => array(
				//   'data' => array(
				//       'hookcontext1',
				//       'hookcontext2',
				//   ),
				//   'entity' => '0',
			),*/

			/* END MODULEBUILDER HOOKSCONTEXTS */
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
			// Set this to 1 if the module provides a website template into doctemplates/websites/website_template-mytemplate
			'websitetemplates' => 0,
			// Set this to 1 if the module provides a captcha driver
			'captcha' => 0
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/subventions/temp","/subventions/subdir");
		$this->dirs = array("/subventions/temp");

		// Config pages. Put here list of php page, stored into subventions/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@subventions");

		// Dependencies
		// A condition to hide module
		$this->hidden = getDolGlobalInt('MODULE_SUBVENTIONS_DISABLED'); // A condition to disable module;
		// List of module class names that must be enabled if this module is enabled. Example: array('always'=>array('modModuleToEnable1','modModuleToEnable2'), 'FR'=>array('modModuleToEnableFR')...)
		$this->depends = array('modSociete');
		// List of module class names to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->requiredby = array();
		// List of module class names this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array();

		// The language file dedicated to your module
		$this->langfiles = array("subventions@subventions");

		// Prerequisites
		$this->phpmin = array(7, 1); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(19, -3); // Minimum version of Dolibarr required by module
		$this->need_javascript_ajax = 0;

		// Messages at activation
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		//$this->automatic_activation = array('FR'=>'SubventionsWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('SUBVENTIONS_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('SUBVENTIONS_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array(
			1 => array('SUBVENTIONS_PROJECT_MONTANT_HT', 'chaine', '$m_acc', 'Montant HT par défaut pour les projets', 1),
			2 => array('SUBVENTIONS_PROJECT_MONTANT_TTC', 'chaine', '$m_acc', 'Montant TTC par défaut pour les projets', 1),
			3 => array('SUBVENTIONS_STATISTIC_DATE', 'chaine', '$date_creation', 'Date par défaut pour les statistiques', 1),
			4 => array('SUBVENTIONS_STATISTIC_COLOR_GREEN', 'chaine', '75', 'Pourcentage minimum pour s\'afficher en vert', 1),
			5 => array('SUBVENTIONS_STATISTIC_COLOR_ORANGE', 'chaine', '50', 'Pourcentage minimum pour s\'afficher en orange', 1),
		);	

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isModEnabled("subventions")) {
			$conf->subventions = new stdClass();
			$conf->subventions->enabled = 0;
		}

		// Array to add new pages in new tabs
		/* BEGIN MODULEBUILDER TABS */
		//$this->tabs = array();
		//$this->tabs[] = array('data' => 'thirdparty:+tabsub:Subventions:subventions@subventions:1:/subventions/subvention_list.php?search_fk_soc=__ID__');
		
		// L'onglet est ajouté directement par un hook dans class/actions_subventions.class.php
		//$this->tabs[] = array('data' => 'thirdparty:+tabsub:'.$langs->trans('Subventions').':subventions@subventions:$user->hasRight("subventions", "subvention", "read"):/subventions/subvention_list.php?socid=__ID__&search_fk_soc=__ID__');

		// Example:
		// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data' => 'objecttype:+tabname1:Title1:mylangfile@subventions:$user->hasRight(\'subventions\', \'read\'):/subventions/mynewtab1.php?id=__ID__');
		//$this->tabs[] = array('data' => 'thirdparty:+tabsub:Subventions:subventions@subventions:$user->hasRight(\'subventions\', \'read\'):/subventions/subvention_list.php??search_fk_soc=__ID__');
			
		// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data' => 'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@subventions:$user->hasRight(\'othermodule\', \'read\'):/subventions/mynewtab2.php?id=__ID__',
		// To remove an existing tab identified by code tabname
		// $this->tabs[] = array('data' => 'objecttype:-tabname:NU:conditiontoremove');
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'delivery'         to add a tab in delivery view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in foundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in sale order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view

		/* END MODULEBUILDER TABS */

		// Dictionaries
		/* Example:
		 $this->dictionaries=array(
		 'langs' => 'subventions@subventions',
		 // List of tables we want to see into dictionary editor
		 'tabname' => array("table1", "table2", "table3"),
		 // Label of tables
		 'tablib' => array("Table1", "Table2", "Table3"),
		 // Request to select fields
		 'tabsql' => array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),
		 // Sort order
		 'tabsqlsort' => array("label ASC", "label ASC", "label ASC"),
		 // List of fields (result of select to show dictionary)
		 'tabfield' => array("code,label", "code,label", "code,label"),
		 // List of fields (list of fields to edit a record)
		 'tabfieldvalue' => array("code,label", "code,label", "code,label"),
		 // List of fields (list of fields for insert)
		 'tabfieldinsert' => array("code,label", "code,label", "code,label"),
		 // Name of columns with primary key (try to always name it 'rowid')
		 'tabrowid' => array("rowid", "rowid", "rowid"),
		 // Condition to show each dictionary
		 'tabcond' => array(isModEnabled('subventions'), isModEnabled('subventions'), isModEnabled('subventions')),
		 // Tooltip for every fields of dictionaries: DO NOT PUT AN EMPTY ARRAY
		 'tabhelp' => array(array('code' => $langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'), array('code' => $langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'), ...),
		 );
		 */
		/* BEGIN MODULEBUILDER DICTIONARIES */
		$this->dictionaries = array(
		 'langs' => 'subventions@subventions',
		 // List of tables we want to see into dictionary editor
		 'tabname' => array(MAIN_DB_PREFIX."c_subventions_financeur"),
		 // Label of tables
		 'tablib' => array("Subventions : Financeurs principaux et comptabilité"),
		 // Request to select fields
		 'tabsql' => array('SELECT f.rowid as rowid, f.ref, f.label, f.accountancy_code, f.active, f.position FROM '.MAIN_DB_PREFIX.'c_subventions_financeur as f'),
		 // Sort order
		 'tabsqlsort' => array("position ASC, rowid ASC"),
		 // List of fields (result of select to show dictionary)
		 'tabfield' => array("ref,label,accountancy_code,active,position"),
		 // List of fields (list of fields to edit a record)
		 'tabfieldvalue' => array("label,accountancy_code,active,position"),
		 // List of fields (list of fields for insert)
		 'tabfieldinsert' => array("ref,label,accountancy_code,active,position"),
		 // Name of columns with primary key (try to always name it 'rowid')
		 'tabrowid' => array("rowid"),
		 // Condition to show each dictionary
		 'tabcond' => array(isModEnabled('subventions')),
		 // Tooltip for every fields of dictionaries: DO NOT PUT AN EMPTY ARRAY
		 'tabhelp' => array(array('code' => $langs->trans('CodeTooltipHelp'),),),
		 );
		/* END MODULEBUILDER DICTIONARIES */

		// Boxes/Widgets
		// Add here list of php file(s) stored in subventions/core/boxes that contains a class to show a widget.
		/* BEGIN MODULEBUILDER WIDGETS */
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'subventionswidget1.php@subventions',
			//      'note' => 'Widget provided by Subventions',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);
		/* END MODULEBUILDER WIDGETS */

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		/* BEGIN MODULEBUILDER CRON */
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/subventions/class/subvention.class.php',
			//      'objectname' => 'Subvention',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => 'isModEnabled("subventions")',
			//      'priority' => 50,
			//  ),
		);
		/* END MODULEBUILDER CRON */
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'isModEnabled("subventions")', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'isModEnabled("subventions")', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 0 + 1);
		$this->rights[$r][1] = $langs->trans('RightsReadSubsidy');
		$this->rights[$r][4] = 'subvention';
		$this->rights[$r][5] = 'read';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 1 + 1);
		$this->rights[$r][1] = $langs->trans('RightsCreateUpdateSubsidy');
		$this->rights[$r][4] = 'subvention';
		$this->rights[$r][5] = 'write';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 2 + 1);
		$this->rights[$r][1] = $langs->trans('RightsDeleteSubsidy');
		$this->rights[$r][4] = 'subvention';
		$this->rights[$r][5] = 'import';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 3 + 1);
		$this->rights[$r][1] = $langs->trans('RightsImportSubsidyFundingAndPayment');
		$this->rights[$r][4] = 'subvention';
		$this->rights[$r][5] = 'delete';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 4 + 1);
		$this->rights[$r][1] = $langs->trans('RightsExportSubsidyFundingAndPayment');
		$this->rights[$r][4] = 'subvention';
		$this->rights[$r][5] = 'export';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (1 * 10) + 0 + 1);
		$this->rights[$r][1] = $langs->trans('RightsReadFunding');
		$this->rights[$r][4] = 'financement';
		$this->rights[$r][5] = 'read';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (1 * 10) + 1 + 1);
		$this->rights[$r][1] = $langs->trans('RightsCreateUpdateFunding');
		$this->rights[$r][4] = 'financement';
		$this->rights[$r][5] = 'write';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (1 * 10) + 2 + 1);
		$this->rights[$r][1] = $langs->trans('RightsDeleteFunding');
		$this->rights[$r][4] = 'financement';
		$this->rights[$r][5] = 'delete';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (2 * 10) + 0 + 1);
		$this->rights[$r][1] = $langs->trans('RightsReadPayment');
		$this->rights[$r][4] = 'paiement';
		$this->rights[$r][5] = 'read';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (2 * 10) + 1 + 1);
		$this->rights[$r][1] = $langs->trans('RightsCreateUpdatePayment');
		$this->rights[$r][4] = 'paiement';
		$this->rights[$r][5] = 'write';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (2 * 10) + 2 + 1);
		$this->rights[$r][1] = $langs->trans('RightsDeletePayment');
		$this->rights[$r][4] = 'paiement';
		$this->rights[$r][5] = 'delete';
		$r++;

		/* END MODULEBUILDER PERMISSIONS */


		// Main menu entries to add
		$this->menu = array();
		$r = 35;
		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
		$this->menu[$r++] = array(
			'fk_menu' => '', // Will be stored into mainmenu + leftmenu. Use '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'top', // This is a Top menu entry
			'titre' => 'ModuleSubventionsName',
			'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle"'),
			'mainmenu' => 'subventions',
			'leftmenu' => '',
			'url' => '/subventions/index.php',
			'langs' => 'subventions@subventions', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")', // Define condition to show or hide menu entry. Use 'isModEnabled("subventions")' if entry must be visible if module is enabled.
			'perms' => '$user->hasRight("subventions", "subvention", "read")', // Use 'perms'=>'$user->hasRight("subventions", "subvention", "read")' if you want your menu with a permission rules
			'target' => '',
			'user' => 2, // 0=Menu for internal users, 1=external users, 2=both
		);
		/* END MODULEBUILDER TOPMENU */

		/* BEGIN MODULEBUILDER LEFTMENU SUBVENTION */
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions',
			'type' => 'left',
			'titre' => 'Subvention',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu' => 'subventions',
			'leftmenu' => 'subvention',
			'url' => '/subventions/index.php',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")',
			'perms' => '$user->hasRight("subventions", "subvention", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Subvention'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subvention',
			'type' => 'left',
			'titre' => 'Nouvelle subvention',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subvention_new',
			'url' => '/subventions/subvention_card.php?action=create',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")',
			'perms' => '$user->hasRight("subventions", "subvention", "write")',
			'target' => '',
			'user' => 2,
			'object' => 'Subvention'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subvention',
			'type' => 'left',
			'titre' => 'Liste des subventions',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subvention_list',
			'url' => '/subventions/subvention_list.php',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")',
			'perms' => '$user->hasRight("subventions", "subvention", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Subvention'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subvention_list',
			'type' => 'left',
			'titre' => 'Non déposées',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subvention_draft',
			'url' => '/subventions/subvention_list.php?search_status=0',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions") && $leftmenu==\'subvention_list\'',
			'perms' => '$user->hasRight("subventions", "subvention", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Subvention'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subvention_list',
			'type' => 'left',
			'titre' => 'Attente de réponse',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subvention_validated',
			'url' => '/subventions/subvention_list.php?search_status=1',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions") && $leftmenu==\'subvention_list\'',
			'perms' => '$user->hasRight("subventions", "subvention", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Subvention'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subvention_list',
			'type' => 'left',
			'titre' => 'Attente de financement',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subvention_accepted',
			'url' => '/subventions/subvention_list.php?search_status=2',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions") && $leftmenu==\'subvention_list\'',
			'perms' => '$user->hasRight("subventions", "subvention", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Subvention'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subvention_list',
			'type' => 'left',
			'titre' => 'Bilan à déposer',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subvention_evaluated',
			'url' => '/subventions/subvention_list.php?search_status=3',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions") && $leftmenu==\'subvention_list\'',
			'perms' => '$user->hasRight("subventions", "subvention", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Subvention'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subvention_list',
			'type' => 'left',
			'titre' => 'Clôturé',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subvention_clotured',
			'url' => '/subventions/subvention_list.php?search_status=5',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions") && $leftmenu==\'subvention_list\'',
			'perms' => '$user->hasRight("subventions", "subvention", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Subvention'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subvention_list',
			'type' => 'left',
			'titre' => 'Refusé',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subvention_refused',
			'url' => '/subventions/subvention_list.php?search_status=6',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions") && $leftmenu==\'subvention_list\'',
			'perms' => '$user->hasRight("subventions", "subvention", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Subvention'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subvention',
			'type' => 'left',
			'titre' => 'Statistics',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subvention_statistics',
			'url' => '/subventions/stats/index.php',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")',
			'perms' => '$user->hasRight("subventions", "subvention", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Subvention'
		);
		/* END MODULEBUILDER LEFTMENU SUBVENTION */
		/* BEGIN MODULEBUILDER LEFTMENU FINANCEMENT */
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions',
			'type' => 'left',
			'titre' => 'Financements',
			'prefix' => img_picto('', 'fa-handshake', 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu' => 'subventions',
			'leftmenu' => 'financement',
			'url' => '/subventions/financement_list.php',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")',
			'perms' => '$user->hasRight("subventions", "financement", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Financement'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=financement',
			'type' => 'left',
			'titre' => 'Nouveau financement',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subventions_financement_new',
			'url' => '/subventions/financement_card.php?action=create',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")',
			'perms' => '$user->hasRight("subventions", "financement", "write")',
			'target' => '',
			'user' => 2,
			'object' => 'Financement'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=financement',
			'type' => 'left',
			'titre' => 'Liste des financements',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subventions_financement_list',
			'url' => '/subventions/financement_list.php',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")',
			'perms' => '$user->hasRight("subventions", "financement", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Financement'
		);
		/*$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subventions_financement_list',
			'type' => 'left',
			'titre' => 'Attente de réponse',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subventions_financement_validated',
			'url' => '/subventions/financement_list.php?search_montant_acc=0&search_montant_ref=0',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")',
			'perms' => '$user->hasRight("subventions", "financement", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Financement'
		);*/
		/* END MODULEBUILDER LEFTMENU FINANCEMENT */
		/* BEGIN MODULEBUILDER LEFTMENU PAIEMENT */
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions',
			'type' => 'left',
			'titre' => 'Paiements',
			'prefix' => img_picto('', 'fa-coins', 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu' => 'subventions',
			'leftmenu' => 'paiement',
			'url' => '/subventions/paiement_list.php',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")',
			'perms' => '$user->hasRight("subventions", "paiement", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Paiement'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=paiement',
			'type' => 'left',
			'titre' => 'Nouveau paiement',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subventions_paiement_new',
			'url' => '/subventions/paiement_card.php?action=create',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")',
			'perms' => '$user->hasRight("subventions", "paiement", "write")',
			'target' => '',
			'user' => 2,
			'object' => 'Paiement'
		);
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=paiement',
			'type' => 'left',
			'titre' => 'Liste des paiements',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subventions_paiement_list',
			'url' => '/subventions/paiement_list.php',
			'langs' => 'subventions@subventions',
			'position' => $r,
			'enabled' => 'isModEnabled("subventions")',
			'perms' => '$user->hasRight("subventions", "paiement", "read")',
			'target' => '',
			'user' => 2,
			'object' => 'Paiement'
		);
		/* END MODULEBUILDER LEFTMENU PAIEMENT */
		/* BEGIN MODULEBUILDER LEFTMENU MYOBJECT */
		/*
		$this->menu[$r++]=array(
			'fk_menu' => 'fk_mainmenu=subventions',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'left',                          // This is a Left menu entry
			'titre' => 'Subvention',
			'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle paddingright"'),
			'mainmenu' => 'subventions',
			'leftmenu' => 'subvention',
			'url' => '/subventions/subventionsindex.php',
			'langs' => 'subventions@subventions',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("subventions")', // Define condition to show or hide menu entry. Use 'isModEnabled("subventions")' if entry must be visible if module is enabled.
			'perms' => '$user->hasRight("subventions", "subvention", "read")',
			'target' => '',
			'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
			'object' => 'Subvention'
		);
		$this->menu[$r++]=array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subvention',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'left',			                // This is a Left menu entry
			'titre' => 'New_Subvention',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subventions_subvention_new',
			'url' => '/subventions/subvention_card.php?action=create',
			'langs' => 'subventions@subventions',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("subventions")', // Define condition to show or hide menu entry. Use 'isModEnabled("subventions")' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms' => '$user->hasRight("subventions", "subvention", "write")'
			'target' => '',
			'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
			'object' => 'Subvention'
		);
		$this->menu[$r++]=array(
			'fk_menu' => 'fk_mainmenu=subventions,fk_leftmenu=subvention',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'left',			                // This is a Left menu entry
			'titre' => 'List_Subvention',
			'mainmenu' => 'subventions',
			'leftmenu' => 'subventions_subvention_list',
			'url' => '/subventions/subvention_list.php',
			'langs' => 'subventions@subventions',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("subventions")', // Define condition to show or hide menu entry. Use 'isModEnabled("subventions")' if entry must be visible if module is enabled.
			'perms' => '$user->hasRight("subventions", "subvention", "read")'
			'target' => '',
			'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
			'object' => 'Subvention'
		);
		*/
		/* END MODULEBUILDER LEFTMENU MYOBJECT */


		// Exports profiles provided by this module
		$r = 0;
		/* BEGIN MODULEBUILDER EXPORT MYOBJECT */
		
		// Subvention
		$langs->load("subventions@subventions");
		$this->export_code[$r] = $this->rights_class.'_'.$r;
		$this->export_label[$r] = 'Subventions, financements et paiements';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r] = $this->picto;

		$this->export_permission[$r] = array(array('subventions', 'subvention', 'export'));
		$this->export_fields_array[$r] = array(
			// Subventions
			's.ref'=>"Ref", 's.label'=>"Nom du projet", 's.fk_soc'=>"ThirdParty", 's.status'=>"Status", 's.note_public'=>"NotePublic",
			's.note_private'=>"NotePrivate", 's.montant_dem'=>"Montant demandé", 's.montant_acc'=>"Montant accepté",
			's.montant_fin'=>"Montant financé", 's.montant_att'=>"Montant en attente", 's.montant_ref'=>"Montant refusé",
			'pr.ref'=>"project", 'pr.title'=>"project"." (libellé)", 's.description'=>"Description", 's.evaluation'=>"Critères d'évaluation",
			's.date_d_projet'=>"Date début projet", 's.date_f_projet'=>"Date fin projet", 's.date_attendue'=>"Date attendue projet",
			's.date_bilan'=>"Date rendu bilan",
			
			// Financements
			'f.fk_sub'=>"Réf Subvention", 'f.ref'=>"Réf Financement", 't.ref'=>"Type de financeur",'t.label'=>"Type de financeur (libellé)", 'f.fk_soc'=>"ThirdParty",
			'f.status'=>"Status", 'f.note_public'=>"NotePublic", 'f.note_private'=>"NotePrivate", 'f.montant_dem'=>"Montant demandé",
			'f.montant_acc'=>"Montant accepté", 'f.montant_fin'=>"Montant financé", 'f.montant_att'=>"Montant en attente",
			'f.montant_ref'=>"Montant refusé",
			
			// Paiements
			'p.fk_sub'=>"Réf Subvention", 'p.fk_fin'=>"Réf Financement", 'p.ref'=>"Réf Paiement", 'p.montant'=>"Montant",
			'p.fk_soc'=>"ThirdParty", 'p.status'=>"Status", 'p.note_public'=>"NotePublic", 'p.note_private'=>"NotePrivate",
			'p.datep'=>"Date paiement", 

		);
		$this->export_TypeFields_array[$r] = array(
			// Subventions
			's.ref'=>"Text", 's.label'=>"Text", 's.fk_soc'=>"FormSelect:select_company", 's.status'=>"Status", 's.note_public'=>"Text",
			's.note_private'=>"Text", 's.montant_dem'=>"Numeric", 's.montant_acc'=>"Numeric", 's.montant_fin'=>"Numeric",
			's.montant_att'=>"Numeric", 's.montant_ref'=>"Numeric", 'pr.ref'=>"Numeric", 'pr.title'=>"Text", 's.description'=>"Numeric",
			's.evaluation'=>"Text", 's.date_d_projet'=>"Date", 's.date_f_projet'=>"Date", 's.date_attendue'=>"Date", 's.date_bilan'=>"Date",
			
			// Financements
			'f.fk_sub'=>"Text", 'f.ref'=>"Text", 't.ref'=>"Text", 't.label'=>"Text", 'f.fk_soc'=>"FormSelect:select_company",
			'f.status'=>"Status", 'f.note_public'=>"Text", 'f.note_private'=>"Text", 'f.montant_dem'=>"Numeric", 'f.montant_acc'=>"Numeric",
			'f.montant_fin'=>"Numeric", 'f.montant_att'=>"Numeric", 'f.montant_ref'=>"Numeric",
			
			// Paiements
			'p.fk_sub'=>"Text", 'p.fk_fin'=>"Text", 'p.ref'=>"Text", 'p.montant'=>"Numeric", 'p.fk_soc'=>"FormSelect:select_company",
			'p.status'=>"Status", 'p.note_public'=>"Text", 'p.note_private'=>"Text", 'p.datep'=>"Date", 
		);

		// Ajout d'un tableau pour gérer correctement les icônes et les nom des objects utilisés
		$temp_array = [];
		foreach ($this->export_fields_array[$r] as $key => $value) {
			if (substr($key, 0, 2) == 's.') {
				$temp_array[$key] = "<i class='fas fa-hand-holding-heart'></i> Subvention";
			}
			elseif (substr($key, 0, 2) == 'f.') {
				$temp_array[$key] = "<i class='fas fa-handshake'></i> Financement";
			}
			elseif (substr($key, 0, 2) == 't.') {
				$temp_array[$key] = "<i class='fas fa-handshake'></i> Financement";
			}
			elseif (substr($key, 0, 2) == 'p.') {
				$temp_array[$key] = "<i class='fas fa-coins'></i> Paiement";
			}
		}

		// Fusion avec les autres champs
		$this->export_entities_array[$r] = array_merge(
			$temp_array,
			array(
				'pr.ref'=>"project", 'pr.title'=>"project",
			)
		);

		$this->export_dependencies_array[$r] = array('financement'=>'f.rowid', 'paiement'=>'p.rowid', 'project'=>'pr.rowid', 'financement'=>'t.rowid');

		$keyforselect='subvention'; $keyforaliasextra='extra'; $keyforelement='subvention@subventions';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		$keyforselect='financement'; $keyforaliasextra='extra'; $keyforelement='financement@subventions';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		$keyforselect='paiement'; $keyforaliasextra='extra'; $keyforelement='paiement@subventions';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';

		$this->export_sql_start[$r] = 'SELECT DISTINCT ';
		$this->export_sql_end[$r] = ' FROM '.MAIN_DB_PREFIX.'subventions_subvention as s';
		$this->export_sql_end[$r] .= ' LEFT JOIN '.MAIN_DB_PREFIX.'subventions_financement as f ON f.fk_sub = s.rowid';
		$this->export_sql_end[$r] .= ' LEFT JOIN '.MAIN_DB_PREFIX.'subventions_paiement as p ON p.fk_fin = f.rowid';
		$this->export_sql_end[$r] .= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet as pr ON pr.rowid = s.fk_project';
		$this->export_sql_end[$r] .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_subventions_financeur as t ON f.fk_financeur = t.rowid';
		$r++;
		/* END MODULEBUILDER EXPORT MYOBJECT */

		/* BEGIN MODULEBUILDER IMPORT MYOBJECT */
		// Imports profiles provided by this module
		$r = 0;

		// Import subventions
		$langs->load("subventions@subventions");
		$this->import_code[$r] = $this->rights_class.'_'.$r;
		$this->import_label[$r] = 'Subventions';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->import_icon[$r] = $this->picto;
		$this->import_permission[$r] = array(array('subventions', 'subvention', 'import'));

		$this->import_tables_array[$r] = array('s' => MAIN_DB_PREFIX.'subventions_subvention', 'extra' => MAIN_DB_PREFIX.'subventions_subvention_extrafields');
		$this->import_tables_creator_array[$r] = array('s' => 'fk_user_creat'); // Fields to store import user id
		$import_sample = array();
		
		$this->import_fields_array[$r] = array(
			's.ref'=>"Ref*", 's.label'=>"Nom du projet*", 's.fk_soc'=>"ThirdParty*", 's.status'=>"Status*", 's.note_public'=>"NotePublic",
			's.note_private'=>"NotePrivate", 's.montant_dem'=>"Montant demandé", 's.montant_acc'=>"Montant accepté",
			's.montant_fin'=>"Montant financé", 's.montant_att'=>"Montant en attente", 's.montant_ref'=>"Montant refusé",
			's.fk_project'=>"project", 's.description'=>"Description", 's.evaluation'=>"Critères d'évaluation",
			's.date_d_projet'=>"Date début projet", 's.date_f_projet'=>"Date fin projet", 's.date_attendue'=>"Date attendue projet",
			's.date_bilan'=>"Date rendu bilan", 's.date_creation'=>"Date création*"
		);
		
		$import_extrafield_sample = array();
		$keyforselect='subvention'; $keyforaliasextra='extra'; $keyforelement='subvention@subventions';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinimport.inc.php';

		$this->import_regex_array[$r] = array(
			's.status'=>'^[0|1|2|3|4|5|6|9]$',
			's.date_d_projet'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]( [0-9][0-9]:[0-9][0-9]:[0-9][0-9])?$',
			's.date_f_projet'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]( [0-9][0-9]:[0-9][0-9]:[0-9][0-9])?$',
			's.date_attendue'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]( [0-9][0-9]:[0-9][0-9]:[0-9][0-9])?$',
			's.date_bilan'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]( [0-9][0-9]:[0-9][0-9]:[0-9][0-9])?$',
			's.date_creation'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]( [0-9][0-9]:[0-9][0-9]:[0-9][0-9])?$',
			's.montant_dem'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			's.montant_acc'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			's.montant_fin'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			's.montant_att'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			's.montant_ref'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			's.fk_soc'=>'^[0-9]+$',		
		);
		
		$this->import_examplevalues_array[$r] = array(
			's.ref'=>"SUB202501-001", 's.label'=>"Subvention de fonctionnement", 's.fk_soc'=>"123", 's.status'=>"1",
			's.note_public'=>"Note :", 's.note_private'=>"Note :", 's.montant_dem'=>"10000", 's.montant_acc'=>"70000",
			's.montant_fin'=>"2000", 's.montant_att'=>"5000", 's.montant_ref'=>"3000", 's.fk_project'=>"357",
			's.description'=>"Main goals", 's.evaluation'=>"evaluation", 's.date_d_projet'=>"2025-01-01", 's.date_f_projet'=>"2025-12-31",
			's.date_attendue'=>"2024-09-15", 's.date_bilan'=>"2026-06-30", 's.date_creation'=>"2026-06-30",
		);
		$this->import_examplevalues_array[$r] = array_merge($import_sample, $import_extrafield_sample);

		$this->import_updatekeys_array[$r] = array('s.ref'=>'Ref');

		$this->import_convertvalue_array[$r] = array(
			's.ref' => array(
				'rule'=>'getrefifauto',
				'class'=>(!getDolGlobalString('SUBVENTIONS_SUBVENTION_ADDON') ? 'mod_subvention_standard' : getDolGlobalString('SUBVENTIONS_SUBVENTION_ADDON')),
				'path'=>"/core/modules/subventions/".(!getDolGlobalString('SUBVENTIONS_SUBVENTION_ADDON') ? 'mod_subvention_standard' : getDolGlobalString('SUBVENTIONS_SUBVENTION_ADDON')).'.php',
				'classobject'=>'Subvention',
				'pathobject'=>'/subventions/class/subvention.class.php',
			),
			's.fk_soc' => array('rule' => 'fetchidfromref', 'file' => '/societe/class/societe.class.php', 'class' => 'Societe', 'method' => 'fetch', 'element' => 'ThirdParty'),
			's.fk_project' => array('rule' => 'fetchidfromref', 'file' => '/projet/class/projet.class.php', 'class' => 'Projet', 'method' => 'fetch', 'element' => 'Project'),
		);

		$this->import_run_sql_after_array[$r] = array();
		$r++;
		

		// Import financements
		$this->import_code[$r] = $this->rights_class.'_'.$r;
		$this->import_label[$r] = 'Financements';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->import_icon[$r] = 'fa-handshake';
		$this->import_permission[$r] = array(array('subventions', 'subvention', 'import'));

		$this->import_tables_array[$r] = array('f' => MAIN_DB_PREFIX.'subventions_financement', 'extra' => MAIN_DB_PREFIX.'subventions_financement_extrafields');
		$this->import_tables_creator_array[$r] = array('f' => 'fk_user_creat'); // Fields to store import user id
		$import_sample = array();
		
		$this->import_fields_array[$r] = array(
			'f.ref'=>"Ref*", 'f.fk_financeur'=>"Type de financeur*", 'f.fk_soc'=>"ThirdParty*", 'f.status'=>"Status*", 'f.note_public'=>"NotePublic",
			'f.note_private'=>"NotePrivate", 'f.montant_dem'=>"Montant demandé", 'f.montant_acc'=>"Montant accepté", 'f.montant_fin'=>"Montant financé",
			'f.montant_att'=>"Montant en attente", 'f.montant_ref'=>"Montant refusé",'f.fk_sub'=>"ID Subvention",
		);
		
		$import_extrafield_sample = array();
		$keyforselect='financement'; $keyforaliasextra='extra'; $keyforelement='subvention@subventions';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinimport.inc.php';

		$this->import_regex_array[$r] = array(
			'f.status'=>'^[0|1|9]$',
			'f.montant_dem'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			'f.montant_acc'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			'f.montant_fin'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			'f.montant_att'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			'f.montant_ref'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			'f.fk_soc'=>'^[0-9]+$',		
		);
		
		$this->import_examplevalues_array[$r] = array(
			'f.ref'=>"FIN202501-001", 'f.fk_financeur'=>"FS-SOC", 'f.fk_soc'=>"123", 'f.status'=>"1", 'f.note_public'=>"Note :",
			'f.note_private'=>"Note :", 'f.montant_dem'=>"10000", 'f.montant_acc'=>"70000", 'f.montant_fin'=>"2000", 'f.montant_att'=>"5000",
			'f.montant_ref'=>"3000",'f.fk_sub'=>"987",
		);
		$this->import_examplevalues_array[$r] = array_merge($import_sample, $import_extrafield_sample);

		$this->import_updatekeys_array[$r] = array('f.ref'=>'Ref');

		$this->import_convertvalue_array[$r] = array(
			'f.ref' => array(
				'rule'=>'getrefifauto',
				'class'=>(!getDolGlobalString('SUBVENTIONS_FINANCEMENT_ADDON') ? 'mod_subvention_standard' : getDolGlobalString('SUBVENTIONS_FINANCEMENT_ADDON')),
				'path'=>"/core/modules/subventions/".(!getDolGlobalString('SUBVENTIONS_FINANCEMENT_ADDON') ? 'mod_subvention_standard' : getDolGlobalString('SUBVENTIONS_FINANCEMENT_ADDON')).'.php',
				'classobject'=>'Financement',
				'pathobject'=>'/subventions/class/financement.class.php',
			),
			's.fk_soc' => array('rule' => 'fetchidfromref', 'file' => '/societe/class/societe.class.php', 'class' => 'Societe', 'method' => 'fetch', 'element' => 'ThirdParty'),
		);

		$this->import_run_sql_after_array[$r] = array();
		$r++;

		// Import paiements
		$this->import_code[$r] = $this->rights_class.'_'.$r;
		$this->import_label[$r] = 'Paiements';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->import_icon[$r] = 'fa-coins';
		$this->import_permission[$r] = array(array('subventions', 'subvention', 'import'));

		$this->import_tables_array[$r] = array('p' => MAIN_DB_PREFIX.'subventions_paiement', 'extra' => MAIN_DB_PREFIX.'subventions_paiement_extrafields');
		$this->import_tables_creator_array[$r] = array('p' => 'fk_user_creat'); // Fields to store import user id
		$import_sample = array();
		
		$this->import_fields_array[$r] = array(
			'p.ref'=>"Ref*", 'p.montant'=>"Montant*", 'p.fk_soc'=>"ThirdParty", 'p.status'=>"Status", 'p.note_public'=>"NotePublic",
			'p.note_private'=>"NotePrivate", 'p.datep'=>"Date paiement*", 'p.fk_sub'=>"ID Subvention", 'p.fk_fin'=>"ID Financement",
		);
		
		$import_extrafield_sample = array();
		$keyforselect='paiement'; $keyforaliasextra='extra'; $keyforelement='subvention@subventions';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinimport.inc.php';

		$this->import_regex_array[$r] = array(
			'p.montant'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			'p.status'=>'^[0|1|9]$',
			'p.datep'=>'^[0-9]+(\\.[0-9]{1,2})?$',
			'p.fk_soc'=>'^[0-9]+$',		
		);
		
		$this->import_examplevalues_array[$r] = array(
			'p.ref'=>"PAI202508-003", 'p.montant'=>"2000", 'p.fk_soc'=>"123", 'p.status'=>"1", 'p.note_public'=>"Note :",
			'p.note_private'=>"Note :", 'p.datep'=>"45900", 'p.fk_sub'=>"456", 'p.fk_fin'=>"789",
		);
		$this->import_examplevalues_array[$r] = array_merge($import_sample, $import_extrafield_sample);

		$this->import_updatekeys_array[$r] = array('f.ref'=>'Ref');

		$this->import_convertvalue_array[$r] = array(
			'f.ref' => array(
				'rule'=>'getrefifauto',
				'class'=>(!getDolGlobalString('SUBVENTIONS_SUBVENTION_ADDON') ? 'mod_subvention_standard' : getDolGlobalString('SUBVENTIONS_SUBVENTION_ADDON')),
				'path'=>"/core/modules/subventions/".(!getDolGlobalString('SUBVENTIONS_SUBVENTION_ADDON') ? 'mod_subvention_standard' : getDolGlobalString('SUBVENTIONS_SUBVENTION_ADDON')).'.php',
				'classobject'=>'Paiement',
				'pathobject'=>'/subventions/class/paiement.class.php',
			),
			'p.fk_soc' => array('rule' => 'fetchidfromref', 'file' => '/societe/class/societe.class.php', 'class' => 'Societe', 'method' => 'fetch', 'element' => 'ThirdParty'),
		);

		$this->import_run_sql_after_array[$r] = array();
		$r++;

		/* END MODULEBUILDER IMPORT MYOBJECT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int<-1,1>          	1 if OK, <=0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		// Create tables of module at module activation
		//$result = $this->_load_tables('/install/mysql/', 'subventions');
		$result = $this->_load_tables('/subventions/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

		// Create extrafields during init
		//include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		//$extrafields = new ExtraFields($this->db);
		//$result0=$extrafields->addExtraField('subventions_separator1', "Separator 1", 'separator', 1,  0, 'thirdparty',   0, 0, '', array('options'=>array(1=>1)), 1, '', 1, 0, '', '', 'subventions@subventions', 'isModEnabled("subventions")');
		//$result1=$extrafields->addExtraField('subventions_myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', -1, 0, '', '', 'subventions@subventions', 'isModEnabled("subventions")');
		//$result2=$extrafields->addExtraField('subventions_myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', -1, 0, '', '', 'subventions@subventions', 'isModEnabled("subventions")');
		//$result3=$extrafields->addExtraField('subventions_myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', -1, 0, '', '', 'subventions@subventions', 'isModEnabled("subventions")');
		//$result4=$extrafields->addExtraField('subventions_myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', -1, 0, '', '', 'subventions@subventions', 'isModEnabled("subventions")');
		//$result5=$extrafields->addExtraField('subventions_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', -1, 0, '', '', 'subventions@subventions', 'isModEnabled("subventions")');

		// Permissions
		$this->remove($options);

		$sql = array();

		// Document templates
		$moduledir = dol_sanitizeFileName('subventions');
		$myTmpObjects = array();
		$myTmpObjects['Subvention'] = array('includerefgeneration' => 0, 'includedocgeneration' => 0);

		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			if ($myTmpObjectArray['includerefgeneration']) {
				$src = DOL_DOCUMENT_ROOT.'/install/doctemplates/'.$moduledir.'/template_subventions.odt';
				$dirodt = DOL_DATA_ROOT.($conf->entity > 1 ? '/'.$conf->entity : '').'/doctemplates/'.$moduledir;
				$dest = $dirodt.'/template_subventions.odt';

				if (file_exists($src) && !file_exists($dest)) {
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result = dol_copy($src, $dest, '0', 0);
					if ($result < 0) {
						$langs->load("errors");
						$this->error = $langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")",
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")"
				));
			}
		}

		return $this->_init($sql, $options);
	}

	/**
	 *	Function called when module is disabled.
	 *	Remove from database constants, boxes and permissions from Dolibarr database.
	 *	Data directories are not deleted
	 *
	 *	@param	string		$options	Options when enabling module ('', 'noboxes')
	 *	@return	int<-1,1>				1 if OK, <=0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
