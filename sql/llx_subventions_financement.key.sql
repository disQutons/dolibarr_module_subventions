-- Copyright (C) 2025		François Brichart			<francois@disqutons.fr>
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


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_subventions_financement ADD INDEX idx_subventions_financement_rowid (rowid);
ALTER TABLE llx_subventions_financement ADD UNIQUE INDEX uk_subventions_financement_ref (ref);
ALTER TABLE llx_subventions_financement ADD INDEX idx_subventions_financement_fk_soc (fk_soc);
ALTER TABLE llx_subventions_financement ADD INDEX idx_subventions_financement_status (status);
ALTER TABLE llx_subventions_financement ADD CONSTRAINT llx_subventions_financement_fk_financeur FOREIGN KEY (fk_financeur) REFERENCES llx_c_subventions_financeur(rowid);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_subventions_financement ADD UNIQUE INDEX uk_subventions_financement_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_subventions_financement ADD CONSTRAINT llx_subventions_financement_fk_field FOREIGN KEY (fk_field) REFERENCES llx_subventions_myotherobject(rowid);
