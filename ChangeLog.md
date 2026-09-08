# CHANGELOG MODULE SUBVENTIONS POUR [DOLIBARR ERP CRM](https://www.dolibarr.org)
## 1.3
### Consolidated prior fixes (translation, database, security)
- Database: added missing `status` and `entity` columns to `llx_subventions_paiement`; fixed dictionary column mapping in `modSubventions.class.php` (missing `ref` in `tabfieldvalue`)
- Translations (DE, EN, ES, FR, IT): fixed German encoding (umlauts); added missing keys `ModuleSubventionsDesc`, `SubventionsDescription`, `AmountAverage`, `AmountTotal`, `PercentAcc`, `PercentFin`; replaced hardcoded French menu labels with translation keys; translated class field labels from French to keys; added German translations for all 10 default funding source types; fixed statistics page (`$date_creation`, hardcoded `% acc` / `% fin`); export/import fields use translation keys
- Security overhaul: removed optional permission bypass (`SUBVENTIONS_ENABLE_PERMISSION_CHECK`); enabled CSRF protection on AJAX endpoints; added field whitelists and entity access validation to `ajax/subvention.php`, `ajax/financement.php`, `ajax/paiement.php`; fixed uninitialized `$options` in `scripts/interface.php`
- Permissions: fixed swapped delete/import permissions in `modSubventions.class.php`; enabled multientity support (`ismultientitymanaged = 1`) in all three classes; enabled triggers in module descriptor
- Data integrity: foreign key constraints with `ON DELETE RESTRICT` for all relations; `model_pdf` added to paiement table and class; indexes on all foreign keys
- Status system: hardcoded French status labels replaced with translation keys (`STATUS_DRAFT` … `STATUS_CANCELED`) in all 5 languages
- Export fixes: duplicate `financement` dependency key resolved; text fields changed from "Numeric" to "Text"
- Code quality: cleaned up class field definitions; consistent label keys; dictionary config aligned

### German user interface
- Full German terminology applied (visible texts only, technical names unchanged): Zuschuss/Subvention → Förderung, Zuschüsse/Subventionen → Förderungen, Zahlung → Auszahlung, Zahlungen → Auszahlungen, Clotured → Abgeschlossen, Nombre → Anzahl, Creation → Erstellung, Neu Financement → Neue Finanzierung, Neu Paiement → Neue Auszahlung
- Fixed hardcoded leftovers (`Nombre` → `Anzahl` on grant card, `Abgelehntter` → `Abgelehnter Betrag`)
- Added missing language keys: `Financement`, `Paiement`, `Subvention`, `DeletePayment`, `Creation`, `NbOfPaiements`, `AmountOfPaiements`, `OpenAmount`, `PaiementReceipt`, `FinancementReceipt`, `DashboardKPIs`, `DocumentsModule`, `SUBVENTIONS_ENABLE_DOC_*`, `DocsDisabled`
- Removed duplicate status translations in de_DE
- Uniform field widths (customer/grant/funding/project reference, 3x amount fields) and icons (fa-hand-holding-heart, fa-handshake, fa-landmark, fa-money-bill-alt, fa-calendar-alt) on entry forms
- Customer moved to first position on funding and payment forms (field positions)

### Security (CSRF, rights, entity)
- `CSRFCHECK_WITH_TOKEN` enforced on `ajax/subvention.php`, `ajax/financement.php`, `ajax/paiement.php` and `scripts/interface.php` (server-side validation via `main.inc.php`)
- AJAX field whitelists kept and verified; no direct `$object->$field` assignment outside the whitelist
- Entity checks on all AJAX endpoints, dropdown queries, lists, dashboard, statistics and detail pages (`accessforbidden` on foreign entity)
- Removed `SUBVENTIONS_ENABLE_PERMISSION_CHECK` switch (its else-branch set rights to 1 = effectively disabled) from all 18 pages; Dolibarr rights are now always checked server-side
- `scripts/interface.php`: per-action rights check, `GETPOST()` validation, no raw `$_POST`, `exit` after JSON output

### Production bugfixes (reported errors)
- Statistics SQL error (`Unknown column 'x.$date_creation'`): date column from configuration is now validated against a whitelist in `SubventionStats` (stale `$`-prefixed values fall back to `date_creation`)
- Payment list SQL error (`Unknown column 't.status'`): upgrade `ALTER TABLE ... ADD COLUMN` statements added to the `.sql` files for columns older installations may lack (`status`, `entity`, `model_pdf` on paiement; `model_pdf` on subvention/financement) — missing columns are added on module upgrade, existing ones are kept (Dolibarr tolerates already-exists errors)
- Master data missing-column errors: covered by the same upgrade mechanism; dictionary column mapping (`ref` in `tabfieldvalue`) fixed earlier (see above)
- PHP warnings `Undefined array key "label"` in `lib/subventions.lib.php`: extrafield label access guarded with `!empty()`/`is_countable()`
- PHP deprecation `Creation of dynamic property modSubventions::$import_permission`: `$import_permission`/`$export_permission` are declared class properties

### Selection logic (customer → grant → funding → payment)
- New AJAX action `getSubventionsBySoc`: only grants of the selected customer (no unfiltered full list without customer), format `[reference – project name]`
- `getFinancementsBySubvention`: format `[reference – project name]` (no customer name), optional customer filter, rights and entity check
- Dynamic cascades on funding and payment forms (JS): customer change reloads grants and resets dependent selections; initial population on create and edit forms
- Fundings and payments are selectable via the customer and only records assigned to that customer are ever offered (server-side filtering; manipulated parameters cannot leak other customers' data)
- Icons added across all entry forms: `fa-money-bill-alt` on all amount fields, `fa-calendar-alt` on all date fields, `fa-landmark` on the funder field, plus uniform reference/project icons

### Database and classes
- Removed duplicate index definitions from `.key.sql` files (single creation at install); fixed wrong commented sample definitions
- Added foreign key `fk_financeur → llx_c_subventions_financeur` with `ON DELETE RESTRICT`; all FKs stay `RESTRICT` (no `CASCADE`)
- `model_pdf` consistent across all three tables/classes/card views
- `majstatut()`: NULL check on raw value before float cast fixed (business logic unchanged)
- `Financement::create()` / `Paiement::create()`: validate/recalc errors propagated instead of ignored
- `Financement::delete()`: recalculation via freshly loaded grant instead of deleted object; `Paiement::delete()`: error propagation
- `Subvention::delete()` uses `deleteCommon()` (RESTRICT errors returned cleanly)
- Transactions: no uncontrolled nesting (DoliDB counter mechanism verified)

### Documents and PDF
- Document management (tabs + card blocks with upload/list/download/delete) for grants, fundings and payments, each switchable via configuration (`SUBVENTIONS_ENABLE_DOC_SUBVENTION/FINANCEMENT/PAIEMENT`, defaults `1`, upgrade-safe)
- `Subvention::generateDocument()` uncommented (was dead code); `Financement::generateDocument()` freed from hardcoded `0`; `Paiement::generateDocument()` added; all three with configuration guard
- New: `pdf_standard_paiement` (payment receipt: customer/project/grant/funding/payment) and `pdf_standard_financement` (funding receipt incl. amounts/notes)
- New: `doc_generic_paiement_odt` and `doc_generic_financement_odt` with own template directories and constants; ODT sample templates shipped
- Setup: model scan deduplicated (no more double "standard") and filtered per object; document models registered for all three types on activation/upgrade; numbering-model section titles per object
- Document tabs only shown when the feature is enabled; notes tabs verified (actions_setnotes + notes.tpl)

### Dashboard and statistics
- Dashboard KPI box: counts and volumes (requested/accepted/refused/financed/paid/open) from one aggregate query per base table (no double counting), entity-safe, rights-checked
- Funder statistics page: customer and funder filters wired (backend already existed)

### Code quality
- ModuleBuilder leftover code removed (markers, define blocks, dummy comments); status TODOs and telemetry (activation ping) preserved
- Removed obsolete release archive `bin/module_subventions-1.0.zip` (outdated, not required for module operation)
- All PHP files pass `php -l`; no faulty `MAIN_DB_PREFIX."llx_*"` constructs

## 1.2 Merci @romainmp
- FIX #26 Traductions manquantes
- FIX #28 Status des subventions lorsque montant demandé est à 0
- FIX #27 Correction statistiques par financeur
- FIX #25 Corrections Warnings & Deprecated (#23)

https://github.com/disQutons/dolibarr_module_subventions/releases/tag/v1.2

## 1.1
- FIX #15 Accès aux projets
- FIX #8 Problème multi-compagnie
- FIX #16 Erreur de lien
- ADD #7 Tab for funding sources
- ADD #3 Payment list dependent on subsidy

https://github.com/disQutons/dolibarr_module_subventions/releases/tag/v1.1

## 1.0

- Gestion des subventions, demandes de financements, financeurs et paiements
- Possibilité d'ajouter des documents : demande, notification, convention, bilan, etc.
- Suivi des différents statuts : non déposé, déposé, accepté, refusé, financé, bilan déposé

- Statistiques par année, financeurs et groupe de financeurs
- [Module Projets]  Possibilité d'ajouter les subventions à la vue d'ensemble
- [Module Tiers] Ajout d'un onglet au sein de la fiche tiers des projets
