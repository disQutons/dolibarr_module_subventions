<?php
/* Copyright (C) 2017       Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2025		François Brichart			<francois@disqutons.fr>
 * Copyright (C) 2026		Romain MP		<romain.mp@gmail.com>
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


// General defined Options
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');					// Force use of CSRF protection with tokens even for GET
//if (! defined('MAIN_AUTHENTICATION_MODE')) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined('MAIN_LANG_DEFAULT'))        define('MAIN_LANG_DEFAULT', 'auto');					// Force LANG (language) to a particular value
//if (! defined('MAIN_SECURITY_FORCECSP'))   define('MAIN_SECURITY_FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');					// Disable browser notification
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');						// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined('NOLOGIN'))                  define('NOLOGIN', '1');						// Do not use login - if this page is public (can be called outside logged session). This includes the NOIPCHECK too.
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  		// Do not load ajax.lib.php library
//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');					// Do not create database handler $db
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');					// Do not load html.form.class.php
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');					// Do not load and show top and left menu
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');					// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');					// Do not load object $langs
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');					// Do not load object $user
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');			// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');			// Do not check injection attack on POST parameters
//if (! defined('NOSESSION'))                define('NOSESSION', '1');						// On CLI mode, no need to use web sessions
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');					// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');					// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)

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

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = getDolGlobalInt('SUBVENTIONS_ENABLE_PERMISSION_CHECK');
if ($enablepermissioncheck) {
	$permissiontoread = $user->hasRight('subventions', 'paiement', 'read');
	$permissiontoadd = $user->hasRight('subventions', 'paiement', 'write'); // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->hasRight('subventions', 'paiement', 'delete') || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->hasRight('subventions', 'paiement', 'write'); // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->hasRight('subventions', 'paiement', 'write'); // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->subventions->multidir_output[isset($object->entity) ? $object->entity : 1].'/paiement';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->module, $object, $object->table_element, $object->element, 'fk_soc', 'rowid', $isdraft);
if (!isModEnabled($object->module)) {
	accessforbidden("Module ".$object->module." not enabled");
}
if (!$permissiontoread) {
	accessforbidden();
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
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/subventions/paiement_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'SUBVENTIONS_PAIEMENT_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Create bank line if payment was added or updated with a bank account and does not have a bank line yet
	if (in_array(GETPOST('action', 'aZ09'), array('add', 'update')) && !empty($object->id) && empty($object->fk_bank)) {
		$accountid = GETPOSTINT('fk_account');
		$paiement_type = GETPOSTINT('fk_paiement');
		$num_paiement = GETPOST('num_paiement', 'alphanohtml');
		if ($accountid > 0 && isModEnabled('banque')) {
			$res_bank = $object->addPaymentToBank($user, $accountid, $paiement_type, '', $num_paiement);
			if ($res_bank < 0) {
				setEventMessages($object->error, $object->errors, 'warnings');
			}
		}
	}

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOSTINT('fk_soc'), '', null, 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOSTINT('projectid'));
	}

	// Actions for accounting engagement
	if ($action == 'confirm_bookkeep' && $confirm == 'yes' && $permissiontoadd) {
		$date_engagement = dol_mktime(12, 0, 0, GETPOSTINT('date_engagementmonth'), GETPOSTINT('date_engagementday'), GETPOSTINT('date_engagementyear'));
		if (empty($date_engagement)) {
			$date_engagement = dol_now();
		}
		$journal = GETPOST('journal', 'alpha');
		$account_bank = GETPOST('account_bank', 'alpha');
		$account_receivable = GETPOST('account_receivable', 'alpha');
		$label = GETPOST('label_engagement', 'restricthtml');
		$subledger = GETPOST('subledger_account', 'alpha');

		$res = $object->bookkeep($user, $date_engagement, $journal, $account_bank, $account_receivable, $label, $subledger);
		if ($res > 0) {
			setEventMessages($langs->trans("SubventionBookkeptSuccess"), null, 'mesgs');
			header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id);
			exit;
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
			$action = '';
		}
	}

	if ($action == 'confirm_unbookkeep' && $confirm == 'yes' && $permissiontoadd) {
		$res = $object->unbookkeep($user);
		if ($res > 0) {
			setEventMessages($langs->trans("SubventionUnbookkeptSuccess"), null, 'mesgs');
			header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id);
			exit;
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
			$action = '';
		}
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
if ($action == 'create') {
	$title = $langs->trans("NewObject", $langs->transnoentitiesnoconv("Paiement"));
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

// Script pour lier les paiements aux financements en fonction de la subvention choisie
?>
<script>
jQuery(document).ready(function() {
    // Récupérer le jeton CSRF
    var csrfToken = jQuery('input[name="token"]').val();
    if (!csrfToken) {
        csrfToken = jQuery('input[name="newtoken"]').val();
    }

    // Gestion du changement de subvention
    jQuery('#fk_sub').change(function() {
        var fk_sub = jQuery(this).val();
        if (fk_sub > 0) {
            jQuery.ajax({
                url: '<?php echo dol_buildpath('/custom/subventions/scripts/interface.php', 1); ?>',
                type: 'POST',
                data: {
                    action: 'getFinancementsBySubvention',
                    fk_sub: fk_sub,
                    token: csrfToken // Ajouter le jeton CSRF
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        jQuery('#fk_fin').html(data.options);
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
    });

    // Gestion du changement de financement
    jQuery('#fk_fin').change(function() {
        var fk_fin = jQuery(this).val();
        if (fk_fin > 0) {
            jQuery.ajax({
                url: '<?php echo dol_buildpath('/custom/subventions/scripts/interface.php', 1); ?>',
                type: 'POST',
                data: {
                    action: 'getSocByFinancement',
                    fk_fin: fk_fin,
                    token: csrfToken // Ajouter le jeton CSRF
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        jQuery('#fk_soc').val(data.options);
						//jQuery('#fk_soc').trigger('change');
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

    // Fix issue #21: Chargement initial si fk_sub est déjà rempli au chargement de la page
    var initialFkSub = jQuery('#fk_sub').val();
    var initialFkFin = jQuery('#fk_fin').val();
    if (initialFkSub > 0) {
        jQuery.ajax({
            url: '<?php echo dol_buildpath("/custom/subventions/scripts/interface.php", 1); ?>',
            type: 'POST',
            data: {
                action: 'getFinancementsBySubvention',
                fk_sub: initialFkSub,
                token: csrfToken
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    jQuery('#fk_fin').html(data.options);
                    // Restaurer la sélection initiale si fk_fin était pré-rempli
                    if (initialFkFin > 0) {
                        jQuery('#fk_fin').val(initialFkFin);
                    }
                }
            }
        });
    }
});
</script>
<?php


// Part to create
if ($action == 'create') {
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
if (($id || $ref) && $action == 'edit') {
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
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$head = paiementPrepareHead($object);

	print dol_get_fiche_head($head, 'card', $langs->trans("Payment"), -1, $object->picto, 0, '', '', 0, '', 1);

	$formconfirm = '';

	// Confirmation to delete (using preloaded confirm popup)
	if ($action == 'delete' || ($conf->use_javascript_ajax && empty($conf->dol_use_jmobile))) {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeletePayment'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 'action-delete');
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}

	// Confirmation of action xxxx (You can use it for xxx = 'close', xxx = 'reopen', ...)
	if ($action == 'xxx') {
		$text = $langs->trans('ConfirmActionXxx', $object->ref);
		/*if (isModEnabled('notification'))
		{
			require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('PAIEMENT_CLOSE', $object->socid, $object);
		}*/

		$formquestion = array();

		/*
		$forcecombo=0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
			// 'text' => $langs->trans("ConfirmClone"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);
		*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// Confirmation of accounting engagement
	if ($action == 'bookkeep') {
		$default_bank_account = '512000';
		$bank_journal = '';
		if (!empty($object->fk_account)) {
			require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
			$acc_tmp = new Account($db);
			if ($acc_tmp->fetch($object->fk_account) > 0) {
				if (!empty($acc_tmp->account_number)) {
					$default_bank_account = $acc_tmp->account_number;
				}
				if (!empty($acc_tmp->fk_accountancy_journal)) {
					$sqlj_acc = "SELECT code FROM ".MAIN_DB_PREFIX."accounting_journal WHERE rowid = ".((int) $acc_tmp->fk_accountancy_journal);
					$resj_acc = $db->query($sqlj_acc);
					if ($resj_acc && ($objj_acc = $db->fetch_object($resj_acc))) {
						$bank_journal = $objj_acc->code;
					}
				}
			}
		}

		$default_receivable = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_CODE_RECEIVABLE_DEFAULT', '441000');
		if ($object->fk_fin > 0) {
			dol_include_once('/subventions/class/financement.class.php');
			$fin_tmp = new Financement($db);
			if ($fin_tmp->fetch($object->fk_fin) > 0 && $fin_tmp->fk_financeur > 0) {
				$sqlf = "SELECT accountancy_code_receivable, accountancy_code FROM ".MAIN_DB_PREFIX."c_subventions_financeur WHERE rowid = ".((int) $fin_tmp->fk_financeur);
				$resf = $db->query($sqlf);
				if ($resf && ($objf = $db->fetch_object($resf))) {
					if (!empty($objf->accountancy_code_receivable)) {
						$default_receivable = $objf->accountancy_code_receivable;
					} elseif (!empty($objf->accountancy_code)) {
						$default_receivable = $objf->accountancy_code;
					}
				}
			}
		}

		$TJournal = array();
		if (isModEnabled('accounting') || isModEnabled('accountancy')) {
			$sqlj = "SELECT code, label FROM ".MAIN_DB_PREFIX."accounting_journal WHERE active = 1 ORDER BY label";
			$resj = $db->query($sqlj);
			if ($resj) {
				while ($objj = $db->fetch_object($resj)) {
					$TJournal[$objj->code] = $objj->code.' - '.$objj->label;
				}
			}
		}
		if (empty($TJournal)) {
			$TJournal['BQ'] = 'BQ - '.$langs->trans("FinanceJournal");
		}
		$default_journal = !empty($bank_journal) ? $bank_journal : getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_JOURNAL_PAYMENT', 'BQ');
		if (!array_key_exists($default_journal, $TJournal)) {
			$default_journal = getDolGlobalString('SUBVENTIONS_ACCOUNTANCY_JOURNAL', 'OD');
			if (!array_key_exists($default_journal, $TJournal) && !empty($TJournal)) {
				$keys = array_keys($TJournal);
				$default_journal = reset($keys);
			}
		}

		$thirdparty = new Societe($db);
		if ($object->fk_soc > 0) {
			$thirdparty->fetch($object->fk_soc);
		}
		$default_subledger = !empty($thirdparty->code_compta_client) ? $thirdparty->code_compta_client : '';

		$formquestion = array(
			array('type' => 'date', 'name' => 'date_engagement', 'label' => $langs->trans("EngagementDate"), 'value' => dol_now()),
			array('type' => 'select', 'name' => 'journal', 'label' => $langs->trans("Journal"), 'values' => $TJournal, 'default' => $default_journal, 'morecss' => 'minwidth300'),
			array('type' => 'text', 'name' => 'account_bank', 'label' => $langs->trans("SubventionBankAccount").' (Débit - Classe 5)', 'value' => $default_bank_account, 'morecss' => 'minwidth200'),
			array('type' => 'text', 'name' => 'subledger_account', 'label' => $langs->trans("SubledgerAccount").' (Tiers)', 'value' => $default_subledger, 'morecss' => 'minwidth200'),
			array('type' => 'text', 'name' => 'account_receivable', 'label' => $langs->trans("SubventionReceivableAccount").' (Crédit - Classe 4)', 'value' => $default_receivable, 'morecss' => 'minwidth200'),
			array('type' => 'text', 'name' => 'label_engagement', 'label' => $langs->trans("Label"), 'value' => $langs->trans("SubventionPayment").': '.$object->ref.' ('.$thirdparty->name.')', 'morecss' => 'centpercent minwidth400'),
		);

		$text = $langs->trans("ConfirmBookkeepPayment", price($object->montant, 0, $langs, 1, -1, -1, $conf->currency));
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans("BookkeepPayment"), $text, 'confirm_bookkeep', $formquestion, 'yes', 1, 'auto', 780);
	}

	if ($action == 'unbookkeep') {
		$text = $langs->trans("ConfirmUnbookkeepPayment");
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans("UnbookkeepPayment"), $text, 'confirm_unbookkeep', array(), 'yes', 1, 'auto', 550);
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

	if (!empty($object->fk_bank) && isModEnabled('banque')) {
		require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
		$acc_line = new AccountLine($db);
		if ($acc_line->fetch($object->fk_bank) > 0) {
			print '<tr><td class="titlefield">'.$langs->trans("BankTransactionLine").'</td>';
			print '<td><a href="'.DOL_URL_ROOT.'/compta/bank/line.php?rowid='.$object->fk_bank.'"><i class="fa fa-university"></i> '.$langs->trans("ShowBankTransaction").' (#'.$object->fk_bank.')</a></td></tr>';
		}
	}

	if (getDolGlobalInt('SUBVENTIONS_ACCOUNTANCY_ENABLED') && (isModEnabled('accounting') || isModEnabled('accountancy'))) {
		print '<tr><td class="titlefield">'.$langs->trans("Accounted").'</td>';
		if (!empty($object->accounted)) {
			print '<td><span class="badge badge-status4 badge-status"><i class="fa fa-check"></i> '.$langs->trans("Accounted").'</span>';
			if (!empty($object->date_engagement)) {
				print ' <span class="opacitymedium">('.$langs->trans("EngagementDate").': '.dol_print_date($object->date_engagement, 'day').')</span>';
			}
			print '</td></tr>';
		} else {
			print '<td><span class="badge badge-status0 badge-status">'.$langs->trans("NotAccounted").'</span></td></tr>';
		}
	}

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

		print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '' : '#line_'.GETPOSTINT('lineid')).'" method="POST">
		<input type="hidden" name="token" value="' . newToken().'">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="page_y" value="">
		<input type="hidden" name="id" value="' . $object->id.'">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines)) {
			$object->printObjectLines($action, $mysoc, null, GETPOSTINT('lineid'), 1);
		}

		// Form to add new line
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines') {
			if ($action != 'editline') {
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

		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}


	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {
			// Modify
			print dolGetButtonAction('', $langs->trans('Modify'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);

			// Accounting engagement (OD)
			if (getDolGlobalInt('SUBVENTIONS_ACCOUNTANCY_ENABLED') && (isModEnabled('accounting') || isModEnabled('accountancy'))) {
				if (empty($object->accounted) && !empty($object->montant) && $object->montant > 0) {
					print dolGetButtonAction('', $langs->trans('BookkeepInLedger'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=bookkeep&token='.newToken(), '', $permissiontoadd);
				} elseif (!empty($object->accounted)) {
					print dolGetButtonAction('', $langs->trans('UnbookkeepInLedger'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=unbookkeep&token='.newToken(), '', $permissiontoadd);
					print dolGetButtonAction('', $langs->trans('ViewInLedger'), 'default', DOL_URL_ROOT.'/accountancy/bookkeeping/list.php?search_doc_ref='.urlencode($object->ref), '', 1);
				}
			}

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
}

// End of page
llxFooter();
$db->close();
