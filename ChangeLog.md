# CHANGELOG MODULE SUBVENTIONS POUR [DOLIBARR ERP CRM](https://www.dolibarr.org)
## 1.3
- ADD Refonte des statuts des financements : Brouillon (0), Déposé (1), Accordé (2), Refusé (3)
- ADD Dialogue de confirmation lors du passage à Accordé (saisie de la date d'engagement et du montant accordé)
- ADD Dialogue de confirmation lors du passage à Refusé (saisie de la date de notification)
- ADD Éligibilité directe des financements accordés dans le journal de transfert des OD
- ADD Masquage des champs « Comptabilisé » et « Date d'engagement » lors de la création d'un financement ou paiement
- ADD Nouveaux boutons de transition de statut (Déposer, Accorder, Refuser, Rouvrir / Remettre en déposé)
- DEL Suppression des fonctions et boutons d'annulation de comptabilisation (unbookkeep)
- ADD Intégration comptable complète : passage par le journal de transfert comptable (accountancy_transfer_journal) selon les journaux par défaut configurés
- ADD Prise en charge des comptes de créance (441x) et produits (74xx) dans le dictionnaire des financeurs
- ADD Paramétrage de la comptabilité avancée (activation, journal OD, journal de paiement/banque, comptes par défaut)
- ADD Sélecteur de journal pour les paiements de subventions (banque/trésorerie)
- ADD Écriture comptable de paiement de subvention (Débit 512x / Crédit 441x) dans le journal de banque
- ADD Liens et boutons d'action vers le journal de transfert comptable et le Grand Livre sur les fiches et listes
- ADD Enregistrement des paiements avec sélection du compte bancaire Dolibarr et du mode de règlement
- ADD Génération automatique des écritures bancaires (llx_bank) lors des encaissements
- ADD Indicateurs d'état comptable synchronisés avec le Grand Livre sur les fiches et listes
- ADD Script de migration SQL 1.2.0-1.3.0 et auto-migration au chargement du module
- ADD Traductions complètes (fr_FR, en_US)
- FIX Correction de l'erreur fatale de redéclaration de subventionsAdminPrepareHead() sur les onglets d'administration

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
