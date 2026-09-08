-- Copyright (C) 2025		François Brichart       <francois@disqutons.fr>
-- Copyright (C) 2026		Daniel Bachmann			<d.bachmann@digiconn.de>
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


CREATE TABLE llx_subventions_paiement(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) DEFAULT '(PROV)' NOT NULL, 
	montant double NOT NULL, 
	datep date NOT NULL, 
	fk_soc integer, 
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	last_main_doc varchar(255), 
	import_key varchar(14), 
	model_pdf varchar(255), 
	fk_sub integer, 
	fk_fin integer, 
	status integer DEFAULT 1 NOT NULL,
	entity integer DEFAULT 1 NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

-- Foreign keys
ALTER TABLE llx_subventions_paiement ADD CONSTRAINT fk_subventions_paiement_fk_sub FOREIGN KEY (fk_sub) REFERENCES llx_subventions_subvention(rowid) ON DELETE RESTRICT;
ALTER TABLE llx_subventions_paiement ADD CONSTRAINT fk_subventions_paiement_fk_fin FOREIGN KEY (fk_fin) REFERENCES llx_subventions_financement(rowid) ON DELETE RESTRICT;
ALTER TABLE llx_subventions_paiement ADD CONSTRAINT fk_subventions_paiement_fk_soc FOREIGN KEY (fk_soc) REFERENCES llx_societe(rowid) ON DELETE RESTRICT;

-- Indexes
CREATE INDEX idx_subventions_paiement_fk_sub ON llx_subventions_paiement(fk_sub);
CREATE INDEX idx_subventions_paiement_fk_fin ON llx_subventions_paiement(fk_fin);
CREATE INDEX idx_subventions_paiement_fk_soc ON llx_subventions_paiement(fk_soc);

-- Upgrade of installations created before these columns existed.
-- Missing columns are added, existing ones are kept
-- (Dolibarr ignores "column already exists" errors when (re)running this file).
ALTER TABLE llx_subventions_paiement ADD COLUMN model_pdf varchar(255);
ALTER TABLE llx_subventions_paiement ADD COLUMN status integer DEFAULT 1 NOT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN entity integer DEFAULT 1 NOT NULL;
