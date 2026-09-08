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


CREATE TABLE llx_subventions_financement(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) DEFAULT '(PROV)' NOT NULL, 
	fk_soc integer NOT NULL, 
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
	montant_dem double DEFAULT NULL, 
	montant_acc double DEFAULT NULL, 
	montant_fin double DEFAULT NULL, 
	montant_att double DEFAULT NULL, 
	montant_ref double DEFAULT NULL, 
	fk_sub integer NOT NULL, 
	fk_financeur integer NOT NULL,
	entity integer DEFAULT 1 NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

-- Foreign keys
ALTER TABLE llx_subventions_financement ADD CONSTRAINT fk_subventions_financement_fk_sub FOREIGN KEY (fk_sub) REFERENCES llx_subventions_subvention(rowid) ON DELETE RESTRICT;
ALTER TABLE llx_subventions_financement ADD CONSTRAINT fk_subventions_financement_fk_soc FOREIGN KEY (fk_soc) REFERENCES llx_societe(rowid) ON DELETE RESTRICT;
ALTER TABLE llx_subventions_financement ADD CONSTRAINT fk_subventions_financement_fk_financeur FOREIGN KEY (fk_financeur) REFERENCES llx_c_subventions_financeur(rowid) ON DELETE RESTRICT;

-- Indexes
CREATE INDEX idx_subventions_financement_fk_sub ON llx_subventions_financement(fk_sub);
CREATE INDEX idx_subventions_financement_fk_soc ON llx_subventions_financement(fk_soc);
CREATE INDEX idx_subventions_financement_fk_financeur ON llx_subventions_financement(fk_financeur);

-- Upgrade of installations created before model_pdf existed.
-- Missing column is added, existing one is kept
-- (Dolibarr ignores "column already exists" errors when (re)running this file).
ALTER TABLE llx_subventions_financement ADD COLUMN model_pdf varchar(255);
