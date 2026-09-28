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


ALTER TABLE llx_subventions_subvention_projet ADD UNIQUE INDEX uk_subventions_sub_proj (fk_subvention, fk_project, annee);
ALTER TABLE llx_subventions_subvention_projet ADD INDEX idx_subventions_sub_proj_fk_project (fk_project);
ALTER TABLE llx_subventions_subvention_projet ADD INDEX idx_subventions_sub_proj_annee (annee);
ALTER TABLE llx_subventions_subvention_projet ADD CONSTRAINT fk_subventions_sub_proj_subvention FOREIGN KEY (fk_subvention) REFERENCES llx_subventions_subvention (rowid);
ALTER TABLE llx_subventions_subvention_projet ADD CONSTRAINT fk_subventions_sub_proj_project FOREIGN KEY (fk_project) REFERENCES llx_projet (rowid);
