-- Copyright (C) 2025		François Brichart			<francois@disqutons.fr>
-- Copyright (C) 2026		Daniel Bachmann			    <d.bachmann@digiconn.de>
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


CREATE TABLE llx_subventions_subvention(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) DEFAULT '(PROV)' NOT NULL, 
	label varchar(255) NOT NULL, 
	montant_dem double DEFAULT NULL, 
	montant_acc double DEFAULT NULL, 
	montant_fin double DEFAULT NULL, 
	montant_att double DEFAULT NULL, 
	montant_ref double DEFAULT NULL, 
	total_ht double DEFAULT NULL, 
	total_ttc double DEFAULT NULL, 
	fk_soc integer NOT NULL, 
	fk_project integer, 
	description text, 
	evaluation text, 
	date_d_projet date, 
	date_f_projet date, 
	date_attendue date, 
	date_bilan date, 
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	last_main_doc varchar(255), 
	import_key varchar(14), 
	model_pdf varchar(255), 
	status integer DEFAULT 1 NOT NULL,
	entity integer DEFAULT 1 NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

-- Foreign keys
ALTER TABLE llx_subventions_subvention ADD CONSTRAINT fk_subventions_subvention_fk_soc FOREIGN KEY (fk_soc) REFERENCES llx_societe(rowid) ON DELETE RESTRICT;
ALTER TABLE llx_subventions_subvention ADD CONSTRAINT fk_subventions_subvention_fk_project FOREIGN KEY (fk_project) REFERENCES llx_projet(rowid) ON DELETE SET NULL;

-- Indexes
CREATE INDEX idx_subventions_subvention_fk_soc ON llx_subventions_subvention(fk_soc);
CREATE INDEX idx_subventions_subvention_fk_project ON llx_subventions_subvention(fk_project);

-- Upgrade of installations created before model_pdf existed.
-- Missing column is added, existing one is kept
-- (Dolibarr ignores "column already exists" errors when (re)running this file).
ALTER TABLE llx_subventions_subvention ADD COLUMN model_pdf varchar(255);
