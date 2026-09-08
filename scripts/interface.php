<?php
/* Copyright (C) 2025		François Brichart		<francois@disqutons.fr>
 * Copyright (C) 2026		Daniel Bachmann			<d.bachmann@digiconn.de>
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
 *    \file       interface.php
 *    \ingroup    subventions
 *    \brief      Page to handle jQuerry requests for subventions module
 */


/*
// FBR récupération des erreurs php
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/

if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', '1'); // Disables token renewal (AJAX endpoint, CSRF token itself is still validated)
}
if (!defined('CSRFCHECK_WITH_TOKEN')) {
	define('CSRFCHECK_WITH_TOKEN', '1'); // Force server-side CSRF token validation in main.inc.php
}

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
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] === $tmp2[$j]) {
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
dol_include_once('/custom/subventions/class/subvention.class.php');
dol_include_once('/custom/subventions/class/financement.class.php');

header('Content-Type: application/json'); // Force le type de réponse en JSON

// Récupérer les subventions (Förderungen) d'un client.
// Format: [Funding reference] – [Project name] (pas de nom client: déjà sélectionné).
// Sans client (fk_soc <= 0): aucune liste non filtrée n'est retournée.
$action = GETPOST('action', 'aZ09');
if ($action === 'getSubventionsBySoc') {
    if (!$user->hasRight('subventions', 'subvention', 'read')) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
    $fk_soc = GETPOST('fk_soc', 'int');
    $options = '<option value="0"></option>';

    if ($fk_soc > 0) {
        $sql = "SELECT s.rowid, s.ref, p.title AS project_name ";
        $sql .= "FROM ".MAIN_DB_PREFIX."subventions_subvention AS s ";
        $sql .= "LEFT JOIN ".MAIN_DB_PREFIX."projet AS p ON s.fk_project = p.rowid ";
        $sql .= "WHERE s.fk_soc = ".((int) $fk_soc);
        $sql .= " AND s.entity IN (".getEntity('subvention').")";
        $sql .= " ORDER BY s.ref ASC";
        $result = $db->query($sql);

        if ($result) {
            while ($obj = $db->fetch_object($result)) {
                $proj = !empty($obj->project_name) ? ' – '.dol_escape_htmltag($obj->project_name) : '';
                $options .= '<option value="'.((int) $obj->rowid).'">'.dol_escape_htmltag($obj->ref).$proj.'</option>';
            }
        } else {
            dol_syslog("scripts/interface.php getSubventionsBySoc SQL error: ".$db->lasterror(), LOG_ERR);
            $options .= '<option value="0">Erreur SQL</option>';
        }
    }

    echo json_encode(['success' => true, 'options' => $options]);
    exit;
}

// Récupérer les financements par subvention (Förderung).
// Format: [Förderungsreferenz] – [Projektname] (pas de nom client: déjà sélectionné).
if ($action === 'getFinancementsBySubvention') {
    if (!$user->hasRight('subventions', 'financement', 'read')) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
    $fk_sub = GETPOST('fk_sub', 'int');
    $fk_soc = GETPOST('fk_soc', 'int');
    $options = '<option value="0"></option>';

    if ($fk_sub > 0) {
        $sql = "SELECT sf.rowid, sf.ref, p.title AS project_name ";
        $sql .= "FROM ".MAIN_DB_PREFIX."subventions_financement AS sf ";
        $sql .= "LEFT JOIN ".MAIN_DB_PREFIX."subventions_subvention AS sub ON sf.fk_sub = sub.rowid ";
        $sql .= "LEFT JOIN ".MAIN_DB_PREFIX."projet AS p ON sub.fk_project = p.rowid ";
        $sql .= "WHERE sf.fk_sub = ".((int) $fk_sub);
        if ($fk_soc > 0) {
            $sql .= " AND sf.fk_soc = ".((int) $fk_soc);
        }
        $sql .= " AND sf.entity IN (".getEntity('financement').")";
        $sql .= " ORDER BY sf.ref ASC";
        $result = $db->query($sql);

        if ($result) {
            while ($obj = $db->fetch_object($result)) {
                $proj = !empty($obj->project_name) ? ' – '.dol_escape_htmltag($obj->project_name) : '';
                $options .= '<option value="'.((int) $obj->rowid).'">'.dol_escape_htmltag($obj->ref).$proj.'</option>';
            }
        } else {
            dol_syslog("scripts/interface.php getFinancementsBySubvention SQL error: ".$db->lasterror(), LOG_ERR);
            $options .= '<option value="0">Erreur SQL</option>';
        }
    }

    echo json_encode(['success' => true, 'options' => $options]);
    exit;
}

// Récupérer le fk_soc par financement
if ($action === 'getSocByFinancement') {
    if (!$user->hasRight('subventions', 'financement', 'read')) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
    $fk_fin = GETPOST('fk_fin', 'int');
    $financement = new Financement($db);
    $financement->fetch($fk_fin);

    if ($financement->id <= 0 || $financement->entity !== $conf->entity) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
    } elseif ($financement->fk_soc > 0) {
        echo json_encode(['success' => true, 'fk_soc' => $financement->fk_soc]);
    } else {
        echo json_encode(['success' => false, 'error' => 'fk_soc non défini']);
    }
    exit;
}

// Si aucune action n'est reconnue
echo json_encode(['success' => false, 'error' => 'Action non reconnue']);

