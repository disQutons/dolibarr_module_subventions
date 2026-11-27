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
-- along with this program.  If not, see <https://www.gnu.org/licenses/>.
--
-- Default contact types declared for the subventions module in llx_c_type_contact
--

INSERT INTO llx_c_type_contact (element, source, code, libelle, active, module, position) VALUES ('subvention', 'internal', 'SUBVENTIONLEADER', 'Subsidy manager', 1, 'subventions', 10);
INSERT INTO llx_c_type_contact (element, source, code, libelle, active, module, position) VALUES ('subvention', 'internal', 'SUBVENTIONCONTRIBUTOR', 'Subsidy contributor', 1, 'subventions', 20);
INSERT INTO llx_c_type_contact (element, source, code, libelle, active, module, position) VALUES ('subvention', 'internal', 'SUBVENTIONACCOUNTING', 'Administrative and financial follow-up', 1, 'subventions', 30);
INSERT INTO llx_c_type_contact (element, source, code, libelle, active, module, position) VALUES ('subvention', 'external', 'SUBVENTIONCONTACT', 'Funder contact', 1, 'subventions', 10);
INSERT INTO llx_c_type_contact (element, source, code, libelle, active, module, position) VALUES ('subvention', 'external', 'SUBVENTIONINSTRUCTOR', 'Grant case officer', 1, 'subventions', 20);
INSERT INTO llx_c_type_contact (element, source, code, libelle, active, module, position) VALUES ('subvention', 'external', 'SUBVENTIONSIGNATORY', 'Signatory', 1, 'subventions', 30);
INSERT INTO llx_c_type_contact (element, source, code, libelle, active, module, position) VALUES ('financement', 'internal', 'FINANCEMENTLEADER', 'Funding manager', 1, 'subventions', 10);
INSERT INTO llx_c_type_contact (element, source, code, libelle, active, module, position) VALUES ('financement', 'external', 'FINANCEMENTCONTACT', 'Funder contact', 1, 'subventions', 10);
INSERT INTO llx_c_type_contact (element, source, code, libelle, active, module, position) VALUES ('financement', 'external', 'FINANCEMENTINSTRUCTOR', 'Funding officer', 1, 'subventions', 20);
INSERT INTO llx_c_type_contact (element, source, code, libelle, active, module, position) VALUES ('paiement', 'internal', 'PAIEMENTLEADER', 'Payment follow-up manager', 1, 'subventions', 10);
INSERT INTO llx_c_type_contact (element, source, code, libelle, active, module, position) VALUES ('paiement', 'external', 'PAIEMENTCONTACT', 'Funder accounting contact', 1, 'subventions', 10);
