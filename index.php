<?php
/* Copyright (C) 2001-2005  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2015       Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
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
 *	\file       subventions/subventionsindex.php
 *	\ingroup    subventions
 *	\brief      Home page of subventions top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/subventions/class/subvention.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/subventions/class/financement.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/subventions/class/paiement.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array("subventions@subventions"));

$action = GETPOST('action', 'aZ09');

$now = dol_now();
$max = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT', 5);

// Security check - Protection if external user
$socid = GETPOSTINT('socid');
if (!empty($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

// Initialize a technical object to manage hooks. Note that conf->hooks_modules contains array
//$hookmanager->initHooks(array($object->element.'index'));

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//if (!isModEnabled('subventions')) {
//	accessforbidden('Module not enabled');
//}
if (! $user->hasRight('subventions', 'subvention', 'read')) {
	accessforbidden();
}
//restrictedArea($user, 'subventions', 0, 'subventions_myobject', 'myobject', '', 'rowid');
//if (empty($user->admin)) {
//	accessforbidden('Must be admin');
//}


/*
 * Actions
 */

// None


/*
 * View
 */
$picto = 'fa-hand-holding-heart';

$form = new Form($db);
$formfile = new FormFile($db);
$substatic = new Subvention($db);


llxHeader("", $langs->trans("SubsidysArea"), '', '', 0, 0, '', '', '', 'mod-subventions page-index');

print load_fiche_titre($langs->trans("SubsidysArea"), '', $picto);

print '<div class="fichecenter"><div class="fichethirdleft">';


// Draft subsidy
if (isModEnabled('subventions') && $user->hasRight('subventions', 'subvention', 'read')) {
		
	$sql = "SELECT s.rowid, s.ref, s.label, s.status";
	$sql .= " FROM ".MAIN_DB_PREFIX."subventions_subvention as s";
	$sql .= " WHERE s.status = 0";
	$resql = $db->query($sql);
	
	if ($resql)
	{
		$total = 0;
		$num = $db->num_rows($resql);

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="3">'.$langs->trans("DraftSubsidy").($num?'<span class="badge marginleftonlyshort">'.$num.'</span>':'').'</th></tr>';

		$var = true;
		if ($num > 0)
		{
			$i = 0;
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);
				print '<tr class="oddeven"><td class="nowrap">';
				
				$substatic->id=$obj->rowid;
				$substatic->ref=$obj->ref;
				$substatic->label=$obj->label;
				$substatic->status=$obj->status;

				print $substatic->getNomUrl(1);
				print '</td>';
				print '<td class="nowrap"><p>'.$obj->label.'</p>';
				print '</td>';
				print '<td class="right" class="nowrap">'.$obj->status.'</td></tr>';
				$i++;
				$total += $obj->status;
			}
			if ($total>0)
			{
				print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td colspan="2" class="right">'.$total."</td></tr>";
			}
		}
		else
		{
			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("NoSubsidy").'</td></tr>';
		}
		print "</table><br>";

		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}
}

// Pending subsidy
if (isModEnabled('subventions') && $user->hasRight('subventions', 'subvention', 'read')) {
		
	$sql = "SELECT s.rowid, s.ref, s.label, s.status, s.montant_att";
	$sql .= " FROM ".MAIN_DB_PREFIX."subventions_subvention as s";
	$sql .= " WHERE s.status > 1";
	$sql .= " AND s.montant_att > 0";
	$resql = $db->query($sql);
	
	if ($resql)
	{
		$total = 0;
		$num = $db->num_rows($resql);

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="3">'.$langs->trans("PendingSubsidy").($num?'<span class="badge marginleftonlyshort">'.$num.'</span>':'').'</th></tr>';

		$var = true;
		if ($num > 0)
		{
			$i = 0;
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);
				print '<tr class="oddeven"><td class="nowrap">';
				
				$substatic->id=$obj->rowid;
				$substatic->ref=$obj->ref;
				$substatic->label=$obj->label;
				$substatic->status=$obj->status;
				$substatic->montant_att=$obj->montant_att;

				print $substatic->getNomUrl(1);
				print '</td>';
				print '<td class="nowrap"><p>'.$obj->label.'</p>';
				print '</td>';
				print '<td class="right" class="nowrap">'.$obj->montant_att.'</td></tr>';
				$i++;
				$total += $obj->montant_att;
			}
			if ($total>0)
			{
				print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td colspan="2" class="right">'.$total."</td></tr>";
			}
		}
		else
		{

			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("NoSubsidy").'</td></tr>';
		}
		print "</table><br>";

		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}
}


print '</div><div class="fichetwothirdright">';


// Last modified subsidy
if (isModEnabled('subventions') && $user->hasRight('subventions','subvention', 'read')) {
	$sql = "SELECT s.rowid, s.ref, s.label, s.status, s.date_creation, s.tms";
	$sql .= " FROM ".MAIN_DB_PREFIX."subventions_subvention as s";
	$sql .= " ORDER BY s.tms DESC";
	$sql .= $db->plimit($max, 0);

	$resql = $db->query($sql);
		
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="2">';
		print $langs->trans("LatestModifiedMySubsidy", $max);
		print '</th>';
		print '<th></th>';
		print '<th class="right">'.$langs->trans("DateModificationShort").'</th>';
		print '</tr>';
		if ($num)
		{
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);

				$substatic->id=$obj->rowid;
				$substatic->ref=$obj->ref;
				$substatic->label=$obj->label;
				$substatic->status=$obj->status;

				print '<tr class="oddeven">';
				print '<td class="nowrap">'.$substatic->getNomUrl(1).'</td>';
				print '<td class="nowrap"><p>'.$obj->label.'</p>';
				print '</td>';
				print '<td class="right nowrap">';
				print "</td>";
				print '<td class="right nowrap">'.dol_print_date($db->jdate($obj->tms), 'day')."</td>";
				print '</tr>';
				$i++;
			}

			$db->free($resql);
		} else {
			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("NoSubsidy").'</td></tr>';
		}
		print "</table><br>";
	}
}

// Last created subsidy
if (isModEnabled('subventions') && $user->hasRight('subventions','subvention', 'read')) {
	$sql = "SELECT s.rowid, s.ref, s.label, s.status, s.date_creation, s.tms";
	$sql .= " FROM ".MAIN_DB_PREFIX."subventions_subvention as s";
	$sql .= " ORDER BY s.date_creation DESC";
	$sql .= $db->plimit($max, 0);

	$resql = $db->query($sql);
		
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="2">';
		print $langs->trans("LatestCreatedMySubsidy", $max);
		print '</th>';
		print '<th></th>';
		print '<th class="right">'.$langs->trans("DateModificationShort").'</th>';
		print '</tr>';
		if ($num)
		{
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);

				$substatic->id=$obj->rowid;
				$substatic->ref=$obj->ref;
				$substatic->label=$obj->label;
				$substatic->status=$obj->status;

				print '<tr class="oddeven">';
				print '<td class="nowrap">'.$substatic->getNomUrl(1).'</td>';
				print '<td class="nowrap"><p>'.$obj->label.'</p>';
				print '</td>';
				print '<td class="right nowrap">';
				print "</td>";
				print '<td class="right nowrap">'.dol_print_date($db->jdate($obj->tms), 'day')."</td>";
				print '</tr>';
				$i++;
			}

			$db->free($resql);
		} else {
			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("NoSubsidy").'</td></tr>';
		}
		print "</table><br>";
	}
}

print '</div></div>';

// End of page
llxFooter();
$db->close();
