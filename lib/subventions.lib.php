<?php
/* Copyright (C) 2025		François Brichart			<francois@disqutons.fr>
 * Copyright (C) 2026		Romain MP					<romain.mp@gmail.com>
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
 * \file    subventions/lib/subventions.lib.php
 * \ingroup subventions
 * \brief   Library files with common functions for Subventions
 */


/*
// FBR récupération des erreurs php
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/

if (!function_exists('subventionsAdminPrepareHead')) {
	/**
	 * Prepare admin pages header
	 *
	 * @return array<array{string,string,string}>
	 */
	function subventionsAdminPrepareHead()
	{
		global $langs, $conf;
		global $db;

		$langs->load("subventions@subventions");

		// Ensure agenda triggers and default constants are registered
		if (!getDolGlobalString('MAIN_AGENDA_ACTIONAUTO_SUBVENTION_VENTILATION_CREATE')) {
			dol_include_once('/subventions/core/modules/modSubventions.class.php');
			$modSub = new modSubventions($db);
			if (method_exists($modSub, 'checkAndInitTriggers')) {
				$modSub->checkAndInitTriggers();
			}
		}

		$h = 0;
		$head = array();

		$head[$h][0] = dol_buildpath("/subventions/admin/setup.php", 1);
		$head[$h][1] = $langs->trans("Settings");
		$head[$h][2] = 'settings';
		$h++;

		
		// Extra fields subventions
		$extrafields = new ExtraFields($db);
		$extrafields->fetch_name_optionals_label('subvention');

		$head[$h][0] = dol_buildpath("/subventions/admin/subvention_extrafields.php", 1);
		$head[$h][1] = $langs->trans("AttributsSuppSubsidy");
		$nbExtrafields = (!empty($extrafields->attributes['subvention']['label']) && is_countable($extrafields->attributes['subvention']['label'])) ? count($extrafields->attributes['subvention']['label']) : 0;
		if ($nbExtrafields > 0) {
			$head[$h][1] .= ' <span class="badge">' . $nbExtrafields . '</span>';
		}
		$head[$h][2] = 'subvention_extrafields';
		$h++;
		
		// Extra fields financements
		$extrafields = new ExtraFields($db);
		$extrafields->fetch_name_optionals_label('financement');

		$head[$h][0] = dol_buildpath("/subventions/admin/financement_extrafields.php", 1);
		$head[$h][1] = $langs->trans("AttributsSuppAddFunding");
		$nbExtrafields = (!empty($extrafields->attributes['financement']['label']) && is_countable($extrafields->attributes['financement']['label'])) ? count($extrafields->attributes['financement']['label']) : 0;
		if ($nbExtrafields > 0) {
			$head[$h][1] .= ' <span class="badge">' . $nbExtrafields . '</span>';
		}
		$head[$h][2] = 'financement_extrafields';
		$h++;
		
		// Extra fields paiements
		$extrafields = new ExtraFields($db);
		$extrafields->fetch_name_optionals_label('paiement');

		$head[$h][0] = dol_buildpath("/subventions/admin/paiement_extrafields.php", 1);
		$head[$h][1] = $langs->trans("AttributsSuppPayment");
		$nbExtrafields = (!empty($extrafields->attributes['paiement']['label']) && is_countable($extrafields->attributes['paiement']['label'])) ? count($extrafields->attributes['paiement']['label']) : 0;
		if ($nbExtrafields > 0) {
			$head[$h][1] .= ' <span class="badge">' . $nbExtrafields . '</span>';
		}
		$head[$h][2] = 'paiement_extrafields';
		$h++;
		

		$head[$h][0] = dol_buildpath("/subventions/admin/about.php", 1);
		$head[$h][1] = $langs->trans("About");
		$head[$h][2] = 'about';
		$h++;

		// Show more tabs from modules
		// Entries must be declared in modules descriptor with line
		//$this->tabs = array(
		//	'entity:+tabname:Title:@subventions:/subventions/mypage.php?id=__ID__'
		//); // to add new tab
		//$this->tabs = array(
		//	'entity:-tabname:Title:@subventions:/subventions/mypage.php?id=__ID__'
		//); // to remove a tab
		complete_head_from_modules($conf, $langs, null, $head, $h, 'subventions@subventions');

		complete_head_from_modules($conf, $langs, null, $head, $h, 'subventions@subventions', 'remove');

		return $head;
	}
}


if (!function_exists('majMontantsFinancementSubvention')) {
	// Mise à jour des montants des paiements liés aux financements et subventions
	function majMontantsFinancementSubvention($object) {
    global $db, $user, $langs;

	// Vérifie le type de l'objet
    if (is_a($object, 'paiement')) {
        $fk_sub = $object->fk_sub;
        $fk_fin = $object->fk_fin;
    } elseif (is_a($object, 'financement')) {
        $fk_sub = $object->fk_sub;
        $fk_fin = $object->id;
    } elseif (is_a($object, 'subvention')) {
        $fk_sub = $object->id;
        $fk_fin = null;
    } else {
        throw new Exception("Type d'objet non supporté : ".get_class($object));
    }

    // On fait un test pour mettre à jour uniquement ce qui doit l'être
    if (empty($fk_sub)) {
        $majsub = 0;
    }
    else {
        $majsub = 1;
    }
    if (empty($fk_fin)) {
        $majfin = 0;
    }
    else {
        $majfin = 1;
    }

    $db->begin();
    try {
		// MAJ FINANCEMENT
        if ($majfin) {
            // Récupère le montant accordé (montant_acc) du financement
            $resql = $db->query("SELECT montant_acc, montant_dem FROM ".MAIN_DB_PREFIX."subventions_financement WHERE rowid = ".$fk_fin);
            if (!$resql) {
                throw new Exception("Erreur SQL : ".$db->lasterror());
            }
            $obj = $db->fetch_object($resql);
            if ($obj) {
                $m_acc = $obj->montant_acc;
                $m_dem = (float) $obj->montant_dem;

                // Calcule la somme des montants reçus (paiements) pour ce financement
                $resql = $db->query("SELECT SUM(montant) as total FROM ".MAIN_DB_PREFIX."subventions_paiement WHERE fk_fin = ".$fk_fin);
                if (!$resql) {
                    throw new Exception("Erreur SQL : ".$db->lasterror());
                }
                $obj2 = $db->fetch_object($resql);
                $m_fin = (float) $obj2->total;

                // Calcule le montant attendu (montant_acc - somme des paiements) ne peut pas être négatif
                $m_att = ($m_acc - $m_fin < 0) ? 0 : $m_acc - $m_fin;
                
                // Si aucun montant n'a été saisi ou qu'aucune demande n'avait été faite, on ne calcule rien
                if (is_null($m_acc) || (is_null($m_dem) && $m_acc > 0)){
                    $m_ref = 0;    
                }
                else{
                    // Calcule le montant refusé (montant_dem - montant_acc) ne peut pas être négatif
                    $m_ref = ($m_dem - $m_acc < 0) ? 0 : $m_dem - $m_acc;
                }

                // Màj financement
                $sql = "UPDATE ".MAIN_DB_PREFIX."subventions_financement SET ";
                $sql .= "montant_fin = ".$m_fin.", ";
                $sql .= "montant_ref = ".$m_ref.", ";
                $sql .= "montant_att = ".$m_att." ";
                $sql .= "WHERE rowid = ".$fk_fin;
                $resql = $db->query($sql);
                if (!$resql) {
                    throw new Exception("Erreur SQL UPDATE : ".$db->lasterror());
                }
            }
        }

		// MAJ SUBVENTION
        if ($majsub){
            // Récupère les montants demandés, accordés, financés et refusés de la subvention
            $sql = "SELECT COALESCE(SUM(montant_dem),0) as m_dem, 
                        COALESCE(SUM(montant_acc),0) as m_acc,
                        COALESCE(SUM(montant_ref),0) as m_ref,
                        COALESCE(SUM(montant_fin),0) as m_fin 
                        FROM ".MAIN_DB_PREFIX."subventions_financement WHERE fk_sub = ".$fk_sub;

            $resql = $db->query($sql);
            
            if (!$resql) {
                throw new Exception("Erreur SQL : ".$db->lasterror());
            }
            $obj = $db->fetch_object($resql);
            $m_dem = (float) $obj->m_dem;
            $m_acc = (float) $obj->m_acc;
            $m_fin = (float) $obj->m_fin;
            $m_ref = (float) $obj->m_ref;

            // Calcule le montant attendu (montant_acc - somme des paiements)
            $m_att = $m_acc - $m_fin;

            // TODO Vérifier bon fonctionnement
            /*$valeur_total_ht = ${getDolGlobalString('SUBVENTIONS_PROJECT_MONTANT_HT')};
            $valeur_total_ttc = ${getDolGlobalString('SUBVENTIONS_PROJECT_MONTANT_TTC')};

            // Màj subvention
            $sql = "UPDATE ".MAIN_DB_PREFIX."subventions_subvention SET ";
            if (!is_null($valeur_total_ht)) {
                $sql .= " total_ht = ".$valeur_total_ht.", ";
            }
            if (!is_null($valeur_total_ttc)) {
                $sql .= " total_ttc = ".$valeur_total_ttc.", ";
            }*/

            $valeur_total_ht = getDolGlobalString('SUBVENTIONS_PROJECT_MONTANT_HT');
            $valeur_total_ttc = getDolGlobalString('SUBVENTIONS_PROJECT_MONTANT_TTC');

            // Màj subvention
            $sql = "UPDATE ".MAIN_DB_PREFIX."subventions_subvention SET ";
            if (!is_null($valeur_total_ht)) {
                $sql .= " total_ht = ".$valeur_total_ht.", ";
            }
            if (!is_null($valeur_total_ttc)) {
                $sql .= " total_ttc = ".$valeur_total_ttc.", ";
            }
            $sql .= " montant_fin = ".$m_fin.", ";
            $sql .= " montant_ref = ".$m_ref.", ";
            $sql .= " montant_dem = ".$m_dem.", ";
            $sql .= " montant_acc = ".$m_acc.", ";
            $sql .= " montant_att = ".$m_att." ";
            $sql .= " WHERE rowid = ".$fk_sub;
            $resql = $db->query($sql);
            if (!$resql) {
                throw new Exception("Erreur SQL UPDATE : ".$db->lasterror());
            }
        }

        $db->commit();

        // Màj statut de la subvention
        majstatut($object);

        return 1;
    } catch (Exception $e) {
        $db->rollback();
        dol_syslog("Erreur majMontantsFinancementSubvention: ".$e->getMessage(), LOG_ERR);
        return -1;
    }
}
}


if (!function_exists('majMontantsTTCHT')) {
// Mise à jour des champs montantHT et montantTTC pour les projets, en lien avec setup.php
function majMontantsTTCHT(){
    global $db, $user, $langs;
     
    $valeur_total_ht = getDolGlobalString('SUBVENTIONS_PROJECT_MONTANT_HT');
    $valeur_total_ttc = getDolGlobalString('SUBVENTIONS_PROJECT_MONTANT_TTC');

    // Màj subvention
    $sql = "UPDATE ".MAIN_DB_PREFIX."subventions_subvention SET ";
    $sql .= " total_ht = ".$valeur_total_ht.", ";
    $sql .= " total_ttc = ".$valeur_total_ttc;
    $resql = $db->query($sql);

    if (!$resql) {
        //throw new Exception("Erreur SQL UPDATE : ".$db->lasterror());
    }
}
}

if (!function_exists('majstatut')) {
// Mis à jour du statut de la subvention
function majstatut ($object){
    global $db, $user, $langs;

    // Vérifie le type de l'objet
    if (is_a($object, 'subvention')) {
        $sub = $object->id;
    } elseif (is_a($object, 'financement')) {
        $sub = $object->fk_sub;
    } elseif (is_a($object, 'paiement')) {
        $sub = $object->fk_sub;
    } else {
        throw new Exception("Type d'objet non supporté : ".get_class($object));
    }

    if (empty($sub)) {
        return 0;
    }

    $db->begin();
    try {
	    // Récupère les informations de la subvention
        $resql = $db->query("SELECT montant_dem, montant_acc, montant_fin, montant_att, montant_ref, s.status FROM ".MAIN_DB_PREFIX."subventions_subvention as s WHERE rowid = ".$sub);
        if (!$resql) {
            throw new Exception("Erreur SQL : ".$db->lasterror());
        }
        $obj = $db->fetch_object($resql);
        $m_acc_is_null = is_null($obj->montant_acc);
        $m_dem = (float) $obj->montant_dem;
        $m_acc = (float) $obj->montant_acc;
        $m_fin = (float) $obj->montant_fin;
        $m_att = (float) $obj->montant_att;
        $m_ref = (float) $obj->montant_ref;
        $stat = (float) $obj->status;
        
        if ($stat == 0 || $stat == 4 || $stat == 5 || $stat == 7 || $stat == 9) {
            // 0: Brouillon
            // 4: Bilan déposé
            // 5: Clôturée
            // 7: Archivée
            // 9: Annulée
            $db->commit();
            return 0;
        }
            else {
            // Calcul du statut correspondant
            if ($m_acc > 0 && $m_att > 0){
                $stat = 2; // Acceptée
            }
            elseif ($m_acc == $m_fin && $m_att == 0 && $m_acc > 0){
                $stat = 3; // Financée
            }
            elseif (($m_dem == 0 && $m_acc_is_null) || ($m_dem > 0 && $m_acc == 0 && $m_ref == 0)){
                $stat = 1; // Validé
            }
            elseif (($m_dem == 0 && $m_acc == 0) || ($m_acc == 0 && $m_ref == $m_dem && $m_dem > 0)){
                $stat = 6; // Refusé
            }
        
            $old_stat = (int) $obj->status;
            if ($stat != $old_stat) {
                // Màj statut de la subvention
                $sql = "UPDATE ".MAIN_DB_PREFIX."subventions_subvention SET ";
                $sql .= " status = ".$stat." ";
                $sql .= " WHERE rowid = ".$sub;
                $resql = $db->query($sql);
                if (!$resql) {
                    throw new Exception("Erreur SQL UPDATE : ".$db->lasterror());
                }

                $db->commit();

                // Lancement du trigger si le statut a changé automatiquement
                dol_include_once('/subventions/class/subvention.class.php');
                $subObj = new Subvention($db);
                if ($subObj->fetch($sub) > 0) {
                    if ($stat == Subvention::STATUS_ACCEPTED) {
                        $subObj->call_trigger('SUBVENTION_ACCEPT', $user);
                    } elseif ($stat == Subvention::STATUS_FINANCED) {
                        $subObj->call_trigger('SUBVENTION_FINANCED', $user);
                    } elseif ($stat == Subvention::STATUS_REFUSED) {
                        $subObj->call_trigger('SUBVENTION_REFUSE', $user);
                    }
                }

                return 1;
            } else {
                $db->commit();
                return 0;
            }
        }
    }
    catch (Exception $e) {
        $db->rollback();
        dol_syslog("Erreur majMontantsFinancementSubvention: ".$e->getMessage(), LOG_ERR);
        return -1;
    }
}
}

if (!function_exists('refuseSub')) {
// Mettre au refus tous les financeurs n'ayant pas accepté
function refuseSub ($object){
    global $db, $user, $langs;

    // Vérifie le type de l'objet
    if (is_a($object, 'subvention')) {
        $sub = $object->id;
    } else {
        throw new Exception("Type d'objet non supporté : ".get_class($object));
    }

    $db->begin();
    try {
	    // Récupère les informations du financement
        $resql = $db->query("SELECT rowid, montant_dem, montant_acc, montant_fin, montant_att, montant_ref FROM ".MAIN_DB_PREFIX."subventions_financement WHERE fk_sub = ".$sub);
        if (!$resql) {
            throw new Exception("Erreur SQL : ".$db->lasterror());
        }
    
        while ($obj = $db->fetch_object($resql)) {
            if (empty($obj->montant_acc)) {
                // Créer un objet Dolibarr pour la mise à jour
                dol_include_once('/custom/subventions/class/financement.class.php');
                $financement = new Financement($db);
                $financement->fetch($obj->rowid);
                
                // Mettre à jour les propriétés
                $financement->montant_ref = $obj->montant_dem;
                $financement->montant_acc = 0;
                $financement->montant_att = 0;
                
                // Sauvegarder en base de données
                $result = $financement->update($user);

                if ($result < 0) {
                    throw new Exception("Erreur lors de la mise à jour : " . $financement->error);
                }
            }
        }

        $db->commit();

    }
    catch (Exception $e) {
        $db->rollback();
        dol_syslog("Erreur majMontantsFinancementSubvention: ".$e->getMessage(), LOG_ERR);
        return -1;
    
    }
}
}

if (!function_exists('getSubventionsTransferJournalUrl')) {
	/**
	 * Get URL to accounting transfer journal for a financement or paiement
	 *
	 * @param  string                  $type   'financement' or 'paiement'
	 * @param  CommonObject|object|null $object Financement or Paiement object (optional)
	 * @return string                          URL to transfer journal
	 */
	function getSubventionsTransferJournalUrl($type = 'financement', $object = null)
	{
		global $db, $conf;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

		$journal_id = 0;
		$journal_nature = 1;
		$journal_code = '';

		if ($type == 'paiement') {
			$journal_nature = 4;
			// If payment is linked to a bank account, check its journal first
			if (!empty($object) && !empty($object->fk_account)) {
				$sqlacc = "SELECT ba.fk_accountancy_journal, j.nature, j.code FROM ".MAIN_DB_PREFIX."bank_account ba LEFT JOIN ".MAIN_DB_PREFIX."accounting_journal j ON j.rowid = ba.fk_accountancy_journal WHERE ba.rowid = ".((int) $object->fk_account);
				$resacc = $db->query($sqlacc);
				if ($resacc && ($objacc = $db->fetch_object($resacc))) {
					if (!empty($objacc->fk_accountancy_journal)) {
						$journal_id = (int) $objacc->fk_accountancy_journal;
						$journal_nature = (int) $objacc->nature;
						$journal_code = $objacc->code;
					}
				}
			}
			if (empty($journal_id)) {
				$journal_code = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_JOURNAL_PAYMENT', 'BQ');
			}
		} else {
			$journal_code = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_JOURNAL', 'OD');
		}

		if (empty($journal_id) && !empty($journal_code)) {
			$sqlj = "SELECT rowid, nature, code FROM ".MAIN_DB_PREFIX."accounting_journal WHERE code = '".$db->escape($journal_code)."' AND active = 1";
			$resj = $db->query($sqlj);
			if ($resj && ($objj = $db->fetch_object($resj))) {
				$journal_id = (int) $objj->rowid;
				$journal_nature = (int) $objj->nature;
			}
		}

		// Fallback if not found
		if (empty($journal_id)) {
			$target_nature = ($type == 'paiement') ? 4 : 1;
			$sqlj = "SELECT rowid, nature, code FROM ".MAIN_DB_PREFIX."accounting_journal WHERE nature = ".((int) $target_nature)." AND active = 1 ORDER BY rowid ASC";
			$resj = $db->query($sqlj);
			if ($resj && ($objj = $db->fetch_object($resj))) {
				$journal_id = (int) $objj->rowid;
				$journal_nature = (int) $objj->nature;
			}
		}

		// Determine prefix (nature 4 with bank line = bank, otherwise various)
		$journalNaturePrefixUrl = ($journal_nature == 4 && !empty($object) && !empty($object->fk_bank)) ? 'bank' : 'various';

		$url = DOL_URL_ROOT.'/accountancy/journal/'.$journalNaturePrefixUrl.'journal.php?mainmenu=accountancy&leftmenu=accountancy_transfer_journal';
		if ($journal_id > 0) {
			$url .= '&id_journal='.$journal_id;
		}

		// Date parameters for filtering transfer journal to the document period
		$docdate = null;
		if (!empty($object)) {
			if ($type == 'paiement') {
				$docdate = !empty($object->date_engagement) ? $object->date_engagement : (!empty($object->datep) ? $object->datep : dol_now());
			} else {
				$docdate = !empty($object->date_engagement) ? $object->date_engagement : (!empty($object->date_creation) ? $object->date_creation : dol_now());
			}
		}
		if (!empty($docdate)) {
			$sm = dol_print_date($docdate, '%m');
			$sy = dol_print_date($docdate, '%Y');
			$ld = dol_print_date(dol_get_last_day((int) $sy, (int) $sm, false), '%d');
			$url .= '&date_startday=1&date_startmonth='.$sm.'&date_startyear='.$sy.'&date_endday='.$ld.'&date_endmonth='.$sm.'&date_endyear='.$sy;
		}

		return $url;
	}
}

if (!function_exists('syncSubventionsAccountedStatus')) {
	/**
	 * Synchronize accounted flag of a financement or paiement with accounting_bookkeeping
	 *
	 * @param  string              $type   'financement' or 'paiement'
	 * @param  CommonObject|object $object Financement or Paiement object
	 * @return int                         Current accounted status (0 or 1)
	 */
	function syncSubventionsAccountedStatus($type, &$object)
	{
		global $db;

		if (empty($object) || empty($object->id)) {
			return 0;
		}

		if ($type == 'financement') {
			$sql = "SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping WHERE doc_type IN ('subvention', 'subvention_financement') AND fk_doc = ".((int) $object->id)." LIMIT 1";
			$res = $db->query($sql);
			$is_accounted = ($res && $db->num_rows($res) > 0) ? 1 : 0;
			if ((int) $object->accounted !== $is_accounted) {
				$object->accounted = $is_accounted;
				$db->query("UPDATE ".MAIN_DB_PREFIX."subventions_financement SET accounted = ".$is_accounted." WHERE rowid = ".((int) $object->id));
			}
			return $is_accounted;
		} elseif ($type == 'paiement') {
			$sql = "SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping WHERE (doc_type = 'subvention_paiement' AND fk_doc = ".((int) $object->id).")";
			if (!empty($object->fk_bank)) {
				$sql .= " OR (doc_type = 'bank' AND fk_doc = ".((int) $object->fk_bank).")";
			}
			$sql .= " LIMIT 1";
			$res = $db->query($sql);
			$is_accounted = ($res && $db->num_rows($res) > 0) ? 1 : 0;
			if ((int) $object->accounted !== $is_accounted) {
				$object->accounted = $is_accounted;
				$db->query("UPDATE ".MAIN_DB_PREFIX."subventions_paiement SET accounted = ".$is_accounted." WHERE rowid = ".((int) $object->id));
			}
			if (empty($is_accounted) && !empty($object->fk_bank) && isModEnabled('banque') && function_exists('migrateSubventionsBankPaymentsToVarious')) {
				migrateSubventionsBankPaymentsToVarious($db, (int) $object->id);
			}
			return $is_accounted;
		}

		return 0;
	}
}

if (!function_exists('migrateSubventionsBankPaymentsToVarious')) {
	/**
	 * Migrate legacy subvention bank payments (llx_bank + company link) to Dolibarr PaymentVarious
	 *
	 * @param  DoliDB $db          Database handler
	 * @param  int    $paiement_id Optional Paiement ID to restrict migration to a single record
	 * @return int                 Number of migrated bank lines if >=0, <0 if KO
	 */
	function migrateSubventionsBankPaymentsToVarious($db, $paiement_id = 0)
	{
		global $conf, $langs, $user;

		if (!isModEnabled('banque')) {
			return 0;
		}

		$has_use_subledger = false;
		$resdesc = $db->DDLDescTable(MAIN_DB_PREFIX.'c_subventions_financeur', 'use_subledger');
		if ($resdesc && $db->num_rows($resdesc) > 0) {
			$has_use_subledger = true;
		}

		$sql = "SELECT p.rowid as p_id, p.ref as p_ref, p.datep as p_datep, p.montant as p_montant,";
		$sql .= " p.fk_sub, p.fk_fin, p.fk_soc as p_fk_soc, p.fk_bank, p.fk_account, p.fk_paiement, p.num_paiement,";
		$sql .= " p.fk_user_creat as p_fk_user_creat, p.entity as p_entity,";
		$sql .= " b.dateo as b_dateo, b.datev as b_datev, b.amount as b_amount, b.fk_type as b_fk_type,";
		$sql .= " b.num_chq as b_num_chq, b.label as b_label, b.numero_compte as b_numero_compte,";
		$sql .= " ba.courant as ba_courant, ba.entity as ba_entity,";
		$sql .= " sub.ref as sub_ref, sub.fk_project as sub_fk_project,";
		$sql .= " fin.fk_soc as fin_fk_soc, fin.fk_financeur,";
		$sql .= " cf.accountancy_code_receivable, cf.accountancy_code";
		if ($has_use_subledger) {
			$sql .= ", cf.use_subledger";
		}
		$sql .= ", soc.nom as soc_name, soc.code_compta as code_compta_client";
		$sql .= " FROM ".MAIN_DB_PREFIX."subventions_paiement as p";
		$sql .= " INNER JOIN ".MAIN_DB_PREFIX."bank as b ON b.rowid = p.fk_bank";
		$sql .= " INNER JOIN ".MAIN_DB_PREFIX."bank_account as ba ON ba.rowid = b.fk_account";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."subventions_subvention as sub ON sub.rowid = p.fk_sub";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."subventions_financement as fin ON fin.rowid = p.fk_fin";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_subventions_financeur as cf ON cf.rowid = fin.fk_financeur";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as soc ON soc.rowid = (CASE WHEN p.fk_soc > 0 THEN p.fk_soc ELSE fin.fk_soc END)";
		$sql .= " WHERE p.fk_bank IS NOT NULL AND p.fk_bank > 0";
		$sql .= " AND (p.accounted IS NULL OR p.accounted = 0)";
		if ($paiement_id > 0) {
			$sql .= " AND p.rowid = ".((int) $paiement_id);
		}
		$sql .= " AND NOT EXISTS (";
		$sql .= "   SELECT 1 FROM ".MAIN_DB_PREFIX."accounting_bookkeeping as ab";
		$sql .= "   WHERE (ab.doc_type = 'subvention_paiement' AND ab.fk_doc = p.rowid)";
		$sql .= "      OR (ab.doc_type = 'bank' AND ab.fk_doc = b.rowid)";
		$sql .= " )";
		$sql .= " AND (";
		$sql .= "   NOT EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."bank_url as bu WHERE bu.fk_bank = p.fk_bank AND bu.type = 'payment_various')";
		$sql .= "   OR EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."bank_url as bu2 WHERE bu2.fk_bank = p.fk_bank AND bu2.type = 'company')";
		$sql .= " )";

		$resql = $db->query($sql);
		if (!$resql) {
			dol_syslog(__FUNCTION__." SQL error: ".$db->lasterror(), LOG_ERR);
			return -1;
		}

		$num = $db->num_rows($resql);
		if ($num == 0) {
			$db->free($resql);
			return 0;
		}

		require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
		$accstatic = new Account($db);
		if (is_object($langs)) {
			$langs->load('subventions@subventions');
		}

		$rows = array();
		while ($obj = $db->fetch_object($resql)) {
			$rows[] = $obj;
		}
		$db->free($resql);

		$migrated = 0;
		$now = dol_now();

		foreach ($rows as $obj) {
			$db->begin();
			$error = 0;

			$fk_bank = (int) $obj->fk_bank;
			$pv_id = 0;

			// Reuse existing llx_payment_various record if already created for this bank line
			$sqlcheckpv = "SELECT rowid FROM ".MAIN_DB_PREFIX."payment_various WHERE fk_bank = ".$fk_bank." ORDER BY rowid ASC".$db->plimit(1);
			$rescheckpv = $db->query($sqlcheckpv);
			if ($rescheckpv && ($objpv = $db->fetch_object($rescheckpv))) {
				$pv_id = (int) $objpv->rowid;
			} elseif (!$rescheckpv) {
				$error++;
			}

			if (!$error && $pv_id <= 0) {
				// Resolve payment mode ID in c_paiement
				$fk_typepayment = 0;
				if ((int) $obj->fk_paiement > 0) {
					$sqlpt = "SELECT id FROM ".MAIN_DB_PREFIX."c_paiement WHERE id = ".((int) $obj->fk_paiement);
					$respt = $db->query($sqlpt);
					if ($respt && ($objpt = $db->fetch_object($respt))) {
						$fk_typepayment = (int) $objpt->id;
					}
				}
				if ($fk_typepayment <= 0 && !empty($obj->b_fk_type)) {
					$sqlpt = "SELECT id FROM ".MAIN_DB_PREFIX."c_paiement WHERE code = '".$db->escape($obj->b_fk_type)."' AND entity IN (".getEntity('c_paiement').") ORDER BY active DESC, id ASC".$db->plimit(1);
					$respt = $db->query($sqlpt);
					if ($respt && ($objpt = $db->fetch_object($respt))) {
						$fk_typepayment = (int) $objpt->id;
					}
				}
				if ($fk_typepayment <= 0) {
					$fallbackcode = ((int) $obj->ba_courant === Account::TYPE_CASH) ? 'LIQ' : 'VIR';
					$sqlpt = "SELECT id FROM ".MAIN_DB_PREFIX."c_paiement WHERE code = '".$db->escape($fallbackcode)."' AND entity IN (".getEntity('c_paiement').") ORDER BY active DESC, id ASC".$db->plimit(1);
					$respt = $db->query($sqlpt);
					if ($respt && ($objpt = $db->fetch_object($respt))) {
						$fk_typepayment = (int) $objpt->id;
					}
				}
				if ($fk_typepayment <= 0) {
					$sqlpt = "SELECT id FROM ".MAIN_DB_PREFIX."c_paiement WHERE active = 1 AND entity IN (".getEntity('c_paiement').") ORDER BY id ASC".$db->plimit(1);
					$respt = $db->query($sqlpt);
					if ($respt && ($objpt = $db->fetch_object($respt))) {
						$fk_typepayment = (int) $objpt->id;
					}
				}

				// Resolve accountancy code and subledger account
				$accountancy_code = '';
				if (!empty($obj->accountancy_code_receivable)) {
					$accountancy_code = $obj->accountancy_code_receivable;
				} elseif (!empty($obj->accountancy_code)) {
					$accountancy_code = $obj->accountancy_code;
				} elseif (!empty($obj->b_numero_compte)) {
					$accountancy_code = $obj->b_numero_compte;
				} else {
					$accountancy_code = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_CODE_RECEIVABLE_DEFAULT', '441000');
				}

				$use_subledger = true;
				if ($has_use_subledger && !is_null($obj->use_subledger)) {
					$use_subledger = !empty($obj->use_subledger);
				}
				$subledger_account = ($use_subledger && !empty($obj->code_compta_client)) ? $obj->code_compta_client : '';

				$datep = !empty($obj->b_dateo) ? $db->jdate($obj->b_dateo) : $db->jdate($obj->p_datep);
				$datev = !empty($obj->b_datev) ? $db->jdate($obj->b_datev) : $datep;
				$raw_amount = ((float) $obj->b_amount != 0) ? (float) $obj->b_amount : (float) $obj->p_montant;
				$sens = ($raw_amount < 0) ? 0 : 1;
				$amount = abs($raw_amount);
				$num_payment = !empty($obj->b_num_chq) ? $obj->b_num_chq : (string) $obj->num_paiement;
				$label = !empty($obj->b_label) ? $obj->b_label : ($langs->transnoentitiesnoconv("SubventionPayment").': '.($obj->sub_ref ? $obj->sub_ref.' - ' : '').$obj->p_ref.($obj->soc_name ? ' ('.$obj->soc_name.')' : ''));
				$fk_project = !empty($obj->sub_fk_project) ? (int) $obj->sub_fk_project : 0;
				$fk_user_author = !empty($obj->p_fk_user_creat) ? (int) $obj->p_fk_user_creat : ((is_object($user) && !empty($user->id)) ? (int) $user->id : 1);
				$entity = !empty($obj->ba_entity) ? (int) $obj->ba_entity : (!empty($obj->p_entity) ? (int) $obj->p_entity : (int) $conf->entity);

				$sqlins = "INSERT INTO ".MAIN_DB_PREFIX."payment_various (";
				$sqlins .= "datep, datev, sens, amount, fk_typepayment, num_payment, label, accountancy_code, subledger_account, fk_projet, fk_user_author, datec, fk_bank, entity";
				$sqlins .= ") VALUES (";
				$sqlins .= "'".$db->idate($datep)."'";
				$sqlins .= ", '".$db->idate($datev)."'";
				$sqlins .= ", ".((int) $sens);
				$sqlins .= ", ".price2num($amount);
				$sqlins .= ", ".((int) $fk_typepayment);
				$sqlins .= ", '".$db->escape($num_payment)."'";
				$sqlins .= ", '".$db->escape($label)."'";
				$sqlins .= ", '".$db->escape($accountancy_code)."'";
				$sqlins .= ", '".$db->escape($subledger_account)."'";
				$sqlins .= ", ".($fk_project > 0 ? $fk_project : 0);
				$sqlins .= ", ".((int) $fk_user_author);
				$sqlins .= ", '".$db->idate($now)."'";
				$sqlins .= ", ".((int) $fk_bank);
				$sqlins .= ", ".((int) $entity);
				$sqlins .= ")";

				if ($db->query($sqlins)) {
					$pv_id = (int) $db->last_insert_id(MAIN_DB_PREFIX."payment_various");
				} else {
					dol_syslog(__FUNCTION__." insert llx_payment_various error: ".$db->lasterror(), LOG_ERR);
					$error++;
				}
			}

			// Ensure payment_various link exists in llx_bank_url
			if (!$error && $pv_id > 0) {
				$url_pv = DOL_URL_ROOT.'/compta/bank/various_payment/card.php?id=';
				if ($accstatic->add_url_line($fk_bank, $pv_id, $url_pv, '(VariousPayment)', 'payment_various') < 0) {
					dol_syslog(__FUNCTION__." add_url_line payment_various error: ".$accstatic->error, LOG_ERR);
					$error++;
				}
			}

			// Ensure payment_subvention link exists in llx_bank_url
			if (!$error) {
				$url_sub = dol_buildpath('/subventions/paiement_card.php', 1).'?id=';
				if ($accstatic->add_url_line($fk_bank, (int) $obj->p_id, $url_sub, '(paiement)', 'payment_subvention') < 0) {
					dol_syslog(__FUNCTION__." add_url_line payment_subvention error: ".$accstatic->error, LOG_ERR);
					$error++;
				}
			}

			// Remove legacy company link that breaks Dolibarr bankjournal.php
			if (!$error) {
				$sqldel = "DELETE FROM ".MAIN_DB_PREFIX."bank_url WHERE fk_bank = ".((int) $fk_bank)." AND type = 'company'";
				if (!$db->query($sqldel)) {
					dol_syslog(__FUNCTION__." delete company link error: ".$db->lasterror(), LOG_ERR);
					$error++;
				}
			}

			if (!$error) {
				$db->commit();
				$migrated++;
			} else {
				$db->rollback();
			}
		}

		return $migrated;
	}
}

if (!function_exists('getSubventionsLedgerUrl')) {
	/**
	 * Get URL to Dolibarr general ledger filtered on the bookkeeping entry of a financement or paiement
	 *
	 * @param  string                    $type   'financement' or 'paiement'
	 * @param  CommonObject|object|null  $object Financement or Paiement object
	 * @return string                            URL to bookkeeping list
	 */
	function getSubventionsLedgerUrl($type = 'financement', $object = null)
	{
		global $db, $conf;

		$ref = (!empty($object) && !empty($object->ref)) ? $object->ref : '';
		$url = DOL_URL_ROOT.'/accountancy/bookkeeping/list.php';

		if (!empty($object) && !empty($object->id)) {
			$sql = "SELECT ab.piece_num FROM ".MAIN_DB_PREFIX."accounting_bookkeeping as ab";
			$sql .= " WHERE ab.entity = ".((int) $conf->entity);
			if ($type == 'paiement') {
				$sql .= " AND ((ab.doc_type = 'subvention_paiement' AND ab.fk_doc = ".((int) $object->id).")";
				if (!empty($object->fk_bank)) {
					$sql .= " OR (ab.doc_type = 'bank' AND ab.fk_doc = ".((int) $object->fk_bank).")";
				}
				$sql .= ")";
			} else {
				$sql .= " AND ab.doc_type IN ('subvention', 'subvention_financement') AND ab.fk_doc = ".((int) $object->id);
			}
			$sql .= " ORDER BY ab.piece_num DESC";
			$sql .= $db->plimit(1);

			$resql = $db->query($sql);
			if ($resql && ($obj = $db->fetch_object($resql)) && !empty($obj->piece_num)) {
				return $url.'?search_mvt_num='.((int) $obj->piece_num);
			}
		}

		if (!empty($ref)) {
			return $url.'?search_doc_ref='.urlencode($ref);
		}

		return $url;
	}
}

