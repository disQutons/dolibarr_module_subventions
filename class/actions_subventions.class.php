<?php
/* Copyright (C) 2023		Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2025		François Brichart			<francois@disqutons.fr>
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
require_once DOL_DOCUMENT_ROOT.'/custom/subventions/class/subvention.class.php';

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
			$this->results = array(
				'subvention' => array(
				'name' => "Subventions",
				'title' => "ListSubventionsAssociatedProject",
				'class' => 'Subvention',
				'table' => 'subventions_subvention',
				'datefieldname' => 'date_creation',
				'margin' => 'add',
				'project_field' => 'fk_project',
				'url' => DOL_URL_ROOT.'/custom/subventions/subvention_list.php?fk_project='.$object->id, // URL pour lister les subventions
				'urlnew' => DOL_URL_ROOT.'/custom/subventions/subvention_card.php?action=create&origin=project&originid='.$id.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
				'lang' => 'subventions',
				'buttonnew' => 'AddSubvention',
				'testnew' => $user->hasRight('subventions', 'subvention', 'write'),
				'test' => isModEnabled('subventions') && $user->hasRight('subventions', 'subvention', 'read'),
				),
			);
        	return 0;
    	}
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
				if ($datacount > 0) {
					$parameters['head'][$counter][0] = dol_buildpath('/subventions/subvention_list.php', 1) . '?socid='.$id.'&search_fk_soc='.$id;
					$parameters['head'][$counter][1] = $langs->trans('Subventions');
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

				if ($datacount > 0) {
					$parameters['head'][$counter][0] = dol_buildpath('/subventions/financement_list.php', 1) . '?socid='.$id.'&search_fk_soc='.$id;
					$parameters['head'][$counter][1] = $langs->trans('Financements');
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
}
