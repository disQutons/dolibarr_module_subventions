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
-- Triggers declared for the subventions module in llx_c_action_trigger
--
INSERT INTO `llx_c_action_trigger` (`elementtype`, `code`, `label`, `description`, `rang`) VALUES 
('subventions', 'SUBVENTION_CREATE', 'Subvention created', 'Executed when a subvention is created', 16001),
('subventions', 'SUBVENTION_VALIDATE', 'Subvention validated', 'Executed when a subvention is validated', 16002),
('subventions', 'SUBVENTION_MODIFY', 'Subvention modified', 'Executed when a subvention is modified', 16003),
('subventions', 'SUBVENTION_ACCEPT', 'Subvention accepted', 'Executed when a subvention is accepted', 16004),
('subventions', 'SUBVENTION_REFUSE', 'Subvention refused', 'Executed when a subvention is refused', 16005),
('subventions', 'SUBVENTION_FINANCED', 'Subvention financed', 'Executed when a subvention is fully financed', 16006),
('subventions', 'SUBVENTION_EVALUATE', 'Subvention report submitted', 'Executed when a subvention evaluation report is submitted', 16007),
('subventions', 'SUBVENTION_CLOTURE', 'Subvention closed', 'Executed when a subvention is closed', 16008),
('subventions', 'SUBVENTION_CANCEL', 'Subvention canceled', 'Executed when a subvention is canceled', 16009),
('subventions', 'SUBVENTION_ARCHIVE', 'Subvention archived', 'Executed when a subvention is archived', 16010),
('subventions', 'SUBVENTION_UNVALIDATE', 'Subvention back to draft', 'Executed when a subvention is set back to draft', 16011),
('subventions', 'SUBVENTION_REOPEN', 'Subvention reopened', 'Executed when a subvention is reopened', 16012),
('subventions', 'SUBVENTION_DELETE', 'Subvention deleted', 'Executed when a subvention is deleted', 16013),
('subventions', 'SUBVENTION_VENTILATION_CREATE', 'Subvention ventilation created', 'Executed when an allocation to a project is created', 16021),
('subventions', 'SUBVENTION_VENTILATION_MODIFY', 'Subvention ventilation modified', 'Executed when an allocation to a project is modified', 16022),
('subventions', 'SUBVENTION_VENTILATION_DELETE', 'Subvention ventilation deleted', 'Executed when an allocation to a project is deleted', 16023),
('subventions', 'SUBVENTION_VENTILATION_AUTOREPARTITION', 'Subvention auto multi-year allocation', 'Executed when automatic multi-year repartition is generated', 16024),
('subventions', 'FINANCEMENT_CREATE', 'Funding created', 'Executed when a funding request is created', 16101),
('subventions', 'FINANCEMENT_VALIDATE', 'Funding validated', 'Executed when a funding request is validated', 16102),
('subventions', 'FINANCEMENT_MODIFY', 'Funding modified', 'Executed when a funding is modified', 16103),
('subventions', 'FINANCEMENT_ACCEPT', 'Funding accepted', 'Executed when a funding is accepted', 16104),
('subventions', 'FINANCEMENT_REFUSE', 'Funding refused', 'Executed when a funding is refused', 16105),
('subventions', 'FINANCEMENT_CANCEL', 'Funding canceled', 'Executed when a funding is canceled', 16106),
('subventions', 'FINANCEMENT_UNVALIDATE', 'Funding back to draft', 'Executed when a funding is set back to draft', 16107),
('subventions', 'FINANCEMENT_REOPEN', 'Funding reopened', 'Executed when a funding is reopened', 16108),
('subventions', 'FINANCEMENT_DELETE', 'Funding deleted', 'Executed when a funding is deleted', 16109),
('subventions', 'PAIEMENT_CREATE', 'Subsidy payment created', 'Executed when a subsidy payment is created', 16201),
('subventions', 'PAIEMENT_VALIDATE', 'Subsidy payment validated', 'Executed when a subsidy payment is validated', 16202),
('subventions', 'PAIEMENT_MODIFY', 'Subsidy payment modified', 'Executed when a subsidy payment is modified', 16203),
('subventions', 'PAIEMENT_DELETE', 'Subsidy payment deleted', 'Executed when a subsidy payment is deleted', 16204);