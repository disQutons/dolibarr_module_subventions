<?php
/* Copyright (C) 2026		Romain MP		<romain.mp@gmail.com>
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
 * \file    core/triggers/interface_30_modSubventions_SubventionsTriggers.class.php
 * \ingroup subventions
 * \brief   Triggers for Subventions module (enables automatic event recording in Agenda)
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';

/**
 * Class of triggers for Subventions module
 */
class InterfaceSubventionsTriggers extends DolibarrTriggers
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "subventions";
		$this->description = "Triggers of Subventions module for Agenda event recording and workflow";
		$this->version = '1.3.0';
		$this->picto = 'subvention@subventions';
	}

	/**
	 * Function called when a Dolibarr business event is done.
	 * All functions "runTrigger" are triggered if file is inside directory htdocs/core/triggers or htdocs/module/code/triggers
	 *
	 * @param string        $action     Event action code
	 * @param CommonObject  $object     Object affected by event
	 * @param User          $user       Object user
	 * @param Translate     $langs      Object langs
	 * @param Conf          $conf       Object conf
	 * @return int                      Return integer <0 if KO, 0 if no trigger ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if (!isModEnabled('subventions')) {
			return 0;
		}

		if (!is_object($object)) {
			return 0;
		}

		// Invalidate event counter cache if an actioncomm linked to our objects is created/modified/deleted
		if (in_array($action, array('ACTION_CREATE', 'ACTION_MODIFY', 'ACTION_DELETE'))) {
			if (!empty($object->fk_element) && !empty($object->elementtype)) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
				if (in_array($object->elementtype, array('subvention', 'subvention@subventions'))) {
					dol_delcache('count_events_subvention_'.$object->fk_element);
				} elseif (in_array($object->elementtype, array('financement', 'financement@subventions'))) {
					dol_delcache('count_events_financement_'.$object->fk_element);
				} elseif (in_array($object->elementtype, array('paiement', 'paiement@subventions'))) {
					dol_delcache('count_events_paiement_'.$object->fk_element);
				}
			}
			return 0;
		}

		// Only handle Subventions module events
		if (!preg_match('/^(SUBVENTION_|SUBVENTIONS_SUBVENTION_|FINANCEMENT_|SUBVENTIONS_FINANCEMENT_|PAIEMENT_)/', $action)) {
			return 0;
		}

		// Normalize action code (strip legacy SUBVENTIONS_ prefix)
		$cleanAction = preg_replace('/^SUBVENTIONS_/', '', $action);

		// Synchronize config keys between clean and prefixed codes for ActionsAuto compatibility
		if (getDolGlobalString('MAIN_AGENDA_ACTIONAUTO_'.$cleanAction) && !getDolGlobalString('MAIN_AGENDA_ACTIONAUTO_'.$action)) {
			$conf->global->{'MAIN_AGENDA_ACTIONAUTO_'.$action} = getDolGlobalString('MAIN_AGENDA_ACTIONAUTO_'.$cleanAction);
		} elseif (getDolGlobalString('MAIN_AGENDA_ACTIONAUTO_'.$action) && !getDolGlobalString('MAIN_AGENDA_ACTIONAUTO_'.$cleanAction)) {
			$conf->global->{'MAIN_AGENDA_ACTIONAUTO_'.$cleanAction} = getDolGlobalString('MAIN_AGENDA_ACTIONAUTO_'.$action);
		} elseif (!getDolGlobalString('MAIN_AGENDA_ACTIONAUTO_'.$cleanAction) && !getDolGlobalString('MAIN_AGENDA_ACTIONAUTO_'.$action)) {
			// Auto-initialize triggers if missing from database
			dol_include_once('/subventions/core/modules/modSubventions.class.php');
			$modSub = new modSubventions($this->db);
			if (method_exists($modSub, 'checkAndInitTriggers')) {
				$modSub->checkAndInitTriggers();
			}
			$conf->global->{'MAIN_AGENDA_ACTIONAUTO_'.$cleanAction} = '1';
			$conf->global->{'MAIN_AGENDA_ACTIONAUTO_'.$action} = '1';
		}

		// Load required language files
		$langs->loadLangs(array("subventions@subventions", "agenda", "other", "companies", "bills", "projects"));

		// Special handling for Subvention ventilation (SubventionProject)
		if (preg_match('/^SUBVENTION_VENTILATION_/', $cleanAction) || (property_exists($object, 'element') && $object->element == 'subventionproject')) {
			if (empty($object->socid) && !empty($object->fk_subvention)) {
				dol_include_once('/subventions/class/subvention.class.php');
				$subObj = new Subvention($this->db);
				if ($subObj->fetch($object->fk_subvention) > 0) {
					$object->socid = $subObj->fk_soc;
					if (empty($object->ref)) {
						$object->ref = $subObj->ref;
					}
				}
			}

			// Point object to parent subvention so ActionsAuto links the event to the subvention
			if (!empty($object->fk_subvention)) {
				$object->orig_id = $object->id;
				$object->orig_element = $object->element;
				$object->id = $object->fk_subvention;
				$object->element = 'subvention';
			}
		}

		// 1. Ensure thirdparty ID (socid) is set on object
		if (empty($object->socid) && !empty($object->fk_soc)) {
			$object->socid = $object->fk_soc;
		}

		// 2. Ensure project ID (fk_project) is set on object (inherit from parent subvention if missing)
		if (empty($object->fk_project) && !empty($object->fk_sub)) {
			dol_include_once('/subventions/class/subvention.class.php');
			$subObj = new Subvention($this->db);
			if ($subObj->fetch($object->fk_sub) > 0) {
				if (!empty($subObj->fk_project)) {
					$object->fk_project = $subObj->fk_project;
				}
				if (empty($object->socid) && !empty($subObj->fk_soc)) {
					$object->socid = $subObj->fk_soc;
				}
			}
		}

		// 3. Prepare label ($object->actionmsg2) for Agenda ActionsAuto
		$ref = (empty($object->newref) ? (string) $object->ref : $object->newref);
		if (empty($object->actionmsg2)) {
			$transKey = $cleanAction."InDolibarr";
			$label = $langs->transnoentities($transKey, $ref);
			if ($label == $transKey) {
				// Fallback to action code with ref
				$altKey = $action."InDolibarr";
				$label = $langs->transnoentities($altKey, $ref);
				if ($label == $altKey) {
					$label = $cleanAction.' '.$ref;
				}
			}
			$object->actionmsg2 = $label;
		}

		// 4. Prepare detailed description ($object->actionmsg) for Agenda ActionsAuto
		if (empty($object->actionmsg)) {
			$desc = $object->actionmsg2;

			if (preg_match('/^SUBVENTION_VENTILATION_/', $cleanAction)) {
				if (!empty($object->fk_project)) {
					require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
					$proj = new Project($this->db);
					if ($proj->fetch($object->fk_project) > 0) {
						$desc .= "\n".$langs->transnoentities("Project").": ".$proj->ref.(!empty($proj->title) ? ' - '.$proj->title : '');
					}
				}
				if (!empty($object->annee)) {
					$desc .= "\n".$langs->transnoentities("Year").": ".$object->annee;
				}
				if (!empty($object->nb_mois)) {
					$desc .= "\n".$langs->transnoentities("DurationMonths").": ".$object->nb_mois;
				}
				if (isset($object->amount) && $object->amount !== '' && $object->amount > 0) {
					$desc .= "\n".$langs->transnoentities("AllocatedAmount").": ".price($object->amount);
				}
				if (!empty($object->note)) {
					$desc .= "\n".$langs->transnoentities("Note").": ".$object->note;
				}
				if (!empty($object->autorepart_count)) {
					$desc .= "\n".$langs->transnoentities("NumberOfYears").": ".$object->autorepart_count;
				}
				if (!empty($object->autorepart_years) && is_array($object->autorepart_years)) {
					$desc .= "\n".$langs->transnoentities("Years").": ".implode(', ', $object->autorepart_years);
				}
				if (!empty($object->autorepart_total)) {
					$desc .= "\n".$langs->transnoentities("TotalVentilated").": ".price($object->autorepart_total);
				}
			} elseif (property_exists($object, 'element')) {
				if ($object->element == 'subvention') {
					if (!empty($object->label)) {
						$desc .= "\n".$langs->transnoentities("Label").": ".$object->label;
					}
					if (!empty($object->montant_dem)) {
						$desc .= "\n".$langs->transnoentities("AmountRequested").": ".price($object->montant_dem);
					}
					if (!empty($object->montant_acc)) {
						$desc .= "\n".$langs->transnoentities("AmountAccepted").": ".price($object->montant_acc);
					}
					if (!empty($object->montant_fin)) {
						$desc .= "\n".$langs->transnoentities("AmountFinanced").": ".price($object->montant_fin);
					}
				} elseif ($object->element == 'financement') {
					if (!empty($object->montant_dem)) {
						$desc .= "\n".$langs->transnoentities("AmountRequested").": ".price($object->montant_dem);
					}
					if (!empty($object->montant_acc)) {
						$desc .= "\n".$langs->transnoentities("AmountAccepted").": ".price($object->montant_acc);
					}
				} elseif ($object->element == 'paiement') {
					if (!empty($object->montant)) {
						$desc .= "\n".$langs->transnoentities("Amount").": ".price($object->montant);
					}
					if (!empty($object->datep)) {
						$desc .= "\n".$langs->transnoentities("DatePayment").": ".dol_print_date($object->datep, 'day');
					}
				}
			}

			$object->actionmsg = $desc;
		}

		// Invalidate event counter cache for current object and parents
		require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
		if (!empty($object->id)) {
			if (property_exists($object, 'element')) {
				if ($object->element == 'subvention') {
					dol_delcache('count_events_subvention_'.$object->id);
				} elseif ($object->element == 'financement') {
					dol_delcache('count_events_financement_'.$object->id);
				} elseif ($object->element == 'paiement') {
					dol_delcache('count_events_paiement_'.$object->id);
				}
			}
		}
		if (!empty($object->fk_subvention)) {
			dol_delcache('count_events_subvention_'.$object->fk_subvention);
		}
		if (!empty($object->fk_sub)) {
			dol_delcache('count_events_subvention_'.$object->fk_sub);
		}

		return 1;
	}
}
