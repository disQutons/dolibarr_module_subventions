-- Migration 1.3.0 vers 1.3.1

-- Choix, par type de financeur, d'utiliser ou non le compte auxiliaire du tiers lors du transfert en comptabilité
-- La valeur par défaut (1) conserve le fonctionnement précédent
ALTER TABLE llx_c_subventions_financeur ADD COLUMN use_subledger tinyint(4) NOT NULL DEFAULT 1 AFTER accountancy_code_receivable;
