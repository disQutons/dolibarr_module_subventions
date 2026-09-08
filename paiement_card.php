<?php
/* Copyright (C) 2017       Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2025		François Brichart		<francois@disqutons.fr>
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
 *    \file       paiement_card.php
 *    \ingroup    subventions
 *    \brief      Page to create/edit/view paiement
 */



/*
// FBR récupération des erreurs php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/subventions/class/paiement.class.php');
dol_include_once('/subventions/lib/subventions_paiement.lib.php');

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Societe $mysoc
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array("subventions@subventions", "other"));

// Get parameters
$id = GETPOSTINT('id');
$ref = GETPOST('ref', 'alpha');
$lineid   = GETPOSTINT('lineid');

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php')); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');					// if not set, a default page will be used
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');	// if not set, $backtopage will be used
$backtopagejsfields = GETPOST('backtopagejsfields', 'alpha');
$optioncss = GETPOST('optioncss', 'aZ'); // Option for the css output (always '' except when 'print')
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');
$sub = GETPOST('origin', 'aZ09');

if (!empty($backtopagejsfields)) {
	$tmpbacktopagejsfields = explode(':', $backtopagejsfields);
	$dol_openinpopup = preg_replace('/[^a-z0-9_]/i', '', $tmpbacktopagejsfields[0]);
}

// Initialize a technical objects
$object = new Paiement($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->subventions->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array($object->element.'card', 'globalcard')); // Note that conf->hooks_modules contains array
$soc = null;

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);


$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criteria
$search_all = trim(GETPOST("search_all", 'alpha'));
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be 'include', not 'include_once'.

// Permissions are always checked server-side (no optional bypass).
$permissiontoread = $user->hasRight('subventions', 'paiement', 'read');
$permissiontoadd = $user->hasRight('subventions', 'paiement', 'write'); // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->hasRight('subventions', 'paiement', 'delete') || ($permissiontoadd && isset($object->status) && $object->status === $object::STATUS_DRAFT);
$permissionnote = $user->hasRight('subventions', 'paiement', 'write'); // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->hasRight('subventions', 'paiement', 'write'); // Used by the include of actions_dellink.inc.php

$upload_dir = $conf->subventions->multidir_output[isset($object->entity) ? $object->entity : 1].'/paiement';

// Security check (enable the most restrictive one)
if (!isModEnabled($object->module)) {
	accessforbidden("Module ".$object->module." not enabled");
}
if (!$permissiontoread) {
	accessforbidden();
}
// Entity security: never display or modify records from another entity
if (!empty($object->id) && isset($object->entity) && !in_array((int) $object->entity, array_map('intval', explode(',', getEntity($object->element))))) {
	accessforbidden('NotAllowedToAccessEntity');
}

$error = 0;


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$backurlforlist = dol_buildpath('/subventions/paiement_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action !== 'add' && $action !== 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/subventions/paiement_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'SUBVENTIONS_PAIEMENT_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action === 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOSTINT('fk_soc'), '', null, 'date', '', $user, $triggermodname);
	}
	if ($action === 'classin' && $permissiontoadd) {
		$object->setProject(GETPOSTINT('projectid'));
	}
}




/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Payment")." - ".$langs->trans('Card');
//$title = $object->ref." - ".$langs->trans('Card');
if ($action === 'create') {
	$title = $langs->trans("NewPayment", $langs->transnoentitiesnoconv("NewPayment"));
}
$help_url = '';

llxHeader('', $title, $help_url, '', 0, 0, '', '', '', 'mod-subventions page-card');

// Example : Adding jquery code
// print '<script type="text/javascript">
// jQuery(document).ready(function() {
// 	function init_myfunc()
// 	{
// 		jQuery("#myid").removeAttr(\'disabled\');
// 		jQuery("#myid").attr(\'disabled\',\'disabled\');
// 	}
// 	init_myfunc();
// 	jQuery("#mybutton").click(function() {
// 		init_myfunc();
// 	});
// });
// </script>';

// Script pour la cascade Client → Subvention → Financement dans les paiements.
// Formats : subventions [Référence – Projet], financements [Référence – Projet] (sans nom client).
?>
<script>
jQuery(document).ready(function() {
    if (!jQuery('#fk_soc').length || !jQuery('#fk_sub').length || !jQuery('#fk_fin').length) {
        return;
    }
    // Récupérer le jeton CSRF
    var csrfToken = jQuery('input[name="token"]').val();
    if (!csrfToken) {
        csrfToken = jQuery('input[name="newtoken"]').val();
    }
    var interfaceUrl = '<?php echo dol_buildpath('/custom/subventions/scripts/interface.php', 1); ?>';

    function loadSubventions(fk_soc, selectedFkSub, afterLoad) {
        if (!(fk_soc > 0)) {
            jQuery('#fk_sub').html('<option value="0"></option>');
            jQuery('#fk_fin').html('<option value="0"></option>');
            return;
        }
        jQuery.ajax({
            url: interfaceUrl,
            type: 'POST',
            data: {
                action: 'getSubventionsBySoc',
                fk_soc: fk_soc,
                token: csrfToken // Jeton CSRF
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    jQuery('#fk_sub').html(data.options);
                    if (selectedFkSub > 0 && jQuery('#fk_sub option[value="' + selectedFkSub + '"]').length) {
                        jQuery('#fk_sub').val(selectedFkSub);
                    }
                    if (typeof afterLoad === 'function') {
                        afterLoad();
                    }
                } else {
                    console.error('Erreur : ', data.error);
                    jQuery('#fk_sub').html('<option value="0">Erreur</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX : ', error);
                jQuery('#fk_sub').html('<option value="0">Erreur AJAX</option>');
            }
        });
    }

    function loadFinancements(fk_sub, fk_soc, selectedFkFin) {
        if (!(fk_sub > 0)) {
            jQuery('#fk_fin').html('<option value="0"></option>');
            return;
        }
        jQuery.ajax({
            url: interfaceUrl,
            type: 'POST',
            data: {
                action: 'getFinancementsBySubvention',
                fk_sub: fk_sub,
                fk_soc: fk_soc,
                token: csrfToken // Jeton CSRF
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    jQuery('#fk_fin').html(data.options);
                    if (selectedFkFin > 0 && jQuery('#fk_fin option[value="' + selectedFkFin + '"]').length) {
                        jQuery('#fk_fin').val(selectedFkFin);
                    }
                } else {
                    console.error('Erreur : ', data.error);
                    jQuery('#fk_fin').html('<option value="0">Erreur</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX : ', error);
                jQuery('#fk_fin').html('<option value="0">Erreur AJAX</option>');
            }
        });
    }

    var lastSoc = jQuery('#fk_soc').val();
    // Changement de client : subventions filtrées, sélections dépendantes réinitialisées
    jQuery('#fk_soc').change(function() {
        var fk_soc = jQuery(this).val();
        if (fk_soc === lastSoc) {
            return;
        }
        lastSoc = fk_soc;
        jQuery('#fk_fin').html('<option value="0"></option>');
        loadSubventions(fk_soc, 0, null);
    });

    // Changement de subvention : financements de cette subvention (et de ce client)
    jQuery('#fk_sub').change(function() {
        loadFinancements(jQuery(this).val(), jQuery('#fk_soc').val(), 0);
    });

    // Gestion du changement de financement : client associé (cohérence)
    jQuery('#fk_fin').change(function() {
        var fk_fin = jQuery(this).val();
        if (fk_fin > 0) {
            jQuery.ajax({
                url: interfaceUrl,
                type: 'POST',
                data: {
                    action: 'getSocByFinancement',
                    fk_fin: fk_fin,
                    token: csrfToken // Jeton CSRF
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        lastSoc = data.fk_soc;
                        jQuery('#fk_soc').val(data.fk_soc).trigger('change.select2');
                    } else {
                        console.error('Erreur : ', data.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur AJAX : ', error);
                }
            });
        }
    });

    // Initialisation (création et édition) : sans client, aucune liste globale
    var initSoc = jQuery('#fk_soc').val();
    var initSub = jQuery('#fk_sub').val();
    var initFin = jQuery('#fk_fin').val();
    if (initSoc > 0) {
        loadSubventions(initSoc, initSub, function() {
            var currentSub = jQuery('#fk_sub').val();
            if (currentSub > 0) {
                loadFinancements(currentSub, initSoc, initFin);
            } else {
                jQuery('#fk_fin').html('<option value="0"></option>');
            }
        });
    } else {
        jQuery('#fk_sub').html('<option value="0"></option>');
        jQuery('#fk_fin').html('<option value="0"></option>');
    }
});
</script>
<?php


// Part to create
if ($action === 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden('NotEnoughPermissions', 0, 1);
	}

	print load_fiche_titre($title, '', $object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}
	if ($backtopagejsfields) {
		print '<input type="hidden" name="backtopagejsfields" value="'.$backtopagejsfields.'">';
	}
	if ($dol_openinpopup) {
		print '<input type="hidden" name="dol_openinpopup" value="'.$dol_openinpopup.'">';
	}

	print dol_get_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action === 'edit') {
	print load_fiche_titre($langs->trans("Payment"), '', $object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action !== 'edit' && $action !== 'create'))) {
	$head = paiementPrepareHead($object);

	print dol_get_fiche_head($head, 'card', $langs->trans("Payment"), -1, $object->picto, 0, '', '', 0, '', 1);

	$formconfirm = '';

	// Confirmation to delete (using preloaded confirm popup)
	if ($action === 'delete' || ($conf->use_javascript_ajax && empty($conf->dol_use_jmobile))) {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeletePayment'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 'action-delete');
	}
	// Confirmation to delete line
	if ($action === 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}

	// Confirmation of action xxxx (You can use it for xxx = 'close', xxx = 'reopen', ...)
	if ($action === 'xxx') {
		$text = $langs->trans('ConfirmActionXxx', $object->ref);
		/*if (isModEnabled('notification'))
		{
			require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('PAIEMENT_CLOSE', $object->socid, $object);
		}*/

		$formquestion = array();

		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	if (empty($sub)){
		$linkback = '<a href="'.dol_buildpath('/subventions/paiement_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';
	}
	else {
		$linkback = '<a href="'.dol_buildpath('/subventions/subvention_card.php', 1).'?id='.$sub.'">'.$langs->trans("BackToSub").'</a>';
	}

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();


	/*
	 * Lines
	 */

	if (!empty($object->table_element_line)) {
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action !== 'editline') ? '' : '#line_'.GETPOSTINT('lineid')).'" method="POST">
		<input type="hidden" name="token" value="' . newToken().'">
		<input type="hidden" name="action" value="' . (($action !== 'editline') ? 'addline' : 'updateline').'">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="page_y" value="">
		<input type="hidden" name="id" value="' . $object->id.'">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status === 0) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status === $object::STATUS_DRAFT && $permissiontoadd && $action !== 'selectlines' && $action !== 'editline')) {
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines)) {
			$object->printObjectLines($action, $mysoc, null, GETPOSTINT('lineid'), 1);
		}

		// Form to add new line
		if ($object->status === 0 && $permissiontoadd && $action !== 'selectlines') {
			if ($action !== 'editline') {
				// Add products/services form

				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
				if ($reshook < 0) {
					setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
				}
				if (empty($reshook)) {
					$object->formAddObjectLine(1, $mysoc, $soc);
				}
			}
		}

		if (!empty($object->lines) || ($object->status === $object::STATUS_DRAFT && $permissiontoadd && $action !== 'selectlines' && $action !== 'editline')) {
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}


	// Buttons for actions
	if ($action !== 'presend' && $action !== 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {
			// Modify
			print dolGetButtonAction('', $langs->trans('Modify'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);

			// Delete (with preloaded confirm popup)
			$deleteUrl = $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=delete&token='.newToken();
			$buttonId = 'action-delete-no-ajax';
			if ($conf->use_javascript_ajax && empty($conf->dol_use_jmobile)) {	// We can use preloaded confirm if not jmobile
				$deleteUrl = '';
				$buttonId = 'action-delete';
			}
			$params = array();
			print dolGetButtonAction('', $langs->trans("Delete"), 'delete', $deleteUrl, $buttonId, $permissiontodelete, $params);
		}
		print '</div>'."\n";
	}

	if ($action !== 'presend') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		$includedocgeneration = getDolGlobalInt('SUBVENTIONS_ENABLE_DOC_PAIEMENT', 1);

		// Documents (upload, list, download, delete, PDF receipt via document tab models)
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref.'/'.$objref.'.pdf';
			$filedir = $conf->subventions->dir_output.'/'.$object->element.'/'.$objref;
			$urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
			$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
			$delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
			print $formfile->showdocuments('subventions:Paiement', $object->element.'/'.$objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		}

		print '</div><div class="fichehalfright">';
		print '</div></div>';
	}
}

// End of page
llxFooter();
$db->close();
