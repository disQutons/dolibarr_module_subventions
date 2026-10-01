-- Copyright (C) 2026		Romain MP		<romain.mp@gmail.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.

-- Migration 1.2.0 to 1.3.0

-- Table llx_c_subventions_financeur
ALTER TABLE llx_c_subventions_financeur ADD COLUMN accountancy_code_receivable varchar(32) DEFAULT NULL AFTER accountancy_code;

UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4410' WHERE ref = 'SF_AUTRE' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4411' WHERE ref = 'SF_ETAT' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4412' WHERE ref = 'SF_REG' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4413' WHERE ref = 'SF_DEP' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4414' WHERE ref = 'SF_COM' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4418' WHERE ref = 'SF_SOC' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4415' WHERE ref = 'SF_EUR' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4416' WHERE ref = 'SF_ASP' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4418' WHERE ref = 'SF_PUB' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4419' WHERE ref = 'SF_PRI' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');

-- Table llx_subventions_financement
ALTER TABLE llx_subventions_financement ADD COLUMN accounted tinyint DEFAULT 0;
ALTER TABLE llx_subventions_financement ADD COLUMN date_engagement date DEFAULT NULL;
ALTER TABLE llx_subventions_financement ADD COLUMN fk_bookkeeping_receivable integer DEFAULT NULL;
ALTER TABLE llx_subventions_financement ADD COLUMN fk_bookkeeping_product integer DEFAULT NULL;

-- Table llx_subventions_paiement
ALTER TABLE llx_subventions_paiement ADD COLUMN fk_bank integer DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN fk_account integer DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN fk_paiement integer DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN num_paiement varchar(50) DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN accounted tinyint DEFAULT 0;
ALTER TABLE llx_subventions_paiement ADD COLUMN date_engagement date DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN fk_bookkeeping_bank integer DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN fk_bookkeeping_receivable integer DEFAULT NULL;

-- Create multi-project & multi-year allocation table
CREATE TABLE IF NOT EXISTS llx_subventions_subvention_projet(
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
	fk_subvention integer NOT NULL,
	fk_project integer NOT NULL,
	annee integer DEFAULT NULL,
	nb_mois integer DEFAULT NULL,
	amount double(24,8) NOT NULL DEFAULT 0,
	note text,
	datec datetime NOT NULL,
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	fk_user_creat integer NOT NULL,
	fk_user_modif integer,
	entity integer DEFAULT 1 NOT NULL
) ENGINE=innodb;

-- Columns annee and nb_mois if table already exists
ALTER TABLE llx_subventions_subvention_projet ADD COLUMN annee integer DEFAULT NULL;
ALTER TABLE llx_subventions_subvention_projet ADD COLUMN nb_mois integer DEFAULT NULL;
ALTER TABLE llx_subventions_subvention_projet ADD COLUMN entity integer DEFAULT 1 NOT NULL;

-- Drop old unique index if exists and recreate with annee
ALTER TABLE llx_subventions_subvention_projet DROP INDEX uk_subventions_sub_proj;
ALTER TABLE llx_subventions_subvention_projet ADD UNIQUE INDEX uk_subventions_sub_proj (fk_subvention, fk_project, annee);
ALTER TABLE llx_subventions_subvention_projet ADD INDEX idx_subventions_sub_proj_fk_project (fk_project);
ALTER TABLE llx_subventions_subvention_projet ADD INDEX idx_subventions_sub_proj_annee (annee);

-- Migrate existing single-project links to the junction table
INSERT IGNORE INTO llx_subventions_subvention_projet (fk_subvention, fk_project, amount, datec, fk_user_creat, entity)
SELECT rowid, fk_project, COALESCE(montant_acc, 0), NOW(), fk_user_creat, entity
FROM llx_subventions_subvention
WHERE fk_project IS NOT NULL AND fk_project > 0;
