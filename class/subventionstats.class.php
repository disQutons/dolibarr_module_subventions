<?php
/* Copyright (C) 2003       Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2005-2011  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009  Regis Houssin       <regis.houssin@inodbox.com>
 * Copyright (C) 2023       Waël Almoman        <info@almoman.com>
 * Copyright (C) 2024		MDW                 <mdeweerd@users.noreply.github.com>
 * Copyright (C) 2024       Frédéric France     <frederic.france@free.fr>
 * Copyright (C) 2025       François brichart   <francois@disqutons.fr>
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
 *	\file       htdocs/adherents/class/adherentstats.class.php
 *	\ingroup    member
 *	\brief      File for class managing statistics of members
 */

 
//FBR récupération des erreurs php
error_reporting(E_ALL);
ini_set('display_errors', 1);


include_once DOL_DOCUMENT_ROOT.'/core/class/stats.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/subventions/class/subvention.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/subventions/class/financement.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/subventions/class/paiement.class.php';


/**
 *	Class to manage statistics of members
 */
class SubventionStats extends Stats
{
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element;

	/**
	 * @var int
	 */
	public $subventionid;
	/**
	 * @var int
	 */
	public $socid;
	/**
	 * @var int
	 */
	public $userid;

	/**
	 * @var string
	 */
	public $from;
	/**
	 * @var int
	 */
	public $where;
	/**
	 * @var string
	 */
	public $mode;
	/**
	 * @var string
	 */
	public $date_stat;
	/**
	 * @var string
	 */
	public $montant;
	/**
	 * @var int
	 */
	public $fundingsource;

	/**
	 *	Constructor
	 *
	 *	@param 		DoliDB		$db			Database handler
	 * 	@param 		int			$socid	   	Id third party
	 * 	@param   	int			$userid    	Id user for filter
	 */
	public function __construct($db, $mode, $montant, $socid = 0, $userid = 0, $object_fundingsource = 0)
	{
		$this->db = $db;
		$this->mode = $mode;
		$this->socid = ($socid > 0 ? $socid : 0);
		$this->userid = $userid;
		$this->fundingsource = $object_fundingsource;				

		if ($this->mode == 'subvention') {
			$this->date_stat = getDolGlobalString('SUBVENTIONS_STATISTIC_DATE');
			$object = new Subvention($this->db);
			
			if ($this->fundingsource > 0) {
				$this->from = MAIN_DB_PREFIX.$object->table_element." as x";
				$this->from .= " LEFT JOIN ".MAIN_DB_PREFIX."subventions_financement as y ON x.rowid = y.fk_sub";
				$this->where .= " x.status != -1";
				$this->where .= " AND y.fk_financeur = ".$this->fundingsource;
			}
			else {
				$this->from = MAIN_DB_PREFIX.$object->table_element." as x";
				$this->where .= " x.status != -1";
			}
		} elseif ($this->mode == 'financement') {
			$this->date_stat = 'date_creation';
			$object = new Financement($this->db);

			$this->from = MAIN_DB_PREFIX.$object->table_element." as x";
			$this->from .= " INNER JOIN ".MAIN_DB_PREFIX."subventions_subvention as y";
			$this->from .= " ON x.fk_sub = y.rowid";

			$this->where .= " y.status != -1";
			if ($this->fundingsource > 0) {
				$this->where .= " AND x.fk_financeur = ".$this->fundingsource;
			}
		} else {
			return 0;
		}

		if ($this->socid) {
			$this->where .= " AND x.fk_soc = ".((int) $this->socid);
		}
		if ($this->userid > 0) {
			$this->where .= ' AND x.fk_user_creat = '.((int) $this->userid);
		}
		
		$this->field = 'ref';
		$this->montant = $montant;
	}


	/**
	 * Return the number of subsidys/funding by month for a given year
	 *
	 *	@param	int		$year       Year
	 *	@param	int		$format		0=Label of abscissa is a translated text, 1=Label of abscissa is month number, 2=Label of abscissa is first letter of month
	 *	@return	array<int<0,11>,array{0:int<1,12>,1:int}>	Array of nb each month
	 */
	public function getNbByMonth($year, $format = 0)
	{
		$sql = "SELECT date_format(x.".$this->date_stat.",'%m') as ds, count(*)";
		$sql .= " FROM ".$this->from;
		$sql .= " WHERE ".dolSqlDateFilter('x.'.$this->date_stat, 0, 0, (int) $year, 1);
		$sql .= " AND ".$this->where;
		$sql .= " GROUP BY ds";
		$sql .= $this->db->order('ds', 'DESC');

		return $this->_getNbByMonth($year, $sql, $format);
	}

	/**
	 * Return the number of subsidys/funding by year
	 *
	 * @return	array<array{0:int,1:int}>				Array of nb each year
	 */
	public function getNbByYear()
	{
		$sql = "SELECT date_format(x.".$this->date_stat.",'%Y') as ds, count(*)";
		$sql .= " FROM ".$this->from;
		$sql .= " WHERE ".$this->where;
		$sql .= " GROUP BY ds";
		$sql .= $this->db->order('ds', 'DESC');

		return $this->_getNbByYear($sql);
	}

	/**
	 * Return the number of subsidys/funding by month for a given year
	 *
	 * @param   int		$year       Year
	 * @param	int		$format		0=Label of abscissa is a translated text, 1=Label of abscissa is month number, 2=Label of abscissa is first letter of month
	 *	@return	array<int<0,11>,array{0:int<1,12>,1:int|float}>	Array of values by month
	 */
	public function getAmountByMonth($year, $format = 0)
	{
		$sql = "SELECT date_format(x.".$this->date_stat.",'%m') as ds, sum(x.".$this->montant.")";
		$sql .= " FROM ".$this->from;
		$sql .= " WHERE ".dolSqlDateFilter('x.'.$this->date_stat, 0, 0, (int) $year, 1);
		$sql .= " AND ".$this->where;
		$sql .= " GROUP BY ds";
		$sql .= $this->db->order('ds', 'DESC');

		return $this->_getAmountByMonth($year, $sql, $format);
	}

	/**
	 * Return average amount each month
	 *
	 *	@param	int		$year       Year
	 *	@return	array<int<0,11>,array{0:int<1,12>,1:int|float}>	Array of average each month
	 */
	public function getAverageByMonth($year)
	{
		$sql = "SELECT date_format(x.".$this->date_stat.",'%m') as ds, avg(x.".$this->montant.")";
		$sql .= " FROM ".$this->from;
		$sql .= " WHERE ".dolSqlDateFilter('x.'.$this->date_stat, 0, 0, (int) $year, 1);
		$sql .= " AND ".$this->where;
		$sql .= " GROUP BY ds";
		$sql .= $this->db->order('ds', 'DESC');

		return $this->_getAverageByMonth($year, $sql);
	}


	/**
	 *	Return nb, total and average
	 *
	 *  @return array<array{year:string,nb:string,nb_diff:float,total?:float,avg?:float,weighted?:float,total_diff?:float,avg_diff?:float,avg_weighted?:float}>    Array with nb, total amount, average for each year
	 */
	public function getAllByYear()
	{
		$sql = "SELECT date_format(x.".$this->date_stat.",'%Y') as year, count(*) as nb, sum(x.".$this->montant.") as total, avg(x.".$this->montant.") as avg";
		$sql .= " FROM ".$this->from;
		$sql .= " WHERE ".$this->where;
		$sql .= " GROUP BY year";
		$sql .= $this->db->order('year', 'DESC');

		return $this->_getAllByYear($sql);
	}

	/**
	 *	Récupérer tous les financeurs, nb demandes, mnt deamndé, mnt accepté, mnt financé, dernière demande
	 *
	 *  @return array<array{fk_soc:int,nb:int,montant_dem:float,montant_acc:float,montant_fin:float,date_creation:date,nom:string}>
	 */
	public function getStatsFundingSource($sumary, $year = 0)
	{
		if ($sumary){
			$sql = "SELECT x.fk_soc, COUNT(x.ref) AS nb, x.montant_dem, x.montant_acc, x.montant_fin, x.date_creation, x.fk_financeur, s.label as nom";
			$sql .= " FROM ".$this->from;
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_subventions_financeur as s ON s.rowid = x.fk_financeur";
			$sql .= " WHERE ".$this->where;
			if ($year > 0) {
				$sql .= " AND ".dolSqlDateFilter('x.'.$this->date_stat, 0, 0, (int) $year, 1);
			}
			$sql .= " GROUP BY x.fk_financeur";
			$sql .= $this->db->order('nom', 'ASC');
		}
		else {
			$sql = "SELECT x.fk_soc, COUNT(x.ref) AS nb, x.montant_dem, x.montant_acc, x.montant_fin, x.date_creation, s.nom";
			$sql .= " FROM ".$this->from;
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = x.fk_soc";
			$sql .= " WHERE ".$this->where;
			$sql .= " GROUP BY x.fk_soc";
			$sql .= $this->db->order('s.nom', 'ASC');
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			$data = array();
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);
				$data[] = array(
					'fk_soc' => $obj->fk_soc,
					'nb' => $obj->nb,
					'montant_dem' => $obj->montant_dem,
					'montant_acc' => $obj->montant_acc,
					'montant_fin' => $obj->montant_fin,
					'date_creation' => $obj->date_creation,
					'nom' => $obj->nom,
				);
				$i++;
			}
			$this->db->free($resql);
			return $data;
		} else {
			dol_print_error($this->db);
			return array();
		}
	}
}
