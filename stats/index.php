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
dol_include_once('/custom/subventions/class/subvention.class.php');
dol_include_once('/custom/subventions/class/financement.class.php');
dol_include_once('/custom/subventions/class/paiement.class.php');
dol_include_once('/custom/subventions/class/subventionstats.class.php');
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

print dol_get_fiche_head($head, 'byyear', '', -1);

dol_mkdir($dir);

$stats = new Subventionstats($db,$mode,$montant,$socid,$userid,$object_fundingsource);

if ($object_status != '' && $object_status >= 0) {
	$stats->where .= ' AND x.status IN ('.$db->sanitize($object_status).')';
}

$nowyear = dol_print_date(dol_now('gmt'), "%Y", 'gmt');
$year = GETPOST('year') > 0 ? GETPOSTINT('year') : $nowyear;
$startyear = $year - (!getDolGlobalInt('MAIN_STATS_GRAPHS_SHOW_N_YEARS') ? 2 : max(1, min(10, getDolGlobalInt('MAIN_STATS_GRAPHS_SHOW_N_YEARS'))));
$endyear = $year;

// Build graphic number of object
$data = $stats->getNbByMonthWithPrevYear($endyear, $startyear);

$filenamenb = $dir."/subsidysinyear-".$year.".png";
$fileurlnb = '';

$fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=subvention&file=subsidysinyear-'.$year.'.png';


$px1 = new DolGraph();
$mesg = $px1->isGraphKo();
if (!$mesg) {
	$px1->SetData($data);
	$i = $startyear;
	$legend = array();
	while ($i <= $endyear) {
		$legend[] = $i;
		$i++;
	}
	$px1->SetLegend($legend);
	$px1->SetMaxValue($px1->GetCeilMaxValue());
	$px1->SetWidth($WIDTH);
	$px1->SetHeight($HEIGHT);
	if ($mode == 'subvention') {
		$px1->SetYLabel($langs->trans("NumberOfSubsidys"));
	} elseif ($mode == 'financement') {
		$px1->SetYLabel($langs->trans("NumberOfFundings"));
	}
	$px1->SetShading(3);
	$px1->SetHorizTickIncrement(1);
	$px1->mode = 'depth';
	if ($mode == 'subvention') {
		$px1->SetTitle($langs->trans("NumberOfSubsidysByMonth"));
	} elseif ($mode == 'financement') {
		$px1->SetTitle($langs->trans("NumberOfFundingsByMonth"));
	}
	$px1->draw($filenamenb, $fileurlnb);
}

// Build graphic amount of object
$data = $stats->getAmountByMonthWithPrevYear($endyear, $startyear);

$filenameamount = $dir."/subsidysamountinyear-".$year.".png";
$fileurlamount = '';
$fileurlamount = DOL_URL_ROOT.'/viewimage.php?modulepart=subvention&file=subsidysamountinyear-'.$year.'.png';

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
if (!$mesg) {
	$px2->SetData($data);
	$i = $startyear;
	$legend = array();
	while ($i <= $endyear) {
		$legend[] = $i;
		$i++;
	}
	$px2->SetLegend($legend);
	$px2->SetMaxValue($px2->GetCeilMaxValue());
	$px2->SetMinValue(min(0, $px2->GetFloorMinValue()));
	$px2->SetWidth($WIDTH);
	$px2->SetHeight($HEIGHT);
	if ($mode == 'subvention') {
		$px2->SetYLabel($langs->trans("AmountOfSubsidys"));
	} elseif ($mode == 'financement') {
		$px2->SetYLabel($langs->trans("AmountOfFundings"));
	}
	$px2->SetShading(3);
	$px2->SetHorizTickIncrement(1);
	$px2->mode = 'depth';
	if ($mode == 'subvention') {
		$px2->SetTitle($langs->trans("AmountOfSubsidysByMonth"));
	} elseif ($mode == 'financement') {
		$px2->SetTitle($langs->trans("AmountOfFundingsByMonth"));
	}
	$px2->draw($filenameamount, $fileurlamount);
}


$data = $stats->getAverageByMonthWithPrevYear($endyear, $startyear);

$fileurl_avg = '';
if (!$user->hasRight('societe', 'client', 'voir')) {
	$filename_avg = $dir.'/ordersaverage-'.$user->id.'-'.$year.'.png';
	$fileurl_avg = DOL_URL_ROOT.'/viewimage.php?modulepart=subvention&file=ordersaverage-'.$user->id.'-'.$year.'.png';
} else {
	$filename_avg = $dir.'/ordersaverage-'.$year.'.png';
	$fileurl_avg = DOL_URL_ROOT.'/viewimage.php?modulepart=subvention&file=ordersaverage-'.$year.'.png';
}

$px3 = new DolGraph();
$mesg = $px3->isGraphKo();
if (!$mesg) {
	$px3->SetData($data);
	$i = $startyear;
	$legend = array();
	while ($i <= $endyear) {
		$legend[] = $i;
		$i++;
	}
	$px3->SetLegend($legend);
	$px3->SetYLabel($langs->trans("AmountAverage"));
	$px3->SetMaxValue($px3->GetCeilMaxValue());
	$px3->SetMinValue((int) $px3->GetFloorMinValue());
	$px3->SetWidth($WIDTH);
	$px3->SetHeight($HEIGHT);
	$px3->SetShading(3);
	$px3->SetHorizTickIncrement(1);
	$px3->mode = 'depth';
	$px3->SetTitle($langs->trans("AmountAverage"));

	$px3->draw($filename_avg, $fileurl_avg);
}


// Show array
$data = $stats->getAllByYear();
$arrayyears = array();
foreach ($data as $val) {
	$arrayyears[$val['year']] = $val['year'];
}
if (!count($arrayyears)) {
	$arrayyears[$nowyear] = $nowyear;
}

print '<div class="fichecenter"><div class="fichethirdleft">';

// Show filter box
print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="mode" value="'.$mode.'">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';

// Company
print '<tr><td>'.$langs->trans("ThirdParty").'</td><td>';
$filter = '';

print img_picto('', 'company', 'class="pictofixedwidth"');
print $form->select_company($socid, 'socid', $filter, 1, 0, 0, array(), 0, 'widthcentpercentminusx maxwidth300');
print '</td></tr>';

// Type funding source
print '<tr><td>'.$langs->trans("FundingSourceType").'</td><td>';
print img_picto('', 'fa-handshake', 'class="pictofixedwidth"');
print $form->selectarray('object_fundingsource', $finstatic->getFundingSources(),$object_fundingsource,1);
print '</td></tr>';

// Type of amount
print '<tr><td>'.$langs->trans("Source").'</td><td>';
print img_picto('', 'fa-hand-holding-heart', 'class="pictofixedwidth"');
$listsource = array('subvention' => $langs->trans("Subsidy"), 'financement' => $langs->trans("Funding"));
print $form->selectarray('mode', $listsource, $mode, 1);
print '</td></tr>';

// User
print '<tr><td>'.$langs->trans("CreatedBy").'</td><td>';
print img_picto('', 'user', 'class="pictofixedwidth"');
print $form->select_dolusers($userid ? $userid : -1, 'userid', 1, null, 0, '', '', '0', 0, 0, '', 0, '', 'widthcentpercentminusx maxwidth300');
print '</td></tr>';

// Status
print '<tr><td>'.$langs->trans("Status").'</td><td>';
$liststatus = array('0' => $langs->trans("STATUS_DRAFT"), '1' => $langs->trans("STATUS_VALIDATED"), '2' => $langs->trans("STATUS_ACCEPTED"), '3' => $langs->trans("STATUS_FINANCED"), '4' => $langs->trans("STATUS_EVALUATED"), '5' => $langs->trans("STATUS_CLOTURED"), '6' => $langs->trans("STATUS_REFUSED"), '9' => $langs->trans("STATUS_CANCELED"));
print $form->selectarray('object_status', $liststatus, $object_status, 1);
print '</td></tr>';

// Year
print '<tr><td>'.$langs->trans("Year").'</td><td>';
if (!in_array($year, $arrayyears)) {
	$arrayyears[$year] = $year;
}
if (!in_array($nowyear, $arrayyears)) {
	$arrayyears[$nowyear] = $nowyear;
}
arsort($arrayyears);
print $form->selectarray('year', $arrayyears, $year, 0, 0, 0, '', 0, 0, 0, '', 'width75');
print '</td></tr>';
print '<tr><td class="center" colspan="2"><input type="submit" name="submit" class="button small" value="'.$langs->trans("Refresh").'"></td></tr>';
print '</table>';
print '</form>';

print '<br><br>';

// Show array of years
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre" height="24">';
print '<td class="center">'.$langs->trans("Year").'</td>';

if ($mode == 'subvention') {
	print '<td class="right">'.$langs->trans("NbOfSubsidys").'</td>';
} elseif ($mode == 'financement') {
	print '<td class="right">'.$langs->trans("NbOfFundings").'</td>';
}

print '<td class="right">%</td>';
print '<td class="right">'.$langs->trans("AmountTotal").'</td>';
print '<td class="right">%</td>';
print '<td class="right">'.$langs->trans("AmountAverage").'</td>';
print '<td class="right">%</td>';
print '</tr>';

$oldyear = 0;
foreach ($data as $val) {
	$year = (int) $val['year'];
	while ($year && $oldyear > $year + 1) {	// If we have empty year
		$oldyear--;

		print '<tr class="oddeven" height="24">';
		print '<td align="center"><a href="'.$_SERVER["PHP_SELF"].'?year='.$oldyear.'&amp;mode='.$mode.($socid > 0 ? '&socid='.$socid : '').($userid > 0 ? '&userid='.$userid : '').'">'.$oldyear.'</a></td>';
		print '<td class="right">0</td>';
		print '<td class="right"></td>';
		print '<td class="right amount">0</td>';
		print '<td class="right"></td>';
		print '<td class="right amount">0</td>';
		print '<td class="right"></td>';
		print '</tr>';
	}

	// Calcul %	
	$greennb = (empty($val['nb_diff']) || $val['nb_diff'] >= 0);
	$greentotal = (empty($val['total_diff']) || $val['total_diff'] >= 0);
	$greenavg = (empty($val['avg_diff']) || $val['avg_diff'] >= 0);

	print '<tr class="oddeven" height="24">';
	print '<td align="center"><a href="'.$_SERVER["PHP_SELF"].'?year='.$year.'&amp;mode='.$mode.($socid > 0 ? '&socid='.$socid : '').($userid > 0 ? '&userid='.$userid : '').'">'.$year.'</a></td>';
	print '<td class="right">'.$val['nb'].'</td>';
	print '<td class="right opacitylow" style="'.($greennb ? 'color: green;' : 'color: red;').'">'.(!empty($val['nb_diff']) && $val['nb_diff'] < 0 ? '' : '+').round(!empty($val['nb_diff']) ? $val['nb_diff'] : 0).'%</td>';
	print '<td class="right"><span class="amount">'.price(price2num($val['total'], 'MT'), 1).'</span></td>';
	print '<td class="right opacitylow" style="'.($greentotal ? 'color: green;' : 'color: red;').'">'.(!empty($val['total_diff']) && $val['total_diff'] < 0 ? '' : '+').round(!empty($val['total_diff']) ? $val['total_diff'] : 0).'%</td>';
	print '<td class="right"><span class="amount">'.price(price2num($val['avg'], 'MT'), 1).'</span></td>';
	print '<td class="right opacitylow" style="'.($greenavg ? 'color: green;' : 'color: red;').'">'.(!empty($val['avg_diff']) && $val['avg_diff'] < 0 ? '' : '+').round(!empty($val['avg_diff']) ? $val['avg_diff'] : 0).'%</td>';
	print '</tr>';
	$oldyear = $year;
}

print '</table>';
print '</div>';

print '</div><div class="fichetwothirdright">';

// Show graphs
print '<table class="border centpercent"><tr class="pair nohover"><td align="center">';
if ($mesg) {
	print $mesg;
} else {
	print $px1->show();
	print "<br>\n";
	print $px2->show();
	print "<br>\n";
	print $px3->show();
}
print '</td></tr></table>';

print '</div></div>';
print '<div class="clearboth"></div>';


// End of page
llxFooter();
$db->close();
