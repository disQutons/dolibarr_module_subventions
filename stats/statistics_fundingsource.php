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

 
//FBR récupération des erreurs php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
require_once DOL_DOCUMENT_ROOT.'/custom/subventions/class/subventionstats.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

$WIDTH = DolGraph::getDefaultGraphSizeForStats('width');
$HEIGHT = DolGraph::getDefaultGraphSizeForStats('height');

// Load translation files required by the page
$langs->loadLangs(array("subventions@subventions"));

$action = GETPOST('action', 'aZ09');

// TODO Réfléchir à comment modifier $mode et $montant. Créer un second objet ?
$mode = GETPOST("mode","alpha") ? GETPOST("mode","alpha") : 'subvention';
if ($mode== -1) { $mode = 'subvention'; }

$montant = GETPOST("montant") ? GETPOST("montant") : 'montant_acc';

$userid = GETPOSTINT('userid');
$object_status = GETPOST('object_status', 'intcomma');
$object_fundingsource = GETPOST('object_fundingsource', 'intcomma');

$now = dol_now();
$max = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT', 5);

// Security check - Protection if external user
$socid = GETPOSTINT('socid');
if ($user->socid > 0) {
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


$form = new Form($db);
$formfile = new FormFile($db);
$substatic = new Subvention($db);
$finstatic = new Financement($db);

$picto = 'fa-hand-holding-heart';
$title = $langs->trans("SubsidysStatistics");
$dir = DOL_DATA_ROOT . "/subvention/temp/";

$h = 0;
$head = array();
$head[$h][0] = DOL_URL_ROOT.'/custom/subventions/stats/index.php';
$head[$h][1] = $langs->trans("ByMonthYear");
$head[$h][2] = 'byyear';
$h++;
$head[$h][0] = DOL_URL_ROOT.'/custom/subventions/stats/statistics_fundingsource.php';
$head[$h][1] = $langs->trans("ByFundingSource");
$head[$h][2] = 'byfundingsource';
$h++;

$type = 'subsidy_stats';

llxHeader('', $title);

print load_fiche_titre($title, '', $picto);

complete_head_from_modules($conf, $langs, null, $head, $h, $type);

print dol_get_fiche_head($head, 'byfundingsource', '', -1);

dol_mkdir($dir);

$stats = new Subventionstats($db,'financement',$montant,$socid,$userid,$object_fundingsource);

if ($object_status != '' && $object_status >= 0) {
	$stats->where .= ' AND x.status IN ('.$db->sanitize($object_status).')';
}

$nowyear = dol_print_date(dol_now('gmt'), "%Y", 'gmt');
$year = GETPOST('year') > 0 ? GETPOSTINT('year') : 0;

// Show array
$data = $stats->getAllByYear();
$arrayyears = array();

foreach ($data as $val) {
	$arrayyears[$val['year']] = $val['year'];
}
if (!count($arrayyears)) {
	$arrayyears[$nowyear] = $nowyear;
}

//print '<div class="fichethirdleft">';
print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="mode" value="'.$mode.'">';

print '<table class="noborder centpercent">';

// Year
print '<tr><td>'.$langs->trans("Year").'</td><td>';
if (!in_array($year, $arrayyears)) {
	$arrayyears[$year] = $year;
}
if (!in_array($nowyear, $arrayyears)) {
	$arrayyears[$nowyear] = $nowyear;
}
arsort($arrayyears);
$arrayyears = array(0 => $langs->trans("AllYears")) + $arrayyears;

print $form->selectarray('year', $arrayyears, $year, 0, 0, 0, '', 0, 0, 0, '', 'width75');
print '</td><td class="left" colspan="2"><input type="submit" name="submit" class="button small" value="'.$langs->trans("Refresh").'"></td></tr>';
print '</table>';
print '</form>';
//print '</div>';

// Initialisation des totaux
$total_nb = 0;
$total_montant_dem = 0;
$total_montant_acc = 0;
$total_montant_fin = 0;

// Récapitulatif des financeurs
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre" height="24">';
print '<td class="left">'.$langs->trans("FundingSourceGroup").'</td>';
print '<td class="center">'.$langs->trans("NbRequest").'</td>';
print '<td class="center">'.$langs->trans("AmountDem").'</td>';
print '<td class="center">'.$langs->trans("AmountAcc").'</td>';
print '<td class="center">% acc</td>';
print '<td class="center">'.$langs->trans("AmountFin").'</td>';
print '<td class="center">% fin</td>';
print '</tr>';

$data = $stats->getStatsFundingSource($summary = true, $year);
foreach ($data as $val) {
    // Calcul des %
    $percentAccDem = (!empty($val['montant_dem']) && $val['montant_dem'] != 0)
        ? round(($val['montant_acc'] / $val['montant_dem']) * 100, 2) : 0;
    $percentFinAcc = (!empty($val['montant_acc']) && $val['montant_acc'] != 0)
        ? round(($val['montant_fin'] / $val['montant_acc']) * 100, 2) : 0;

    // Détermination de la couleur (vert/rouge)
    if ($percentAccDem > (int) getDolGlobalString('SUBVENTIONS_STATISTIC_COLOR_GREEN')) {
		$colorAccDem = 'green';}
	elseif ($percentAccDem >= (int) getDolGlobalString('SUBVENTIONS_STATISTIC_COLOR_ORANGE')) {
		$colorAccDem = 'orange';}
	else {$colorAccDem = 'red';}

	if ($percentFinAcc > (int) getDolGlobalString('SUBVENTIONS_STATISTIC_COLOR_GREEN')) {
		$colorFinAcc = 'green';}
	elseif ($percentFinAcc >= (int) getDolGlobalString('SUBVENTIONS_STATISTIC_COLOR_ORANGE')) {
		$colorFinAcc = 'orange';}
	else {$colorFinAcc = 'red';}

    print '<tr class="oddeven" height="24">';
    print '<td align="left"><a href="'.dol_buildpath('custom/subventions/financement_list.php?search_fk_soc='.$val['fk_soc'].'">'.$val['nom'],1).'</a></td>';
    print '<td class="center">'.$val['nb'].'</td>';
    print '<td class="right"><span class="amount">'.price(price2num($val['montant_dem'], 'MT'), 1).'</span></td>';
    print '<td class="right"><span class="amount">'.price(price2num($val['montant_acc'], 'MT'), 1).'</span></td>';
    print '<td class="right right opacitylow" style="color: '.$colorAccDem.';">'.$percentAccDem.'%</td>';
    print '<td class="right"><span class="amount">'.price(price2num($val['montant_fin'], 'MT'), 1).'</span></td>';
    print '<td class="right opacitylow" style="color: '.$colorFinAcc.';">'.$percentFinAcc.'%</td>';
    print '</tr>';
	
	// Mise à jour des totaux
    $total_nb += $val['nb'];
    $total_montant_dem += $val['montant_dem'];
    $total_montant_acc += $val['montant_acc'];
    $total_montant_fin += $val['montant_fin'];
}

// Calcul des %
$total_percentAccDem = ($total_montant_dem > 0) ? round($total_montant_acc / $total_montant_dem * 100, 2) : 0;
$total_percentFinAcc = ($total_montant_acc > 0) ? round($total_montant_fin / $total_montant_acc * 100, 2) : 0;

// Ligne de total
print '<tr class="liste_total" height="24">';
print '<td class="left"><strong>'.$langs->trans("Total").'</strong></td>';
print '<td class="center"><strong>'.$total_nb.'</strong></td>';
print '<td class="right"><strong><span class="amount">'.price(price2num($total_montant_dem, 'MT'), 1).'</span></strong></td>';
print '<td class="right"><strong><span class="amount">'.price(price2num($total_montant_acc, 'MT'), 1).'</span></strong></td>';
print '<td class="right opacitylow" style="color:black;"><strong>'.$total_percentAccDem.'%</strong></td>';
print '<td class="right"><strong><span class="amount">'.price(price2num($total_montant_fin, 'MT'), 1).'</span></strong></td>';
print '<td class="right opacitylow" style="color: black;"><strong>'.$total_percentFinAcc.'%</strong></td>';
print '</tr>';

print '</div></table>';

// Liste complète des financeurs
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre" height="24">';
print '<td class="left">'.$langs->trans("FundingSource").'</td>';
print '<td class="center">'.$langs->trans("NbRequest").'</td>';
print '<td class="center">'.$langs->trans("AmountDem").'</td>';
print '<td class="center">'.$langs->trans("AmountAcc").'</td>';
print '<td class="center">% acc</td>';
print '<td class="center">'.$langs->trans("AmountFin").'</td>';
print '<td class="center">% fin</td>';
//print '<td class="center">'.$langs->trans("LastRequest").'</td>';
print '</tr>';

$data = $stats->getStatsFundingSource($summary = false, $year);
foreach ($data as $val) {
    // Calcul des %
    $percentAccDem = (!empty($val['montant_dem']) && $val['montant_dem'] != 0)
        ? round(($val['montant_acc'] / $val['montant_dem']) * 100, 2) : 0;
    $percentFinAcc = (!empty($val['montant_acc']) && $val['montant_acc'] != 0)
        ? round(($val['montant_fin'] / $val['montant_acc']) * 100, 2) : 0;

    // Détermination de la couleur (vert/rouge)
    if ($percentAccDem > (int) getDolGlobalString('SUBVENTIONS_STATISTIC_COLOR_GREEN')) {
		$colorAccDem = 'green';}
	elseif ($percentAccDem >= (int) getDolGlobalString('SUBVENTIONS_STATISTIC_COLOR_ORANGE')) {
		$colorAccDem = 'orange';}
	else {$colorAccDem = 'red';}

	if ($percentFinAcc > (int) getDolGlobalString('SUBVENTIONS_STATISTIC_COLOR_GREEN')) {
		$colorFinAcc = 'green';}
	elseif ($percentFinAcc >= (int) getDolGlobalString('SUBVENTIONS_STATISTIC_COLOR_ORANGE')) {
		$colorFinAcc = 'orange';}
	else {$colorFinAcc = 'red';}

	// Ligne de détail
    print '<tr class="oddeven" height="24">';
    print '<td align="left"><a href="'.dol_buildpath('custom/subventions/financement_list.php?search_fk_soc='.$val['fk_soc'].'">'.$val['nom'],1).'</a></td>';
    print '<td class="center">'.$val['nb'].'</td>';
    print '<td class="right"><span class="amount">'.price(price2num($val['montant_dem'], 'MT'), 1).'</span></td>';
    print '<td class="right"><span class="amount">'.price(price2num($val['montant_acc'], 'MT'), 1).'</span></td>';
    print '<td class="right right opacitylow" style="color: '.$colorAccDem.';">'.$percentAccDem.'%</td>';
    print '<td class="right"><span class="amount">'.price(price2num($val['montant_fin'], 'MT'), 1).'</span></td>';
    print '<td class="right opacitylow" style="color: '.$colorFinAcc.';">'.$percentFinAcc.'%</td>';
    print '</tr>';

	// Mise à jour des totaux
    $total_nb += $val['nb'];
    $total_montant_dem += $val['montant_dem'];
    $total_montant_acc += $val['montant_acc'];
    $total_montant_fin += $val['montant_fin'];
}

// Calcul des %
$total_percentAccDem = ($total_montant_dem > 0) ? round($total_montant_acc / $total_montant_dem * 100, 2) : 0;
$total_percentFinAcc = ($total_montant_acc > 0) ? round($total_montant_fin / $total_montant_acc * 100, 2) : 0;

// Ligne de total
print '<tr class="liste_total" height="24">';
print '<td class="left"><strong>'.$langs->trans("Total").'</strong></td>';
print '<td class="center"><strong>'.$total_nb.'</strong></td>';
print '<td class="right"><strong><span class="amount">'.price(price2num($total_montant_dem, 'MT'), 1).'</span></strong></td>';
print '<td class="right"><strong><span class="amount">'.price(price2num($total_montant_acc, 'MT'), 1).'</span></strong></td>';
print '<td class="right opacitylow" style="color:black;"><strong>'.$total_percentAccDem.'%</strong></td>';
print '<td class="right"><strong><span class="amount">'.price(price2num($total_montant_fin, 'MT'), 1).'</span></strong></td>';
print '<td class="right opacitylow" style="color:black;"><strong>'.$total_percentFinAcc.'%</strong></td>';
print '</tr>';

print '</div></table>';


// End of page
llxFooter();
$db->close();
