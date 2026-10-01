<?php
/* Copyright (C) 2023		Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2025		François Brichart			<francois@disqutons.fr>
 * Copyright (C) 2026		Romain MP		<romain.mp@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    subventions/class/actions_subventions.class.php
 * \ingroup subventions
 * \brief   Example hook overload.
 *
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonhookactions.class.php';

/**
 * Class ActionsSubventions
 */
class ActionsSubventions extends CommonHookActions
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var string[] Errors
	 */
	public $errors = array();


	/**
	 * @var mixed[] Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var ?string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var int		Priority of hook (50 is used if value is not defined)
	 */
	public $priority;


	/**
	 * Constructor
	 *
	 *  @param	DoliDB	$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}
	
	/**
	 * Hook pour afficher des informations dans l'onglet vue d'ensemble d'un projet
	 */
	public function completeListOfReferent($parameters, &$object, &$action) {
		// $parameters : tableau de paramètres (contexte, etc.)
		// $object : objet projet actuel (ex : $object->id, $object->ref)
		// $action : action en cours

		global $conf, $user, $langs;

		if ($object->element == 'project' && getDolGlobalInt('SUBVENTIONS_PROJECT')) {
			// Handle custom unlink action for subventions_subvention_projet
			if ($action == 'unlink' && GETPOST('tablename', 'aZ09') == 'subventions_subvention_projet') {
				$elementselectid = GETPOSTINT('elementselect');
				if ($elementselectid > 0) {
					dol_include_once('/custom/subventions/class/subventionproject.class.php');
					$sp = new SubventionProject($this->db);
					$sp->id = $elementselectid;
					$sp->delete($user);
					$action = '';
					setEventMessages($langs->trans("RecordDeleted"), null, 'mesgs');
				}
			}

			// Check if multi-project ventilation table exists and has data for this project
			dol_include_once('/custom/subventions/class/subventionproject.class.php');
			$subventionproject = new SubventionProject($this->db);
			$ventilations = $subventionproject->fetchAllBySubvention(0); // dummy call to check class

			// Use the junction table for ventilated amounts per project
			$this->results = array(
				'subvention' => array(
				'name' => $langs->trans("SubventionsAllocated"),
				'title' => $langs->trans("ListSubventionsAllocatedProject"),
				'class' => 'SubventionProject',
				'table' => 'subventions_subvention_projet',
				'datefieldname' => 'datec',
				'margin' => 'add',
				'project_field' => 'fk_project',
				'url' => dol_buildpath('/subventions/subvention_list.php', 1).'?search_fk_project='.$object->id,
				'urlnew' => dol_buildpath('/subventions/subvention_card.php', 1).'?action=create&origin=project&originid='.$object->id.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$object->id),
				'lang' => 'subventions',
				'buttonnew' => $langs->trans('AddSubvention'),
				'testnew' => $user->hasRight('subventions', 'subvention', 'write'),
				'test' => isModEnabled('subventions') && $user->hasRight('subventions', 'subvention', 'read'),
				),
			);
        	return 0;
    	}
	}

	/**
	 * Hook printOverviewDetail: customize detail table for subventions in project overview
	 *
	 * @param	array<string,mixed>	$parameters		Hook parameters ('key', 'value', 'dates', 'datee')
	 * @param	CommonObject		$object			Project object
	 * @param	string				$action			Current action
	 * @param	Hookmanager			$hookmanager	Hook manager
	 * @return	int									0 to let standard code execute, 1 to replace with $this->resprints
	 */
	public function printOverviewDetail($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		if (empty($parameters['key']) || $parameters['key'] !== 'subvention') {
			return 0;
		}

		$value = $parameters['value'];
		if (empty($value['test'])) {
			return 0;
		}

		$langs->loadLangs(array('main', 'subventions@subventions', 'projects', 'companies', 'bills'));

		$title = $value['title'];
		$tablename = $value['table'];
		$datefieldname = $value['datefieldname'];
		$urlnew = empty($value['urlnew']) ? '' : $value['urlnew'];
		$buttonnew = empty($value['buttonnew']) ? '' : $value['buttonnew'];
		$testnew = empty($value['testnew']) ? '' : $value['testnew'];
		$project_field = empty($value['project_field']) ? 'fk_project' : $value['project_field'];
		$dates = $parameters['dates'];
		$datee = $parameters['datee'];

		$elementarray = $object->get_element_list('subvention', $tablename, $datefieldname, $dates, $datee, $project_field);

		$addform = '';
		if (!getDolGlobalString('PROJECT_CREATE_ON_OVERVIEW_DISABLED') && $urlnew) {
			$addform .= '<div class="inline-block valignmiddle">';
			if ($testnew) {
				$addform .= '<a class="buttonxxx marginleftonly" href="'.$urlnew.'" title="'.dol_escape_htmltag($langs->trans($buttonnew)).'"><span class="fa fa-plus-circle valignmiddle paddingleft"></span></a>';
			} elseif (!getDolGlobalString('MAIN_BUTTON_HIDE_UNAUTHORIZED')) {
				$addform .= '<span title="'.dol_escape_htmltag($langs->trans($buttonnew)).'"><a class="buttonxxx marginleftonly buttonRefused" disabled="disabled" href="#"><span class="fa fa-plus-circle valignmiddle paddingleft"></span></a></span>';
			}
			$addform .= '</div>';
		}

		$out = '<a id="table_'.$tablename.'"></a>';
		$out .= load_fiche_titre($langs->trans($title), $addform, '');
		$out .= "\n<!-- Table for tablename = ".$tablename." -->\n";
		$out .= '<div class="div-table-responsive">';
		$out .= '<table class="noborder centpercent">';

		// Table header
		$out .= '<tr class="liste_titre">';
		$out .= '<td style="width: 24px"></td>';
		$out .= '<td style="width: 200px">'.$langs->trans("Ref").'</td>';
		$out .= '<td class="center" style="width: 80px">'.$langs->trans("Year").'</td>';
		$out .= '<td class="center" style="width: 100px">'.$langs->trans("NumberOfMonths").'</td>';
		$out .= '<td class="center" style="width: 120px">'.$langs->trans("Date").'</td>';
		$out .= '<td>'.$langs->trans("ThirdParty").'</td>';
		$out .= '<td class="right" width="120">'.$langs->trans("AmountHT").'</td>';
		$out .= '<td class="right" width="120">'.$langs->trans("AmountTTC").'</td>';
		$out .= '<td class="right" width="200">'.$langs->trans("Status").'</td>';
		$out .= '</tr>';

		if (is_array($elementarray) && count($elementarray) > 0) {
			dol_include_once('/custom/subventions/class/subventionproject.class.php');
			dol_include_once('/custom/subventions/class/subvention.class.php');

			$total_ht = 0;
			$total_ttc = 0;
			$total_months = 0;
			$i = 0;

			foreach ($elementarray as $elem) {
				$tmp = explode('_', $elem);
				$idofelement = (int) $tmp[0];

				$element = new SubventionProject($this->db);
				if ($element->fetch($idofelement) <= 0) {
					continue;
				}

				$i++;
				$out .= '<tr class="oddeven">';

				// Remove link column
				$out .= '<td style="width: 24px">';
				if (!getDolGlobalString('PROJECT_DISABLE_UNLINK_FROM_OVERVIEW') || !empty($user->admin)) {
					$unlinkUrl = $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=unlink&token='.newToken().'&tablename='.$tablename.'&elementselect='.$element->id.($project_field ? '&projectfield='.$project_field : '');
					$out .= '<a href="'.$unlinkUrl.'" class="reposition">';
					$out .= img_picto($langs->trans('Unlink'), 'unlink');
					$out .= '</a>';
				}
				$out .= '</td>';

				// Ref
				$out .= '<td class="left nowraponall">';
				$out .= $element->getNomUrl(1);
				$out .= '</td>';

				// Year
				$out .= '<td class="center">'.(!empty($element->annee) ? $element->annee : '').'</td>';

				// Duration in months
				$out .= '<td class="center">'.(!empty($element->nb_mois) ? $element->nb_mois : '').'</td>';

				// Date
				$out .= '<td class="center">'.dol_print_date($element->datec, 'day').'</td>';

				// ThirdParty
				$out .= '<td class="tdoverflowmax150">';
				if (is_object($element->thirdparty)) {
					$out .= $element->thirdparty->getNomUrl(1, '', 48);
				}
				$out .= '</td>';

				// Amount HT
				$out .= '<td class="right"><span class="amount">'.price($element->amount).'</span></td>';

				// Amount TTC
				$out .= '<td class="right"><span class="amount">'.price($element->amount).'</span></td>';

				// Status
				$out .= '<td class="right">'.$element->getLibStatut(5).'</td>';

				$out .= '</tr>';

				$total_ht += $element->amount;
				$total_ttc += $element->amount;
				if (!empty($element->nb_mois)) {
					$total_months += $element->nb_mois;
				}
			}

			// Total
			$out .= '<tr class="liste_total">';
			$out .= '<td colspan="3">'.$langs->trans("Number").': '.$i.'</td>';
			$out .= '<td class="center">'.($total_months > 0 ? $total_months : '').'</td>';
			$out .= '<td colspan="2"></td>';
			$out .= '<td class="right">'.$langs->trans("TotalHT").' : '.price($total_ht).'</td>';
			$out .= '<td class="right">'.$langs->trans("TotalTTC").' : '.price($total_ttc).'</td>';
			$out .= '<td></td>';
			$out .= '</tr>';
		} else {
			$out .= '<tr class="oddeven"><td colspan="9" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
		}

		$out .= '</table></div>';

		$this->resprints = $out;
		return 1;
	}


	/**
	 * Execute action completeTabsHead
	 *
	 * @param	array<string,mixed>	$parameters		Array of parameters
	 * @param	CommonObject		$object			The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string				$action			'add', 'update', 'view'
	 * @param	Hookmanager			$hookmanager	Hookmanager
	 * @return	int									Return integer <0 if KO,
	 *												=0 if OK but we want to process standard actions too,
	 *												>0 if OK and we want to replace standard actions.
	 */
	public function completeTabsHead(&$parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user, $db;

		if (!isset($parameters['object']->element)) {
			return 0;
		}
		if ($parameters['mode'] == 'remove') {
			// used to make some tabs removed
			return 0;
		} elseif ($parameters['mode'] == 'add') {
			$langs->load('subventions@subventions');
			// used when we want to add some tabs
			
			$counter = count($parameters['head']);
			$element = $parameters['object']->element;
			$id = $parameters['object']->id;
			$identifiantsOnglets = array_column($parameters['head'], 2);


			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
			//if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
			if (in_array($element, ['societe']) && !in_array('subventions', $identifiantsOnglets)) {
				$datacount = 0;
				$sql = "SELECT COUNT(*) as nb FROM " . MAIN_DB_PREFIX . "subventions_subvention WHERE fk_soc = ".$id;
				$resql = $this->db->query($sql);
				if ($resql) {
					$obj = $this->db->fetch_object($resql);
					$datacount = $obj->nb;
				} else {
					dol_syslog("Erreur SQL : " . $this->db->lasterror(), LOG_ERR);
				}
				$parameters['head'][$counter][0] = dol_buildpath('/subventions/subvention_list.php', 1) . '?socid='.$id.'&search_fk_soc='.$id;
				$parameters['head'][$counter][1] = $langs->trans('Subventions');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'subventions';
				$counter++;
			}
			if (in_array($element, ['societe']) && !in_array('financements', $identifiantsOnglets)) {
				$datacount = 0;
				$sql = "SELECT COUNT(*) as nb FROM " . MAIN_DB_PREFIX . "subventions_financement WHERE fk_soc = ".$id;
				$resql = $this->db->query($sql);
				if ($resql) {
					$obj = $this->db->fetch_object($resql);
					$datacount = $obj->nb;
				} else {
					dol_syslog("Erreur SQL : " . $this->db->lasterror(), LOG_ERR);
				}

				$parameters['head'][$counter][0] = dol_buildpath('/subventions/financement_list.php', 1) . '?socid='.$id.'&search_fk_soc='.$id;
				$parameters['head'][$counter][1] = $langs->trans('Financements');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'financements';
				$counter++;
			}
			if ($counter > 0 && (int) DOL_VERSION < 14) {  // @phpstan-ignore-line
				$this->results = $parameters['head'];
				// return 1 to replace standard code
				return 1;
			} else {
				// From V14 onwards, $parameters['head'] is modifiable by reference
				return 0;
			}
		} else {
			// Bad value for $parameters['mode']
			return -1;
		}
	}

	/**
	 * Hook getData for AccountingJournal (accountingjournaldao)
	 * Injects Subventions Financements and Paiements into Journal des Opérations Diverses (nature 1)
	 *
	 * @param  array<string,mixed> $parameters Array of parameters
	 * @param  AccountingJournal   $object     Accounting journal object
	 * @param  string              $action     Current action
	 * @param  HookManager         $hookmanager Hook manager
	 * @return int                             0 if OK
	 */
	public function getData(&$parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $langs, $user;

		if (!getDolGlobalInt('SUBVENTIONS_ACCOUNTANCY_ENABLED')) {
			return 0;
		}

		$journal_od = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_JOURNAL', 'OD');
		$journal_payment = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_JOURNAL_PAYMENT', 'BQ');

		// Match the default journal configured in Subventions settings, or fallback to nature if unset
		$is_od_journal = (!empty($journal_od) && $object->code == $journal_od) || (empty($journal_od) && isset($object->nature) && $object->nature == 1);
		$is_payment_journal = (!empty($journal_payment) && $object->code == $journal_payment) || (empty($journal_payment) && isset($object->nature) && ($object->nature == 4 || $object->nature == 1));

		if (!$is_od_journal && !$is_payment_journal) {
			return 0;
		}

		require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/accounting.lib.php';
		dol_include_once('/subventions/class/subvention.class.php');
		dol_include_once('/subventions/class/financement.class.php');
		dol_include_once('/subventions/class/paiement.class.php');
		require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

		$langs->loadLangs(array('subventions@subventions', 'accountancy'));

		$type = !empty($parameters['type']) ? $parameters['type'] : 'view';
		$date_start = !empty($parameters['date_start']) ? $parameters['date_start'] : null;
		$date_end = !empty($parameters['date_end']) ? $parameters['date_end'] : null;
		$in_bookkeeping = !empty($parameters['in_bookkeeping']) ? $parameters['in_bookkeeping'] : 'notyet';

		$journal = $object->code;
		$journal_label_formatted = $langs->transnoentities($object->label);
		$now = dol_now();

		// Auto-synchronize accounted flags based on bookkeeping existence
		$this->db->query("UPDATE ".MAIN_DB_PREFIX."subventions_financement sf SET accounted = 1 WHERE accounted = 0 AND EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping ab WHERE ab.doc_type IN ('subvention', 'subvention_financement') AND ab.fk_doc = sf.rowid)");
		$this->db->query("UPDATE ".MAIN_DB_PREFIX."subventions_paiement sp SET accounted = 1 WHERE accounted = 0 AND (EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping ab WHERE ab.doc_type = 'subvention_paiement' AND ab.fk_doc = sp.rowid) OR (sp.fk_bank IS NOT NULL AND sp.fk_bank > 0 AND EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping ab WHERE ab.doc_type = 'bank' AND ab.fk_doc = sp.fk_bank)))");
		$this->db->query("UPDATE ".MAIN_DB_PREFIX."subventions_financement sf SET accounted = 0 WHERE accounted = 1 AND NOT EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping ab WHERE ab.doc_type IN ('subvention', 'subvention_financement') AND ab.fk_doc = sf.rowid)");
		$this->db->query("UPDATE ".MAIN_DB_PREFIX."subventions_paiement sp SET accounted = 0 WHERE accounted = 1 AND NOT EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping ab WHERE (ab.doc_type = 'subvention_paiement' AND ab.fk_doc = sp.rowid) OR (sp.fk_bank IS NOT NULL AND sp.fk_bank > 0 AND ab.doc_type = 'bank' AND ab.fk_doc = sp.fk_bank))");

		// --- 1. Financements (Engagements - OD) ---
		if ($is_od_journal) {
		$sqlf = "SELECT f.rowid, f.ref, f.date_creation, f.date_engagement, f.montant_acc, f.fk_soc, f.fk_sub, f.fk_financeur, f.accounted";
		$sqlf .= " FROM ".MAIN_DB_PREFIX."subventions_financement as f";
		$sqlf .= " WHERE f.entity IN (".getEntity('financement@subventions').")";
		$sqlf .= " AND f.status = 2 AND f.montant_acc > 0";
		if ($date_start && $date_end) {
			$sqlf .= " AND (";
			$sqlf .= "   (f.date_engagement IS NOT NULL AND f.date_engagement >= '".$this->db->idate($date_start)."' AND f.date_engagement <= '".$this->db->idate($date_end)."')";
			$sqlf .= "   OR (f.date_engagement IS NULL AND f.date_creation >= '".$this->db->idate($date_start)."' AND f.date_creation <= '".$this->db->idate($date_end)."')";
			$sqlf .= " )";
		}
		if ($in_bookkeeping == 'already') {
			$sqlf .= " AND EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping ab WHERE ab.doc_type IN ('subvention', 'subvention_financement') AND ab.fk_doc = f.rowid AND ab.code_journal = '".$this->db->escape($journal)."')";
		} elseif ($in_bookkeeping == 'notyet') {
			$sqlf .= " AND NOT EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping ab WHERE ab.doc_type IN ('subvention', 'subvention_financement') AND ab.fk_doc = f.rowid AND ab.code_journal = '".$this->db->escape($journal)."')";
		}
		$sqlf .= " ORDER BY f.rowid ASC";

		$resqlf = $this->db->query($sqlf);
		if ($resqlf) {
			$fin_static = new Financement($this->db);
			$sub_static = new Subvention($this->db);
			$soc_static = new Societe($this->db);

			while ($objf = $this->db->fetch_object($resqlf)) {
				$fin_static->fetch((int) $objf->rowid);
				$sub_static->fetch((int) $objf->fk_sub);
				if ($objf->fk_soc > 0) {
					$soc_static->fetch((int) $objf->fk_soc);
				}

				$docdate = !empty($objf->date_engagement) ? $this->db->jdate($objf->date_engagement) : $this->db->jdate($objf->date_creation);
				$docdate_fmt = dol_print_date($docdate, 'day');

				// Determine receivable and product accounts
				$account_receivable = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_CODE_RECEIVABLE_DEFAULT', '441000');
				$account_product = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_CODE_PRODUCT_DEFAULT', '740000');
				if ($objf->fk_financeur > 0) {
					$sqlc = "SELECT accountancy_code_receivable, accountancy_code FROM ".MAIN_DB_PREFIX."c_subventions_financeur WHERE rowid = ".((int) $objf->fk_financeur);
					$resc = $this->db->query($sqlc);
					if ($resc && ($objc = $this->db->fetch_object($resc))) {
						if (!empty($objc->accountancy_code_receivable)) $account_receivable = $objc->accountancy_code_receivable;
						if (!empty($objc->accountancy_code)) $account_product = $objc->accountancy_code;
					}
				}
				$subledger = !empty($soc_static->code_compta_client) ? $soc_static->code_compta_client : '';
				$label = $langs->trans("SubventionEngagement").': '.($sub_static->ref ? $sub_static->ref.' - ' : '').$fin_static->ref.' ('.$soc_static->name.')';
				$montant = (float) $objf->montant_acc;

				$acc_info_rec = $object->getAccountingAccountInfos($account_receivable);
				$acc_info_prod = $object->getAccountingAccountInfos($account_product);

				$element = array(
					'ref' => dol_trunc($fin_static->ref, 16, 'right', 'UTF-8', 1),
					'error' => '',
					'blocks' => array(),
				);

				$blocks = array();
				if ($type == 'view') {
					$piece_link = $fin_static->getNomUrl(1);
					$blocks[] = array(
						'date' => $docdate_fmt,
						'piece' => $piece_link,
						'account_accounting' => length_accountg($account_receivable),
						'subledger_account' => $subledger,
						'label_operation' => $label,
						'debit' => price($montant),
						'credit' => '',
					);
					$blocks[] = array(
						'date' => $docdate_fmt,
						'piece' => $piece_link,
						'account_accounting' => length_accountg($account_product),
						'subledger_account' => '',
						'label_operation' => $label,
						'debit' => '',
						'credit' => price($montant),
					);
				} elseif ($type == 'bookkeeping') {
					$blocks[] = array(
						'doc_date' => $docdate,
						'date_lim_reglement' => '',
						'doc_ref' => ($sub_static->ref ? $sub_static->ref.' / ' : '').$fin_static->ref,
						'date_creation' => $now,
						'doc_type' => 'subvention_financement',
						'fk_doc' => $fin_static->id,
						'fk_docdet' => $fin_static->id,
						'thirdparty_code' => $soc_static->code_client,
						'subledger_account' => $subledger,
						'subledger_label' => $soc_static->name,
						'numero_compte' => (string) $account_receivable,
						'label_compte' => !empty($acc_info_rec['label']) ? $acc_info_rec['label'] : $langs->trans("SubventionReceivableAccount"),
						'label_operation' => $label,
						'montant' => $montant,
						'sens' => 'D',
						'debit' => $montant,
						'credit' => 0,
						'code_journal' => $journal,
						'journal_label' => $journal_label_formatted,
						'piece_num' => '',
						'import_key' => '',
						'fk_user_author' => $user->id,
						'entity' => $conf->entity,
					);
					$blocks[] = array(
						'doc_date' => $docdate,
						'date_lim_reglement' => '',
						'doc_ref' => ($sub_static->ref ? $sub_static->ref.' / ' : '').$fin_static->ref,
						'date_creation' => $now,
						'doc_type' => 'subvention_financement',
						'fk_doc' => $fin_static->id,
						'fk_docdet' => $fin_static->id,
						'thirdparty_code' => $soc_static->code_client,
						'subledger_account' => '',
						'subledger_label' => '',
						'numero_compte' => (string) $account_product,
						'label_compte' => !empty($acc_info_prod['label']) ? $acc_info_prod['label'] : $langs->trans("SubventionProductAccount"),
						'label_operation' => $label,
						'montant' => $montant,
						'sens' => 'C',
						'debit' => 0,
						'credit' => $montant,
						'code_journal' => $journal,
						'journal_label' => $journal_label_formatted,
						'piece_num' => '',
						'import_key' => '',
						'fk_user_author' => $user->id,
						'entity' => $conf->entity,
					);
				} else { // csv
					$blocks[] = array(
						$docdate,
						$fin_static->ref,
						$account_receivable,
						$label,
						price($montant),
						'',
					);
					$blocks[] = array(
						$docdate,
						$fin_static->ref,
						$account_product,
						$label,
						'',
						price($montant),
					);
				}

				$element['blocks'][] = $blocks;
				$parameters['data'][] = $element;
			}
		}
		}

		// --- 2. Paiements (Encaissements - Banque / Trésorerie) ---
		if ($is_payment_journal) {
		$sqlp = "SELECT p.rowid, p.ref, p.datep, p.date_engagement, p.montant, p.fk_soc, p.fk_sub, p.fk_fin, p.fk_account, p.accounted, p.fk_bank";
		$sqlp .= " FROM ".MAIN_DB_PREFIX."subventions_paiement as p";
		$sqlp .= " WHERE p.entity IN (".getEntity('paiement@subventions').")";
		$sqlp .= " AND p.status > 0 AND p.montant > 0";
		if ($date_start && $date_end) {
			$sqlp .= " AND (";
			$sqlp .= "   (p.date_engagement IS NOT NULL AND p.date_engagement >= '".$this->db->idate($date_start)."' AND p.date_engagement <= '".$this->db->idate($date_end)."')";
			$sqlp .= "   OR (p.date_engagement IS NULL AND p.datep >= '".$this->db->idate($date_start)."' AND p.datep <= '".$this->db->idate($date_end)."')";
			$sqlp .= " )";
		}
		if ($in_bookkeeping == 'already') {
			$sqlp .= " AND (EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping ab WHERE ab.doc_type = 'subvention_paiement' AND ab.fk_doc = p.rowid AND ab.code_journal = '".$this->db->escape($journal)."') OR (p.fk_bank IS NOT NULL AND p.fk_bank > 0 AND EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping ab WHERE ab.doc_type = 'bank' AND ab.fk_doc = p.fk_bank)))";
		} elseif ($in_bookkeeping == 'notyet') {
			$sqlp .= " AND NOT EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping ab WHERE (ab.doc_type = 'subvention_paiement' AND ab.fk_doc = p.rowid AND ab.code_journal = '".$this->db->escape($journal)."') OR (p.fk_bank IS NOT NULL AND p.fk_bank > 0 AND ab.doc_type = 'bank' AND ab.fk_doc = p.fk_bank))";
		}
		$sqlp .= " ORDER BY p.rowid ASC";

		$resqlp = $this->db->query($sqlp);
		if ($resqlp) {
			$pay_static = new Paiement($this->db);
			$fin_static = new Financement($this->db);
			$sub_static = new Subvention($this->db);
			$soc_static = new Societe($this->db);

			while ($objp = $this->db->fetch_object($resqlp)) {
				// Account bank / treasury (Debit) & check target journal
				$account_bank = '512000';
				$account_bank_journal = $journal_payment;
				if (!empty($objp->fk_account)) {
					$sqlacc = "SELECT ba.account_number, j.code as journal_code FROM ".MAIN_DB_PREFIX."bank_account ba LEFT JOIN ".MAIN_DB_PREFIX."accounting_journal j ON j.rowid = ba.fk_accountancy_journal WHERE ba.rowid = ".((int) $objp->fk_account);
					$resacc = $this->db->query($sqlacc);
					if ($resacc && ($objacc = $this->db->fetch_object($resacc))) {
						if (!empty($objacc->account_number)) {
							$account_bank = $objacc->account_number;
						}
						if (!empty($objacc->journal_code)) {
							$account_bank_journal = $objacc->journal_code;
						}
					}
				}

				if (!empty($account_bank_journal) && $account_bank_journal != $journal) {
					continue;
				}

				$pay_static->fetch((int) $objp->rowid);
				$sub_static->fetch((int) $objp->fk_sub);
				if ($objp->fk_fin > 0) {
					$fin_static->fetch((int) $objp->fk_fin);
				}
				$socid = $objp->fk_soc > 0 ? $objp->fk_soc : ($fin_static->fk_soc > 0 ? $fin_static->fk_soc : 0);
				if ($socid > 0) {
					$soc_static->fetch((int) $socid);
				}

				$docdate = !empty($objp->date_engagement) ? $this->db->jdate($objp->date_engagement) : $this->db->jdate($objp->datep);
				$docdate_fmt = dol_print_date($docdate, 'day');

				// Account receivable (Credit)
				$account_receivable = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_CODE_RECEIVABLE_DEFAULT', '441000');
				if ($fin_static->fk_financeur > 0) {
					$sqlc = "SELECT accountancy_code_receivable, accountancy_code FROM ".MAIN_DB_PREFIX."c_subventions_financeur WHERE rowid = ".((int) $fin_static->fk_financeur);
					$resc = $this->db->query($sqlc);
					if ($resc && ($objc = $this->db->fetch_object($resc))) {
						if (!empty($objc->accountancy_code_receivable)) {
							$account_receivable = $objc->accountancy_code_receivable;
						} elseif (!empty($objc->accountancy_code)) {
							$account_receivable = $objc->accountancy_code;
						}
					}
				}

				$subledger = !empty($soc_static->code_compta_client) ? $soc_static->code_compta_client : '';
				$label = $langs->trans("SubventionPayment").': '.($sub_static->ref ? $sub_static->ref.' - ' : '').$pay_static->ref.($soc_static->name ? ' ('.$soc_static->name.')' : '');
				$montant = (float) $objp->montant;

				$acc_info_bank = $object->getAccountingAccountInfos($account_bank);
				$acc_info_rec = $object->getAccountingAccountInfos($account_receivable);

				$element = array(
					'ref' => dol_trunc($pay_static->ref, 16, 'right', 'UTF-8', 1),
					'error' => '',
					'blocks' => array(),
				);

				$blocks = array();
				if ($type == 'view') {
					$piece_link = $pay_static->getNomUrl(1);
					$blocks[] = array(
						'date' => $docdate_fmt,
						'piece' => $piece_link,
						'account_accounting' => length_accountg($account_bank),
						'subledger_account' => '',
						'label_operation' => $label,
						'debit' => price($montant),
						'credit' => '',
					);
					$blocks[] = array(
						'date' => $docdate_fmt,
						'piece' => $piece_link,
						'account_accounting' => length_accountg($account_receivable),
						'subledger_account' => $subledger,
						'label_operation' => $label,
						'debit' => '',
						'credit' => price($montant),
					);
				} elseif ($type == 'bookkeeping') {
					$blocks[] = array(
						'doc_date' => $docdate,
						'date_lim_reglement' => '',
						'doc_ref' => ($sub_static->ref ? $sub_static->ref.' / ' : '').$pay_static->ref,
						'date_creation' => $now,
						'doc_type' => 'subvention_paiement',
						'fk_doc' => $pay_static->id,
						'fk_docdet' => $pay_static->id,
						'thirdparty_code' => $soc_static->code_client,
						'subledger_account' => '',
						'subledger_label' => '',
						'numero_compte' => (string) $account_bank,
						'label_compte' => !empty($acc_info_bank['label']) ? $acc_info_bank['label'] : $langs->trans("SubventionBankAccount"),
						'label_operation' => $label,
						'montant' => $montant,
						'sens' => 'D',
						'debit' => $montant,
						'credit' => 0,
						'code_journal' => $journal,
						'journal_label' => $journal_label_formatted,
						'piece_num' => '',
						'import_key' => '',
						'fk_user_author' => $user->id,
						'entity' => $conf->entity,
					);
					$blocks[] = array(
						'doc_date' => $docdate,
						'date_lim_reglement' => '',
						'doc_ref' => ($sub_static->ref ? $sub_static->ref.' / ' : '').$pay_static->ref,
						'date_creation' => $now,
						'doc_type' => 'subvention_paiement',
						'fk_doc' => $pay_static->id,
						'fk_docdet' => $pay_static->id,
						'thirdparty_code' => $soc_static->code_client,
						'subledger_account' => $subledger,
						'subledger_label' => $soc_static->name,
						'numero_compte' => (string) $account_receivable,
						'label_compte' => !empty($acc_info_rec['label']) ? $acc_info_rec['label'] : $langs->trans("SubventionReceivableAccount"),
						'label_operation' => $label,
						'montant' => $montant,
						'sens' => 'C',
						'debit' => 0,
						'credit' => $montant,
						'code_journal' => $journal,
						'journal_label' => $journal_label_formatted,
						'piece_num' => '',
						'import_key' => '',
						'fk_user_author' => $user->id,
						'entity' => $conf->entity,
					);
				} else { // csv
					$blocks[] = array(
						$docdate,
						$pay_static->ref,
						$account_bank,
						$label,
						price($montant),
						'',
					);
					$blocks[] = array(
						$docdate,
						$pay_static->ref,
						$account_receivable,
						$label,
						'',
						price($montant),
					);
				}

				$element['blocks'][] = $blocks;
				$parameters['data'][] = $element;
			}
		}
		}

		return 0;
	}
}
