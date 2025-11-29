-- Copyright (C) 2025		François Brichart			
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
	fk_fin integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
-- Création de la table c_subventions_financeur
CREATE TABLE llx_c_subventions_financeur (
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    ref varchar(12) NOT NULL,
    label varchar(128) NOT NULL,
    accountancy_code varchar(32) DEFAULT NULL,
    active tinyint(4) NOT NULL DEFAULT 1,
    module varchar(32) DEFAULT NULL,
    position int(11) NOT NULL DEFAULT 0,
    type int(11) DEFAULT 0
) ENGINE=innodb;

-- Insertion des données par défaut
INSERT INTO llx_c_subventions_financeur (`rowid`, `ref`, `label`, `accountancy_code`, `active`, `module`, `position`, `type`)
VALUES
	(1, 'SF_AUTRE', 'Autre', '7400', 1, NULL, 1, 0),
    (2, 'SF_ETAT', 'État', '7401', 1, NULL, 2, 0),
    (3, 'SF_REG', 'Région', '7402', 1, NULL, 3, 0),
    (4, 'SF_DEP', 'Département', '7403', 1, NULL, 4, 0),
    (5, 'SF_COM', 'Commune', '7404', 1, NULL, 5, 0),
    (6, 'SF_SOC', 'Organismes sociaux (CAF, etc.)', '7408', 1, NULL, 6, 0),
    (7, 'SF_EUR', 'Fonds européens (FSE, FEDER, etc.)', '7405', 1, NULL, 7, 0),
    (8, 'SF_ASP', 'L\'agence de services et de paiement (emplois aidés)', '7406', 1, NULL, 8, 0),
    (9, 'SF_PUB', 'Autres établissements publics', '7408', 1, NULL, 9, 0),
    (10, 'SF_PRI', 'Aides privées (fondation)', '7409', 1, NULL, 10, 0);
