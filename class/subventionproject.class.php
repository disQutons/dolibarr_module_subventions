<?php
/* Copyright (C) 2026		Romain MP		<romain.mp@gmail.com>
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
 * \file        class/subventionproject.class.php
 * \ingroup     subventions
 * \brief       Class for SubventionProject (multi-project allocation / ventilation)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';


/**
 * Class SubventionProject
 *
 * Junction table between subventions and projects with allocated amounts.
 * Used by projet/element.php to show only the allocated portion per project.
 */
class SubventionProject extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'subventions';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'subventionproject';

	/**
	 * @var string Name of table without prefix where object is stored.
	 */
	public $table_element = 'subventions_subvention_projet';

	/**
	 * @var string String with name of icon.
	 */
	public $picto = 'fa-hand-holding-heart';


	/**
	 * @var int Subvention ID
	 */
	public $fk_subvention;

	/**
	 * @var int Project ID
	 */
	public $fk_project;

	/**
	 * @var int Budget year / exercice (e.g. 2026)
	 */
	public $annee;

	/**
	 * @var int Number of months (for auto prorata temporis)
	 */
	public $nb_mois;

	/**
	 * @var float Allocated amount
	 */
	public $amount;

	/**
	 * @var float Total HT (alias for amount, used by projet/element.php)
	 */
	public $total_ht;

	/**
	 * @var float Total TTC (alias for amount, no VAT on subsidies)
	 */
	public $total_ttc;

	/**
	 * @var string Note
	 */
	public $note;

	/**
	 * @var string Date of creation
	 */
	public $datec;

	/**
	 * @var int User who created
	 */
	public $fk_user_creat;

	/**
	 * @var int Entity ID
	 */
	public $entity = 1;

	/**
	 * @var string Reference (subvention ref, for element.php)
	 */
	public $ref;

	/**
	 * @var int Status (subvention status, for element.php)
	 */
	public $status;

	/**
	 * @var Societe Third party (for element.php)
	 */
	public $thirdparty;

	/**
	 * @var int Third party ID (fk_soc)
	 */
	public $fk_soc;

	/**
	 * @var int Third party ID (socid alias)
	 */
	public $socid;

	/**
	 * @var int User who modified
	 */
	public $fk_user_modif;


	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  int  $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int             Return integer <0 if KO, >0 if OK (rowid)
	 */
	public function create(User $user, $notrigger = 0)
	{
		global $conf;

		$error = 0;

		// Validation
		if (empty($this->fk_subvention) || empty($this->fk_project)) {
			$this->error = 'ErrorMissingRequiredFields';
			return -1;
		}
		if ($this->amount < 0) {
			$this->error = 'ErrorNegativeAmount';
			return -1;
		}

		$entity = !empty($this->entity) ? (int) $this->entity : (int) $conf->entity;

		$this->db->begin();

		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element." (";
		$sql .= "fk_subvention, fk_project, annee, nb_mois, amount, note, datec, fk_user_creat, entity";
		$sql .= ") VALUES (";
		$sql .= " ".((int) $this->fk_subvention);
		$sql .= ", ".((int) $this->fk_project);
		$sql .= ", ".(!empty($this->annee) ? ((int) $this->annee) : "NULL");
		$sql .= ", ".(!empty($this->nb_mois) ? ((int) $this->nb_mois) : "NULL");
		$sql .= ", ".((float) $this->amount);
		$sql .= ", ".(!empty($this->note) ? "'".$this->db->escape($this->note)."'" : "NULL");
		$sql .= ", '".$this->db->idate(dol_now())."'";
		$sql .= ", ".((int) $user->id);
		$sql .= ", ".((int) $entity);
		$sql .= ")";

		dol_syslog(get_class($this)."::create", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);
			$this->datec = dol_now();
			$this->fk_user_creat = $user->id;
			$this->entity = $entity;

			// Sync total_ht/total_ttc
			$this->total_ht = $this->amount;
			$this->total_ttc = $this->amount;

			if (!$error) {
				$this->db->commit();
				return $this->id;
			} else {
				$this->db->rollback();
				return -1;
			}
		} else {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param  int    $id  Id object
	 * @return int         Return integer <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id)
	{
		$sql = "SELECT sp.rowid, sp.fk_subvention, sp.fk_project, sp.annee, sp.nb_mois, sp.amount, sp.note, sp.datec, sp.tms, sp.fk_user_creat, sp.fk_user_modif, sp.entity,";
		$sql .= " s.ref as subvention_ref, s.fk_soc, s.status as subvention_status";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as sp";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."subventions_subvention as s ON s.rowid = sp.fk_subvention";
		$sql .= " WHERE sp.rowid = ".((int) $id);

		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;
				$this->fk_subvention = $obj->fk_subvention;
				$this->fk_project = $obj->fk_project;
				$this->annee = !empty($obj->annee) ? (int) $obj->annee : null;
				$this->nb_mois = !empty($obj->nb_mois) ? (int) $obj->nb_mois : null;
				$this->amount = $obj->amount;
				$this->total_ht = $obj->amount;
				$this->total_ttc = $obj->amount;
				$this->note = $obj->note;
				$this->datec = $this->db->jdate($obj->datec);
				$this->tms = $this->db->jdate($obj->tms);
				$this->fk_user_creat = $obj->fk_user_creat;
				$this->fk_user_modif = $obj->fk_user_modif;
				$this->entity = $obj->entity;
				$this->ref = $obj->subvention_ref;
				$this->status = $obj->subvention_status;
				if (!empty($this->annee)) {
					$this->ref_customer = (string) $this->annee;
				}

				if (!empty($obj->fk_soc)) {
					$this->fk_soc = (int) $obj->fk_soc;
					$this->socid = (int) $obj->fk_soc;
					require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
					$this->thirdparty = new Societe($this->db);
					$this->thirdparty->fetch($obj->fk_soc);
				}

				$this->db->free($resql);
				return 1;
			} else {
				$this->db->free($resql);
				return 0;
			}
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  int  $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int             Return integer <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = 0)
	{
		if ($this->amount < 0) {
			$this->error = 'ErrorNegativeAmount';
			return -1;
		}

		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET";
		$sql .= " amount = ".((float) $this->amount);
		$sql .= ", annee = ".(!empty($this->annee) ? ((int) $this->annee) : "NULL");
		$sql .= ", nb_mois = ".(!empty($this->nb_mois) ? ((int) $this->nb_mois) : "NULL");
		$sql .= ", note = ".(!empty($this->note) ? "'".$this->db->escape($this->note)."'" : "NULL");
		$sql .= ", fk_user_modif = ".((int) $user->id);
		$sql .= " WHERE rowid = ".((int) $this->id);

		dol_syslog(get_class($this)."::update", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			$this->total_ht = $this->amount;
			$this->total_ttc = $this->amount;
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param  User $user      User that deletes
	 * @param  int  $notrigger 0=launch triggers, 1=disable triggers
	 * @return int             Return integer <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = 0)
	{
		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE rowid = ".((int) $this->id);

		dol_syslog(get_class($this)."::delete", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Fetch all allocations for a given subvention
	 *
	 * @param  int   $subvention_id  Subvention ID
	 * @return SubventionProject[]|int  Array of objects or <0 if KO
	 */
	public function fetchAllBySubvention($subvention_id)
	{
		$records = array();

		$sql = "SELECT sp.rowid, sp.fk_subvention, sp.fk_project, sp.annee, sp.nb_mois, sp.amount, sp.note, sp.datec,";
		$sql .= " sp.fk_user_creat, sp.fk_user_modif,";
		$sql .= " p.ref as project_ref, p.title as project_title";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as sp";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet as p ON p.rowid = sp.fk_project";
		$sql .= " WHERE sp.fk_subvention = ".((int) $subvention_id);
		$sql .= " ORDER BY COALESCE(sp.annee, 9999) ASC, sp.rowid ASC";

		dol_syslog(get_class($this)."::fetchAllBySubvention", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->id = $obj->rowid;
				$record->fk_subvention = $obj->fk_subvention;
				$record->fk_project = $obj->fk_project;
				$record->annee = !empty($obj->annee) ? (int) $obj->annee : null;
				$record->nb_mois = !empty($obj->nb_mois) ? (int) $obj->nb_mois : null;
				$record->amount = $obj->amount;
				$record->total_ht = $obj->amount;
				$record->total_ttc = $obj->amount;
				$record->note = $obj->note;
				$record->datec = $this->db->jdate($obj->datec);
				$record->fk_user_creat = $obj->fk_user_creat;
				$record->fk_user_modif = $obj->fk_user_modif;
				// Extra joined fields
				$record->project_ref = $obj->project_ref;
				$record->project_title = $obj->project_title;

				$records[$record->id] = $record;
				$i++;
			}
			$this->db->free($resql);
			return $records;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Get total allocated amount for a given subvention
	 *
	 * @param  int    $subvention_id  Subvention ID
	 * @return float|int  Total amount or <0 if KO
	 */
	public function getTotalVentilated($subvention_id)
	{
		$sql = "SELECT COALESCE(SUM(amount), 0) as total";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE fk_subvention = ".((int) $subvention_id);

		dol_syslog(get_class($this)."::getTotalVentilated", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			$obj = $this->db->fetch_object($resql);
			return (float) $obj->total;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Return the label of a given status
	 *
	 * @param  int $mode 0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 * @return string    Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 * Return the label of a given status
	 *
	 * @param  int $status Id status
	 * @param  int $mode   0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 * @return string      Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		dol_include_once('/custom/subventions/class/subvention.class.php');
		$subvention = new Subvention($this->db);
		return $subvention->LibStatut($status, $mode);
	}


	/**
	 * Return a link to the subvention card
	 *
	 * @param  int    $withpicto             Add picto into link
	 * @param  string $option                Where point the link (0=>card, 1=>card)
	 * @param  int    $notooltip             1=Disable tooltip
	 * @param  string $moreparam             Add more parameters into link
	 * @param  int    $save_lastsearch_value -1=Auto, 0=No save of lastsearch_values, 1=Save lastsearch_values
	 * @return string                        HTML String with link
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $moreparam = '', $save_lastsearch_value = -1)
	{
		dol_include_once('/custom/subventions/class/subvention.class.php');

		$subvention = new Subvention($this->db);
		if ($subvention->fetch($this->fk_subvention) > 0) {
			return $subvention->getNomUrl($withpicto, $option, $notooltip, $moreparam, $save_lastsearch_value);
		}
		return '';
	}

	/**
	 * Generate automatic multi-year repartition (prorata temporis by month)
	 *
	 * @param  Subvention $subvention Subvention object
	 * @param  User       $user       User executing the action
	 * @param  int        $fk_project Project ID to assign to the ventilation
	 * @return int                    Number of generated years or <0 if KO
	 */
	public function generateAutoRepartitionYears($subvention, $user, $fk_project)
	{
		if (empty($subvention->date_d_projet) || empty($subvention->date_f_projet)) {
			$this->error = 'ErrorMissingProjectDates';
			return -1;
		}
		if (empty($fk_project)) {
			$this->error = 'ErrorFieldRequired';
			return -1;
		}

		$start_year = (int) dol_print_date($subvention->date_d_projet, '%Y');
		$start_month = (int) dol_print_date($subvention->date_d_projet, '%m');
		$end_year = (int) dol_print_date($subvention->date_f_projet, '%Y');
		$end_month = (int) dol_print_date($subvention->date_f_projet, '%m');

		if ($start_year > $end_year || ($start_year == $end_year && $start_month > $end_month)) {
			$this->error = 'ErrorStartDateGreaterThanEndDate';
			return -1;
		}

		// Reference amount: montant_acc if > 0, otherwise montant_dem
		$total_amount = (!empty($subvention->montant_acc) && $subvention->montant_acc > 0) ? (float) $subvention->montant_acc : (float) $subvention->montant_dem;
		if ($total_amount <= 0) {
			$this->error = 'ErrorAmountRequired';
			return -1;
		}

		// Calculate months per year
		$months_per_year = array();
		$total_months = 0;
		for ($y = $start_year; $y <= $end_year; $y++) {
			if ($start_year == $end_year) {
				$m = $end_month - $start_month + 1;
			} elseif ($y == $start_year) {
				$m = 12 - $start_month + 1;
			} elseif ($y == $end_year) {
				$m = $end_month;
			} else {
				$m = 12;
			}
			$months_per_year[$y] = $m;
			$total_months += $m;
		}

		if ($total_months <= 0) {
			$this->error = 'ErrorTotalMonthsZero';
			return -1;
		}

		$this->db->begin();

		// Delete existing records for this subvention and project having annee set
		$sql_del = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql_del .= " WHERE fk_subvention = ".((int) $subvention->id);
		$sql_del .= " AND fk_project = ".((int) $fk_project);
		$res_del = $this->db->query($sql_del);
		if (!$res_del) {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -1;
		}

		$sum_allocated = 0;
		$years = array_keys($months_per_year);
		$last_year = end($years);
		$count = 0;

		foreach ($months_per_year as $y => $m) {
			if ($y == $last_year) {
				// Cent-rounding compensation on last year
				$amount = round($total_amount - $sum_allocated, 2);
			} else {
				$amount = round($total_amount * ($m / $total_months), 2);
				$sum_allocated += $amount;
			}

			$alloc = new self($this->db);
			$alloc->fk_subvention = $subvention->id;
			$alloc->fk_project = $fk_project;
			$alloc->annee = $y;
			$alloc->nb_mois = $m;
			$alloc->amount = $amount;
			$alloc->note = $m.' '.(($m > 1) ? 'mois' : 'mois').' ('.round(($m / $total_months) * 100, 1).'%)';

			$res = $alloc->create($user);
			if ($res < 0) {
				$this->error = $alloc->error;
				$this->db->rollback();
				return -1;
			}
			$count++;
		}

		$this->db->commit();
		return $count;
	}
}
