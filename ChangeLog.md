# CHANGELOG MODULE SUBVENTIONS POUR [DOLIBARR ERP CRM](https://www.dolibarr.org)
## 1.3.1
- FIX #33 Modèles de numérotation standards activés par défaut et codes raccourcis (SUB, FIN et PAI), masques de numérotation préremplis dès l'activation du module
- FIX #34 Noms explicites des fichiers d'import et d'export, et exemples de valeurs restaurés pour les financements et les paiements
- FIX Import : numérotation automatique (auto), clés des profils financement et paiement, statuts acceptés, format de la date de paiement et date de création obligatoire
- FIX Ajout automatique à l'activation des colonnes manquantes, qui ne se déclenchait jamais
- FIX Erreur SQL dans les statistiques par financeurs lorsque la date choisie appartient à la subvention
- FIX Décalage des colonnes à la modification du dictionnaire des financeurs
- FIX #50 Correction de la liste des champs éditables du dictionnaire des financeurs
- FIX #56 Affichage des infobulles d'en-tête du dictionnaire des financeurs
- FIX #46 Suppression des appels à la fonction inexistante dol_delcache() dans les triggers
- FIX #54 Traduction des modes de règlement dans la fiche et la liste des paiements via le sélecteur natif Dolibarr
- FIX #13 Harmonisation et enrichissement des infobulles d'aide (tooltips) sur les subventions, financements et paiements en français et en anglais
- FIX #53 #51 Utilisation de PaymentVarious pour le transfert comptable des paiements sans tiers auxiliaire, évitant l'erreur de tiers inconnu et la duplication du crédit 441x, et nettoyage des colonnes/méthodes obsolètes
- ADD Choix, par type de financeur, d'utiliser ou non le compte auxiliaire du tiers lors du transfert en comptabilité
- ADD Traduction complète en allemand (de_DE) reprenant et complétant la PR #32

https://github.com/disQutons/dolibarr_module_subventions/releases/tag/v1.3.1

## 1.3
- ADD #2 Support multi-projets : ventilation d'une subvention sur plusieurs projets distincts
- ADD #10 Répartition pluriannuelle : ventilation par exercice budgétaire et calcul automatique « Prorata temporis » selon la durée en mois
- ADD Affichage détaillé des ventilations (Année, Nombre de mois, montants et total) dans la vue d'ensemble du projet
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
- FIX Enregistrement automatique des événements dans l'Agenda Dolibarr (création, modification, transitions de statuts subventions/financements/paiements) et déclaration des triggers dans llx_c_action_trigger
- ADD Déclencheurs d'actions (triggers) et enregistrement Agenda pour la ventilation d'une subvention sur les projets (création, modification, suppression, répartition automatique)

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
