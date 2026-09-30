<?php
/* Copyright (C) 2017       Laurent Destailleur      <eldy@users.sourceforge.net>
 * Copyright (C) 2023-2024  Frédéric France          <frederic.france@free.fr>
 * Copyright (C) 2025		François Brichart			<francois@disqutons.fr>
 * Copyright (C) 2026		Romain MP		<romain.mp@gmail.com>
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
 * \file        class/paiement.class.php
 * \ingroup     subventions
 * \brief       This file is a CRUD class file for Paiement (Create/Read/Update/Delete)
 */

/*
//FBR récupération des erreurs php
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
dol_include_once('/custom/subventions/lib/subventions.lib.php');

if (!class_exists('Paiement')) {
/**
 * Class for Paiement
 */
class Paiement extends CommonObject
{
	/**
	 * @var string 	ID of module.
	 */
	public $module = 'subventions';

	/**
	 * @var string 	ID to identify managed object.
	 */
	public $element = 'paiement';

	/**
	 * @var string 	Name of table without prefix where object is stored. This is also the key used for extrafields management (so extrafields know the link to the parent table).
	 */
	public $table_element = 'subventions_paiement';

	/**
	 * @var string 	If permission must be checkec with hasRight('subventions', 'read') and not hasright('mymodyle', 'paiement', 'read'), you can uncomment this line
	 */
	//public $element_for_permission = 'subventions';

	/**
	 * @var string 	String with name of icon for paiement. Must be a 'fa-xxx' fontawesome code (or 'fa-xxx_fa_color_size') or 'paiement@subventions' if picto is file 'img/object_paiement.png'.
	 */
	public $picto = 'fa-coins';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;

	/**
	 *  'type' field format:
	 *  	'integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]',
	 *  	'select' (list of values are in 'options'. for integer list of values are in 'arrayofkeyval'),
	 *  	'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:CategoryIdType[:CategoryIdList[:SortField]]]]]]',
	 *  	'chkbxlst:...',
	 *  	'varchar(x)',
	 *  	'text', 'text:none', 'html',
	 *   	'double(24,8)', 'real', 'price', 'stock',
	 *  	'date', 'datetime', 'timestamp', 'duration',
	 *  	'boolean', 'checkbox', 'radio', 'array',
	 *  	'mail', 'phone', 'url', 'password', 'ip'
	 *		Note: Filter must be a Dolibarr Universal Filter syntax string. Example: "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.status:!=:0) or (t.nature:is:NULL)"
	 *  'length' the length of field. Example: 255, '24,8'
	 *  'label' the translation key.
	 *  'langfile' the key of the language file for translation.
	 *  'alias' the alias used into some old hard coded SQL requests
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or 'getDolGlobalInt("MY_SETUP_PARAM")' or 'isModEnabled("multicurrency")' ...)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form (not create). 5=Visible on list and view form (not create/not update). 6=visible on list and update/view form (not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'alwayseditable' says if field can be modified also when status is not draft ('1' or '0')
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommended to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	 *  'placeholder' to set the placeholder of a varchar field.
	 *  'help' and 'helplist' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code like the constructor of the class.
	 *  'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel"). Note that type can be 'integer' or 'varchar'
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *	'validate' is 1 if you need to validate the field with $this->validateField(). Need MAIN_ACTIVATE_VALIDATION_RESULT.
	 *  'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1=picto after label, 2=picto after value)
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @inheritdoc
	 * Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields = array(
		"rowid" => array("type" => "integer", "label" => "TechnicalID", "enabled" => "1", 'position' => 1, 'notnull' => 1, "visible" => "0", "noteditable" => "1", "index" => "1", "css" => "left", "comment" => "Id"),
		"ref" => array("type" => "varchar(128)", "label" => "Ref", "enabled" => "1", 'position' => 20, 'notnull' => 1, "visible" => "4", "noteditable" => "1", "default" => "(PROV)", "index" => "1", "searchall" => "1", "showoncombobox" => "1", "validate" => "1", "comment" => "Reference of object"),
		"montant" => array("type" => "price", "label" => "Montant", "enabled" => "1", 'position' => 40, 'notnull' => 1, "visible" => "1", "isameasure" => "1", "help" => "Help text for amount", "validate" => "1",),
		"datep" => array("type" => "date", "label" => "Date paiement", "enabled" => "1", 'position' => 40, 'notnull' => 1, "visible" => "1", "help" => "Help text for amount", "validate" => "1",),
		"fk_soc" => array("type" => "integer:Societe:societe/class/societe.class.php:1:((status:=:1) AND (entity:IN:__SHARED_ENTITIES__))", "label" => "ThirdParty", "picto" => "company", "enabled" => "isModEnabled('societe')", 'position' => 50, 'notnull' => -1, "visible" => "1", "index" => "1", "css" => "maxwidth500 widthcentpercentminusxx", "csslist" => "tdoverflowmax150", "help" => "OrganizationEventLinkToThirdParty", "validate" => "1",),
		"note_public" => array("type" => "html", "label" => "NotePublic", "enabled" => "1", 'position' => 61, 'notnull' => 0, "visible" => "0", "cssview" => "wordbreak", "validate" => "1",),
		"note_private" => array("type" => "html", "label" => "NotePrivate", "enabled" => "1", 'position' => 62, 'notnull' => 0, "visible" => "0", "cssview" => "wordbreak", "validate" => "1",),
		"date_creation" => array("type" => "datetime", "label" => "DateCreation", "enabled" => "1", 'position' => 500, 'notnull' => 1, "visible" => "-2",),
		"tms" => array("type" => "timestamp", "label" => "DateModification", "enabled" => "1", 'position' => 501, 'notnull' => 0, "visible" => "-2",),
		"fk_user_creat" => array("type" => "integer:User:user/class/user.class.php", "label" => "UserAuthor", "picto" => "user", "enabled" => "1", 'position' => 510, 'notnull' => 1, "visible" => "-2", "csslist" => "tdoverflowmax150",),
		"fk_user_modif" => array("type" => "integer:User:user/class/user.class.php", "label" => "UserModif", "picto" => "user", "enabled" => "1", 'position' => 511, 'notnull' => -1, "visible" => "-2", "csslist" => "tdoverflowmax150",),
		"last_main_doc" => array("type" => "varchar(255)", "label" => "LastMainDoc", "enabled" => "1", 'position' => 600, 'notnull' => 0, "visible" => "0",),
		"import_key" => array("type" => "varchar(14)", "label" => "ImportId", "enabled" => "1", 'position' => 1000, 'notnull' => -1, "visible" => "-2",),
		"fk_sub" => array("type" => "integer:subvention:/custom/subventions/class/subvention.class.php", "label" => "Réf subvention", "enabled" => "1", 'position' => 25, 'notnull' => 0, "visible" => "1",),
		"fk_fin" => array("type" => "integer:financement:/custom/subventions/class/financement.class.php", "label" => "Réf financement", "enabled" => "1", 'position' => 30, 'notnull' => 0, "visible" => "1",),
		"fk_bank" => array("type" => "integer", "label" => "BankTransaction", "enabled" => "isModEnabled('banque')", 'position' => 52, 'notnull' => 0, "visible" => "0",),
		"fk_account" => array("type" => "integer:Account:compta/bank/class/account.class.php:1:(t.clos:=:0)", "label" => "BankAccount", "picto" => "bank_account", "enabled" => "isModEnabled('banque')", 'position' => 53, 'notnull' => 0, "visible" => "1",),
		"fk_paiement" => array("type" => "sellist:c_paiement:libelle:id::active=1", "label" => "PaymentMode", "enabled" => "1", 'position' => 54, 'notnull' => 0, "visible" => "1",),
		"num_paiement" => array("type" => "varchar(50)", "label" => "NumPayment", "enabled" => "1", 'position' => 55, 'notnull' => 0, "visible" => "1",),
		"accounted" => array("type" => "integer", "label" => "Accounted", "enabled" => "(isModEnabled('accounting') || isModEnabled('accountancy'))", 'position' => 60, 'notnull' => 0, "visible" => "1", "default" => "0", "csslist" => "center", "arrayofkeyval" => array("0" => "No", "1" => "Yes"),),
		"date_engagement" => array("type" => "date", "label" => "EngagementDate", "enabled" => "(isModEnabled('accounting') || isModEnabled('accountancy'))", 'position' => 61, 'notnull' => 0, "visible" => "0",),
		"fk_bookkeeping_bank" => array("type" => "integer", "label" => "BookkeepingBank", "enabled" => "(isModEnabled('accounting') || isModEnabled('accountancy'))", 'position' => 62, 'notnull' => 0, "visible" => "0",),
		"fk_bookkeeping_receivable" => array("type" => "integer", "label" => "BookkeepingReceivable", "enabled" => "(isModEnabled('accounting') || isModEnabled('accountancy'))", 'position' => 63, 'notnull' => 0, "visible" => "0",),
		"status" => array("type" => "integer", "label" => "Status", "enabled" => "1", 'position' => 2000, 'notnull' => 1, "visible" => "0", "noteditable" => "1", "default" => "1", "index" => "1", "arrayofkeyval" => array("0" => "Brouillon", "1" => "Valid&eacute;", "9" => "Annul&eacute;"), "validate" => "1",),
		"entity" => array('type' => 'integer', 'label' => 'Entity', 'default' => '1', 'enabled' => 1, 'visible' => -2, 'notnull' => 1, 'position' => 15, 'index' => 1),
	);
	public $rowid;
	public $ref;
	public $montant;
	public $datep;
	public $fk_soc;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $last_main_doc;
	public $import_key;
	public $fk_sub;
	public $fk_fin;
	public $fk_bank;
	public $fk_account;
	public $fk_paiement;
	public $num_paiement;
	public $accounted = 0;
	public $date_engagement;
	public $fk_bookkeeping_bank;
	public $fk_bookkeeping_receivable;
	public $sub_label;
	public $status;
	public $entity;
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	// public $table_element_line = 'subventions_paiementline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_paiement';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'Paiementline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array('mychildtable' => array('name'=>'Paiement', 'fk_element'=>'fk_paiement'));

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('subventions_paiementdet');

	// /**
	//  * @var PaiementLine[]     Array of subtable lines
	//  */
	// public $lines = array();



	/**
	 * Constructor
	 *
	 * @param	DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $langs;

		$this->db = $db;
		$this->ismultientitymanaged = 0;
		$this->isextrafieldmanaged = 1;

		if (!getDolGlobalInt('MAIN_SHOW_TECHNICAL_ID') && isset($this->fields['rowid']) && !empty($this->fields['ref'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param	User		$user		User that creates
	 * @param	int<0,1> 	$notrigger	0=launch triggers after, 1=disable triggers
	 * @return	int<-1,max>				Return integer <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = 0)
	{
		$resultcreate = $this->createCommon($user, $notrigger);

		if ($resultcreate > 0) {
			// fetch to retrieve some fields (ie date_creation for masked ref)
			$this->fetch($this->id);
			$resultvalidate = $this->validate($user, $notrigger);

			// Mise à jour des montants des financements liés
			$resultmaj = majMontantsFinancementSubvention($this);

			// Automatically create bank transaction if bank account was selected
			if (!empty($this->fk_account) && $this->fk_account > 0 && empty($this->fk_bank) && isModEnabled('banque')) {
				$this->addPaymentToBank($user, $this->fk_account, $this->fk_paiement, '', $this->num_paiement);
			}
		}

		return $resultcreate;
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param	int    		$id   			Id object
	 * @param	string 		$ref  			Ref
	 * @param	int<0,1>	$noextrafields	0=Default to load extrafields, 1=No extrafields
	 * @param	int<0,1>	$nolines		0=Default to load extrafields, 1=No extrafields
	 * @return	int<-1,1>					Return integer <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null, $noextrafields = 0, $nolines = 0)
	{
		$result = $this->fetchCommon($id, $ref, '', $noextrafields);
		if ($result > 0 && !empty($this->table_element_line) && empty($nolines)) {
			$this->fetchLines($noextrafields);
		}
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @param	int<0,1>	$noextrafields	0=Default to load extrafields, 1=No extrafields
	 * @return 	int<-1,1>					Return integer <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines($noextrafields = 0)
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon('', $noextrafields);
		return $result;
	}


	/**
	 * Load list of objects in memory from the database.
	 * Using a fetchAll() with limit = 0 is a very bad practice. Instead try to forge yourself an optimized SQL request with
	 * your own loop with start and stop pagination.
	 *
	 * @param	string		$sortorder	Sort Order
	 * @param	string		$sortfield	Sort field
	 * @param	int<0,max>	$limit		Limit the number of lines returned
	 * @param	int<0,max>	$offset		Offset
	 * @param	string		$filter		Filter as an Universal Search string.
	 *                                  Example: '((client:=:1) OR ((client:>=:2) AND (client:<=:3))) AND (client:!=:8) AND (nom:like:'a%')'
	 * @param	string		$filtermode	No longer used
	 * @return	array<int,self>|int<-1,-1>	 <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 1000, $offset = 0, string $filter = '', $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".$this->db->prefix().$this->table_element." as t";
		if (isset($this->isextrafieldmanaged) && $this->isextrafieldmanaged == 1) {
			$sql .= " LEFT JOIN ".$this->db->prefix().$this->table_element."_extrafields as te ON te.fk_object = t.rowid";
		}
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= " WHERE t.entity IN (".getEntity($this->element).")";
		} else {
			$sql .= " WHERE 1 = 1";
		}

		// Manage filter
		$errormessage = '';
		$sql .= forgeSQLFromUniversalSearchCriteria($filter, $errormessage);
		if ($errormessage) {
			$this->errors[] = $errormessage;
			dol_syslog(__METHOD__.' '.implode(',', $this->errors), LOG_ERR);
			return -1;
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				if (!empty($record->isextrafieldmanaged)) {
					$record->fetch_optionals();
				}

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.implode(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param	User		$user		User that modifies
	 * @param	int<0,1>	$notrigger	0=launch triggers after, 1=disable triggers
	 * @return	int<-1,1>				Return integer <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = 0)
	{
		$result = $this->updateCommon($user, $notrigger);
		
		// mise à jour des montants des financements liés
		$resultmaj = majMontantsFinancementSubvention($this);

		return $result;
	}

	/**
	 * Delete object in database
	 *
	 * @param	User		$user		User that deletes
	 * @param	int<0,1> 	$notrigger	0=launch triggers, 1=disable triggers
	 * @return	int<-1,1>				Return integer <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = 0)
	{
		// If accounted in ledger, unbookkeep first
		if (!empty($this->accounted)) {
			$this->unbookkeep($user);
		}

		$result = $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);

		if ($result > 0 && !empty($this->fk_bank) && isModEnabled('banque')) {
			require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
			$acc = new Account($this->db);
			$acc->delete_line($user, $this->fk_bank);
		}

		// mise à jour des montants des financements liés
		$resultmaj = majMontantsFinancementSubvention($this);

		return $result;
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param	User		$user		User that delete
	 *  @param	int			$idline		Id of line to delete
	 *  @param	int<0,1>	$notrigger	0=launch triggers after, 1=disable triggers
	 *  @return	int<-2,1>				>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = 0)
	{
		if ($this->status < 0) {
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}


	/**
	 *	Validate object
	 *
	 *	@param	User		$user		User making status change
	 *  @param	int<0,1>	$notrigger	1=Does not execute triggers, 0= execute triggers
	 *	@return	int<-1,1>				Return integer <=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED) {
			dol_syslog(get_class($this)."::validate action abandoned: already validated", LOG_WARNING);
			return 0;
		}

		/* if (! ((!getDolGlobalInt('MAIN_USE_ADVANCED_PERMS') && $user->hasRight('subventions', 'paiement', 'write'))
		 || (getDolGlobalInt('MAIN_USE_ADVANCED_PERMS') && $user->hasRight('subventions', 'paiement_advance', 'validate')))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) { // empty should not happened, but when it occurs, the test save life
			$num = $this->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ";
			if (!empty($this->fields['ref'])) {
				$sql .= " ref = '".$this->db->escape($num)."',";
			}
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) {
				$sql .= ", date_validation = '".$this->db->idate($now)."'";
			}
			if (!empty($this->fields['fk_user_valid'])) {
				$sql .= ", fk_user_valid = ".((int) $user->id);
			}
			$sql .= " WHERE rowid = ".((int) $this->id);

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger) {
				// Call trigger
				$result = $this->call_trigger('PAIEMENT_VALIDATE', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
		}

		if (!$error) {
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref)) {
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'paiement/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'paiement/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++;
					$this->error = $this->db->lasterror();
				}
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filepath = 'paiement/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filepath = 'paiement/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++;
					$this->error = $this->db->lasterror();
				}

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->subventions->dir_output.'/paiement/'.$oldref;
				$dirdest = $conf->subventions->dir_output.'/paiement/'.$newref;
				if (!$error && file_exists($dirsource)) {
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest)) {
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->subventions->dir_output.'/paiement/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry) {
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error) {
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 * getTooltipContentArray
	 *
	 * @param	array<string,string> 	$params 	Params to construct tooltip data
	 * @since 	v18
	 * @return	array{optimize?:string,picto?:string,ref?:string}
	 */
	public function getTooltipContentArray($params)
	{
		global $langs;

		$datas = [];

		if (getDolGlobalInt('MAIN_OPTIMIZEFORTEXTBROWSER')) {
			return ['optimize' => $langs->trans("ShowPayment")];
		}
		$datas['picto'] = img_picto('', $this->picto).' <u>'.$langs->trans("Payment").'</u>';
		if (isset($this->status)) {
			$datas['picto'] .= ' '.$this->getLibStatut(5);
		}
		if (property_exists($this, 'ref')) {
			$datas['ref'] = '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;
		}
		if (property_exists($this, 'label')) {
			$datas['ref'] = '<br>'.$langs->trans('Label').':</b> '.$this->label;
		}

		return $datas;
	}

	/**
	 *  Return a link to the object card (with optionally the picto)
	 *
	 *  @param	int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param	string  $option                     On what the link point to ('nolink', ...)
	 *  @param	int     $notooltip                  1=Disable tooltip
	 *  @param	string  $morecss                    Add more css on link
	 *  @param	int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1, $moreparams = '')
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';
		$params = [
			'id' => $this->id,
			'objecttype' => $this->element.($this->module ? '@'.$this->module : ''),
			'option' => $option,
		];
		$classfortooltip = 'classfortooltip';
		$dataparams = '';
		if (getDolGlobalInt('MAIN_ENABLE_AJAX_TOOLTIP')) {
			$classfortooltip = 'classforajaxtooltip';
			$dataparams = ' data-params="'.dol_escape_htmltag(json_encode($params)).'"';
			$label = '';
		} else {
			$label = implode($this->getTooltipContentArray($params));
		}

		$url = dol_buildpath('/subventions/paiement_card.php', 1).'?id='.$this->id;

		if ($option !== 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && isset($_SERVER["PHP_SELF"]) && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($url && $add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
			if ($moreparams) {
				$url .= $moreparams;
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (getDolGlobalInt('MAIN_OPTIMIZEFORTEXTBROWSER')) {
				$label = $langs->trans("ShowPayment");
				$linkclose .= ' alt="'.dolPrintHTMLForAttribute($label).'"';
			}
			$linkclose .= ($label ? ' title="'.dolPrintHTMLForAttribute($label).'"' : ' title="tocomplete"');
			$linkclose .= $dataparams.' class="'.$classfortooltip.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink' || empty($url)) {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink' || empty($url)) {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), (($withpicto != 2) ? 'class="paddingright"' : ''), 0, 0, $notooltip ? 0 : 1);
			}
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (!getDolGlobalString(strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS')) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) {
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array($this->element.'dao'));
		$parameters = array('id' => $this->id, 'getnomurl' => &$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *	Return a thumb for kanban views
	 *
	 *	@param	string	    			$option		Where point the link (0=> main card, 1,2 => shipment, 'nolink'=>No link)
	 *  @param	?array<string,string>	$arraydata	Array of data
	 *  @return	string								HTML Code for Kanban thumb.
	 */
	public function getKanbanView($option = '', $arraydata = null)
	{
		global $conf, $langs;

		$selected = (empty($arraydata['selected']) ? 0 : $arraydata['selected']);

		$return = '<div class="box-flex-item box-flex-grow-zero">';
		$return .= '<div class="info-box info-box-sm">';
		$return .= '<span class="info-box-icon bg-infobox-action">';
		$return .= img_picto('', $this->picto);
		$return .= '</span>';
		$return .= '<div class="info-box-content">';
		$return .= '<span class="info-box-ref inline-block tdoverflowmax150 valignmiddle">'.(method_exists($this, 'getNomUrl') ? $this->getNomUrl() : $this->ref).'</span>';
		if ($selected >= 0) {
			$return .= '<input id="cb'.$this->id.'" class="flat checkforselect fright" type="checkbox" name="toselect[]" value="'.$this->id.'"'.($selected ? ' checked="checked"' : '').'>';
		}
		if (!empty($this->sub_label)) {
			$return .= ' <div class="inline-block opacitymedium valignmiddle tdoverflowmax150" title="'.dol_escape_htmltag($this->sub_label).'">'.$this->sub_label.'</div>';
		} elseif (property_exists($this, 'label')) {
			$return .= ' <div class="inline-block opacitymedium valignmiddle tdoverflowmax100">'.$this->label.'</div>';
		}
		if (property_exists($this, 'thirdparty') && is_object($this->thirdparty)) {
			$return .= '<br><div class="info-box-ref tdoverflowmax150">'.$this->thirdparty->getNomUrl(1).'</div>';
		}
		if (property_exists($this, 'amount')) {
			$return .= '<br>';
			$return .= '<span class="info-box-label amount">'.price($this->amount, 0, $langs, 1, -1, -1, $conf->currency).'</span>';
		}
		if (method_exists($this, 'getLibStatut')) {
			$return .= '<br><div class="info-box-status">'.$this->getLibStatut(3).'</div>';
		}
		$return .= '</div>';
		$return .= '</div>';
		$return .= '</div>';

		return $return;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param	int<0,6>	$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLabelStatus($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param	int<0,6>	$mode	0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string				Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the label of a given status
	 *
	 *  @param	int			$status		Id status
	 *  @param	int<0,6>	$mode		0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string					Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (is_null($status)) {
			return '';
		}

		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("subventions@subventions");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) {
			$statusType = 'status6';
		}

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param	int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = "SELECT rowid,";
		$sql .= " date_creation as datec, tms as datem";
		if (!empty($this->fields['date_validation'])) {
			$sql .= ", date_validation as datev";
		}
		if (!empty($this->fields['fk_user_creat'])) {
			$sql .= ", fk_user_creat";
		}
		if (!empty($this->fields['fk_user_modif'])) {
			$sql .= ", fk_user_modif";
		}
		if (!empty($this->fields['fk_user_valid'])) {
			$sql .= ", fk_user_valid";
		}
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);

				$this->id = $obj->rowid;

				if (!empty($this->fields['fk_user_creat'])) {
					$this->user_creation_id = $obj->fk_user_creat;
				}
				if (!empty($this->fields['fk_user_modif'])) {
					$this->user_modification_id = $obj->fk_user_modif;
				}
				if (!empty($this->fields['fk_user_valid'])) {
					$this->user_validation_id = $obj->fk_user_valid;
				}
				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = empty($obj->datem) ? '' : $this->db->jdate($obj->datem);
				if (!empty($obj->datev)) {
					$this->date_validation   = empty($obj->datev) ? '' : $this->db->jdate($obj->datev);
				}
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialize object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return	int
	 */
	public function initAsSpecimen()
	{
		// Set here init that are not commonf fields
		// $this->property1 = ...
		// $this->property2 = ...

		return $this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return	CommonObjectLine[]|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new PaiementLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, '(fk_paiement:=:'.((int) $this->id).')');

		if (is_numeric($result)) {
			$this->setErrorsFromObject($objectline);
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return	string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("subventions@subventions");

		if (!getDolGlobalString('SUBVENTIONS_PAIEMENT_ADDON')) {
			$conf->global->SUBVENTIONS_PAIEMENT_ADDON = 'mod_paiement_standard';
		}

		if (getDolGlobalString('SUBVENTIONS_PAIEMENT_ADDON')) {
			$mybool = false;

			$file = getDolGlobalString('SUBVENTIONS_PAIEMENT_ADDON').".php";
			$classname = getDolGlobalString('SUBVENTIONS_PAIEMENT_ADDON');

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir) {
				$dir = dol_buildpath($reldir."core/modules/subventions/");

				// Load file with numbering class (if found)
				$mybool = $mybool || @include_once $dir.$file;
			}

			if (!$mybool) {
				dol_print_error(null, "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				'@phan-var-force ModeleNumRefPaiement $obj';
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1') {
					return $numref;
				} else {
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		} else {
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}


	/**
	 * Return validation test result for a field.
	 * Need MAIN_ACTIVATE_VALIDATION_RESULT to be called.
	 *
	 * @param   array<string,array{type:string,label:string,enabled:int<0,2>|string,position:int,notnull?:int,visible:int<-2,5>|string,noteditable?:int<0,1>,default?:int<0,1>|string,index?:int,foreignkey?:string,searchall?:int<0,1>,isameasure?:int<0,1>,css?:string,csslist?:string,help?:string,showoncombobox?:int<0,2>,disabled?:int<0,1>,arrayofkeyval?:array<int|string,string>,comment?:string,validate?:int<0,1>}>  $fields Array of properties of field to show
	 * @param	string  $fieldKey            Key of attribute
	 * @param	string  $fieldValue          value of attribute
	 * @return	bool 						Return false if fail, true on success, set $this->error for error message
	 */
	public function validateField($fields, $fieldKey, $fieldValue)
	{
		// Add your own validation rules here.

		return parent::validateField($fields, $fieldKey, $fieldValue);
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		//global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlogfile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__." start", LOG_INFO);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		dol_syslog(__METHOD__." end", LOG_INFO);

		return $error;
	}

	/**
	 * Update fk_bank in subventions_paiement table
	 *
	 * @param  int $bank_line_id ID of bank line in llx_bank
	 * @return int               >0 if OK, <=0 if KO
	 */
	public function update_fk_bank($bank_line_id)
	{
		$this->fk_bank = $bank_line_id;
		$sql = "UPDATE ".MAIN_DB_PREFIX."subventions_paiement SET fk_bank = ".((int) $bank_line_id)." WHERE rowid = ".((int) $this->id);
		$resql = $this->db->query($sql);
		if ($resql) {
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Add payment to Dolibarr bank account (llx_bank)
	 *
	 * @param  User   $user             User making the action
	 * @param  int    $accountid        Bank account ID (llx_bank_account)
	 * @param  int    $paiement_type    Payment type ID or code from c_paiement
	 * @param  string $label            Label for bank transaction
	 * @param  string $num_paiement     Cheque/transfer reference number
	 * @param  string $accountancy_code Receivable accountancy code (e.g. 4411)
	 * @return int                      >0 if OK, <0 if KO
	 */
	public function addPaymentToBank($user, $accountid, $paiement_type = 0, $label = '', $num_paiement = '', $accountancy_code = '')
	{
		global $conf, $langs;

		if (!isModEnabled("banque")) {
			return 0;
		}

		if (empty($accountid) || $accountid <= 0) {
			$this->error = $langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("BankAccount"));
			return -1;
		}

		require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
		require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
		dol_include_once('/subventions/class/subvention.class.php');
		dol_include_once('/subventions/class/financement.class.php');

		$subvention = new Subvention($this->db);
		if ($this->fk_sub > 0) {
			$subvention->fetch($this->fk_sub);
		}

		$financement = new Financement($this->db);
		if ($this->fk_fin > 0) {
			$financement->fetch($this->fk_fin);
		}

		$thirdparty = new Societe($this->db);
		$socid = $this->fk_soc > 0 ? $this->fk_soc : ($financement->fk_soc > 0 ? $financement->fk_soc : 0);
		if ($socid > 0) {
			$thirdparty->fetch($socid);
		}

		// Find receivable accountancy code if not specified
		if (empty($accountancy_code)) {
			if ($financement->fk_financeur > 0) {
				$sqlf = "SELECT accountancy_code_receivable, accountancy_code FROM ".MAIN_DB_PREFIX."c_subventions_financeur WHERE rowid = ".((int) $financement->fk_financeur);
				$resf = $this->db->query($sqlf);
				if ($resf && ($objf = $this->db->fetch_object($resf))) {
					$accountancy_code = !empty($objf->accountancy_code_receivable) ? $objf->accountancy_code_receivable : $objf->accountancy_code;
				}
			}
			if (empty($accountancy_code)) {
				$accountancy_code = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_CODE_RECEIVABLE_DEFAULT', '441000');
			}
		}

		// Resolve payment mode code
		$paymentmode_code = 'VIR';
		if (!empty($paiement_type)) {
			if (is_numeric($paiement_type)) {
				$sqlm = "SELECT code FROM ".MAIN_DB_PREFIX."c_paiement WHERE id = ".((int) $paiement_type);
				$resm = $this->db->query($sqlm);
				if ($resm && ($objm = $this->db->fetch_object($resm))) {
					$paymentmode_code = $objm->code;
				}
			} else {
				$paymentmode_code = (string) $paiement_type;
			}
		}

		if (empty($label)) {
			$label = $langs->trans("SubventionPayment").': '.($subvention->ref ? $subvention->ref.' - ' : '').$this->ref.($thirdparty->name ? ' ('.$thirdparty->name.')' : '');
		}

		$acc = new Account($this->db);
		$result = $acc->fetch($accountid);
		if ($result <= 0) {
			$this->error = $langs->trans("ErrorBankAccountNotFound");
			return -1;
		}

		// Insert payment into llx_bank
		$bank_line_id = $acc->addline(
			$this->datep,
			$paymentmode_code,
			$label,
			(float) $this->montant,
			$num_paiement,
			0,
			$user,
			$thirdparty->name,
			'',
			$accountancy_code
		);

		if ($bank_line_id > 0) {
			$this->fk_bank = $bank_line_id;
			$this->fk_account = $accountid;
			$this->fk_paiement = is_numeric($paiement_type) ? (int) $paiement_type : 0;
			$this->num_paiement = $num_paiement;

			$sql = "UPDATE ".MAIN_DB_PREFIX."subventions_paiement SET ";
			$sql .= "fk_bank = ".((int) $bank_line_id).", ";
			$sql .= "fk_account = ".((int) $accountid).", ";
			$sql .= "fk_paiement = ".((int) $this->fk_paiement).", ";
			$sql .= "num_paiement = '".$this->db->escape($num_paiement)."' ";
			$sql .= "WHERE rowid = ".((int) $this->id);
			$this->db->query($sql);

			// Add link to subvention payment in bank_url
			$url_paiement = dol_buildpath('/subventions/paiement_card.php', 1).'?id=';
			$acc->add_url_line($bank_line_id, $this->id, $url_paiement, '(paiement)', 'payment_subvention');

			// Add link to company in bank_url if thirdparty exists
			if ($socid > 0) {
				$url_soc = DOL_URL_ROOT.'/societe/card.php?socid=';
				$acc->add_url_line($bank_line_id, $socid, $url_soc, $thirdparty->name, 'company');
			}

			return $bank_line_id;
		} else {
			$this->error = $acc->error;
			$this->errors = $acc->errors;
			return -1;
		}
	}

	/**
	 * Record accounting engagement in Dolibarr General Ledger (BookKeeping)
	 *
	 * @param  User   $user                User making the action
	 * @param  int    $date_engagement     Timestamp date of engagement
	 * @param  string $journal             Journal code (e.g. 'OD')
	 * @param  string $account_bank        Bank / treasury account (e.g. '512000') - Debit
	 * @param  string $account_receivable  Receivable account (e.g. '441000') - Credit
	 * @param  string $label               Operation label
	 * @param  string $subledger_account   Subledger account code (tiers)
	 * @return int                         >0 if OK, <0 if KO
	 */
	public function bookkeep($user, $date_engagement, $journal = '', $account_bank = '', $account_receivable = '', $label = '', $subledger_account = '')
	{
		global $conf, $langs;

		if (empty($this->montant) || $this->montant <= 0) {
			$this->error = $langs->trans("ErrorNoAmountToBookkeep");
			return -1;
		}

		if (!isModEnabled('accounting') && !isModEnabled('accountancy')) {
			$this->error = $langs->trans("ErrorAccountancyModuleNotActive");
			return -1;
		}

		require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/accountancy/class/bookkeeping.class.php';
		require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
		dol_include_once('/subventions/class/subvention.class.php');
		dol_include_once('/subventions/class/financement.class.php');

		$subvention = new Subvention($this->db);
		if ($this->fk_sub > 0) {
			$subvention->fetch($this->fk_sub);
		}

		$financement = new Financement($this->db);
		if ($this->fk_fin > 0) {
			$financement->fetch($this->fk_fin);
		}

		$thirdparty = new Societe($this->db);
		$socid = $this->fk_soc > 0 ? $this->fk_soc : ($financement->fk_soc > 0 ? $financement->fk_soc : 0);
		if ($socid > 0) {
			$thirdparty->fetch($socid);
		}

		// Fallback for journal (prefer bank account's journal, then payment journal setting, then OD)
		if (empty($journal)) {
			$bank_journal = '';
			if (!empty($this->fk_account)) {
				require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
				$acc_tmp = new Account($this->db);
				if ($acc_tmp->fetch($this->fk_account) > 0 && !empty($acc_tmp->fk_accountancy_journal)) {
					$sqlj_acc = "SELECT code FROM ".MAIN_DB_PREFIX."accounting_journal WHERE rowid = ".((int) $acc_tmp->fk_accountancy_journal);
					$resj_acc = $this->db->query($sqlj_acc);
					if ($resj_acc && ($objj_acc = $this->db->fetch_object($resj_acc))) {
						$bank_journal = $objj_acc->code;
					}
				}
			}
			$journal = !empty($bank_journal) ? $bank_journal : getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_JOURNAL_PAYMENT', 'BQ');
			if (empty($journal)) {
				$journal = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_JOURNAL', 'OD');
			}
		}

		// Fallback for bank account (Class 5)
		if (empty($account_bank)) {
			if (!empty($this->fk_account)) {
				require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
				$acc_tmp = new Account($this->db);
				if ($acc_tmp->fetch($this->fk_account) > 0 && !empty($acc_tmp->account_number)) {
					$account_bank = $acc_tmp->account_number;
				}
			}
			if (empty($account_bank)) {
				$account_bank = '512000';
			}
		}

		// Fallback for receivable account (Class 4)
		if (empty($account_receivable)) {
			if ($financement->fk_financeur > 0) {
				$sqlf = "SELECT accountancy_code_receivable, accountancy_code FROM ".MAIN_DB_PREFIX."c_subventions_financeur WHERE rowid = ".((int) $financement->fk_financeur);
				$resf = $this->db->query($sqlf);
				if ($resf && ($objf = $this->db->fetch_object($resf))) {
					$account_receivable = !empty($objf->accountancy_code_receivable) ? $objf->accountancy_code_receivable : $objf->accountancy_code;
				}
			}
			if (empty($account_receivable)) {
				$account_receivable = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_CODE_RECEIVABLE_DEFAULT', '441000');
			}
		}

		$journal_label = 'Journal '.$journal;
		$sqlj = "SELECT label FROM ".MAIN_DB_PREFIX."accounting_journal WHERE code = '".$this->db->escape($journal)."' AND entity = ".((int) $conf->entity);
		$resj = $this->db->query($sqlj);
		if ($resj && ($objj = $this->db->fetch_object($resj))) {
			$journal_label = $objj->label;
		}

		if (empty($label)) {
			$label = $langs->trans("SubventionPayment").': '.($subvention->ref ? $subvention->ref.' - ' : '').$this->ref.($thirdparty->name ? ' ('.$thirdparty->name.')' : '');
		}

		$this->db->begin();

		// Line 1: Débit 512x (Trésorerie / Banque)
		$bk1 = new BookKeeping($this->db);
		$bk1->doc_date = $date_engagement;
		$bk1->doc_type = 'subvention_paiement';
		$bk1->doc_ref = ($subvention->ref ? $subvention->ref.' / ' : '').$this->ref;
		$bk1->fk_doc = $this->id;
		$bk1->fk_docdet = $this->id;
		$bk1->thirdparty_code = !empty($thirdparty->code_client) ? $thirdparty->code_client : '';
		$bk1->subledger_account = '';
		$bk1->subledger_label = '';
		$bk1->numero_compte = $account_bank;
		$bk1->label_compte = $langs->trans("SubventionBankAccount");
		$bk1->label_operation = $label;
		$bk1->sens = 'D';
		$bk1->debit = (float) $this->montant;
		$bk1->credit = 0;
		$bk1->montant = (float) $this->montant;
		$bk1->code_journal = $journal;
		$bk1->journal_label = $journal_label;
		$bk1->fk_user_author = $user->id;
		$bk1->entity = $conf->entity;

		$res1 = $bk1->create($user);
		if ($res1 < 0) {
			$this->error = $bk1->error;
			$this->errors = $bk1->errors;
			$this->db->rollback();
			return -1;
		}

		// Line 2: Crédit 441x (Créance de subvention)
		$bk2 = new BookKeeping($this->db);
		$bk2->doc_date = $date_engagement;
		$bk2->doc_type = 'subvention_paiement';
		$bk2->doc_ref = ($subvention->ref ? $subvention->ref.' / ' : '').$this->ref;
		$bk2->fk_doc = $this->id;
		$bk2->fk_docdet = $this->id;
		$bk2->thirdparty_code = !empty($thirdparty->code_client) ? $thirdparty->code_client : (!empty($thirdparty->code_compta_client) ? $thirdparty->code_compta_client : '');
		$bk2->subledger_account = !empty($subledger_account) ? $subledger_account : (!empty($thirdparty->code_compta_client) ? $thirdparty->code_compta_client : '');
		$bk2->subledger_label = $thirdparty->name;
		$bk2->numero_compte = $account_receivable;
		$bk2->label_compte = $langs->trans("SubventionReceivableAccount");
		$bk2->label_operation = $label;
		$bk2->sens = 'C';
		$bk2->debit = 0;
		$bk2->credit = (float) $this->montant;
		$bk2->montant = (float) $this->montant;
		$bk2->code_journal = $journal;
		$bk2->journal_label = $journal_label;
		$bk2->piece_num = $bk1->piece_num;
		$bk2->fk_user_author = $user->id;
		$bk2->entity = $conf->entity;

		$res2 = $bk2->create($user);
		if ($res2 < 0) {
			$this->error = $bk2->error;
			$this->errors = $bk2->errors;
			$this->db->rollback();
			return -1;
		}

		$this->accounted = 1;
		$this->date_engagement = $date_engagement;
		$this->fk_bookkeeping_bank = $bk1->id;
		$this->fk_bookkeeping_receivable = $bk2->id;

		$sql = "UPDATE ".MAIN_DB_PREFIX."subventions_paiement SET ";
		$sql .= "accounted = 1, ";
		$sql .= "date_engagement = '".$this->db->idate($date_engagement)."', ";
		$sql .= "fk_bookkeeping_bank = ".((int) $bk1->id).", ";
		$sql .= "fk_bookkeeping_receivable = ".((int) $bk2->id)." ";
		$sql .= "WHERE rowid = ".((int) $this->id);

		$resql = $this->db->query($sql);
		if (!$resql) {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -1;
		}

		$this->db->commit();
		return 1;
	}

	/**
	 * Remove accounting engagement from Dolibarr General Ledger (BookKeeping)
	 *
	 * @param  User $user User cancelling the entry
	 * @return int        >0 if OK, <0 if KO
	 */
	public function unbookkeep($user)
	{
		global $conf;

		$this->db->begin();

		// Delete bookkeeping entries linked to this payment
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."accounting_bookkeeping ";
		$sql .= "WHERE doc_type = 'subvention_paiement' AND fk_doc = ".((int) $this->id)." AND entity = ".((int) $conf->entity);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -1;
		}

		$this->accounted = 0;
		$this->date_engagement = null;
		$this->fk_bookkeeping_bank = null;
		$this->fk_bookkeeping_receivable = null;

		$sql2 = "UPDATE ".MAIN_DB_PREFIX."subventions_paiement SET ";
		$sql2 .= "accounted = 0, date_engagement = NULL, fk_bookkeeping_bank = NULL, fk_bookkeeping_receivable = NULL ";
		$sql2 .= "WHERE rowid = ".((int) $this->id);
		$resql2 = $this->db->query($sql2);
		if (!$resql2) {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -1;
		}

		$this->db->commit();
		return 1;
	}
}


require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';

/**
 * Class PaiementLine. You can also remove this and generate a CRUD class for lines objects.
 */
class PaiementLine extends CommonObjectLine
{
	// To complete with content of an object PaiementLine
	// We should have a field rowid, fk_paiement and position

	/**
	 * To overload
	 * @see CommonObjectLine
	 */
	public $parent_element = '';		// Example: '' or 'paiement'

	/**
	 * To overload
	 * @see CommonObjectLine
	 */
	public $fk_parent_attribute = '';	// Example: '' or 'fk_paiement'

	/**
	 * Constructor
	 *
	 * @param	DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;

		$this->isextrafieldmanaged = 0;
	}
}
}

