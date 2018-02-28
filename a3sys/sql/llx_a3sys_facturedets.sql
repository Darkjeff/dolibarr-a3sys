-- ========================================================================
-- <one line to give the program's name and a brief idea of what it does.>
-- Copyright (C) <2017> SaaSprov.ma <saasprov@gmail.com>
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
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.
--
-- ========================================================================


CREATE TABLE IF NOT EXISTS `llx_a3sys_facturedets` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `N_fact` int(11) NOT NULL,
  `ref_article` int(11) NULL,
  `libelle_article` varchar(255) NULL,
  `qty` int(11) NULL,
  `pu_ttc` double(10,2)  NULL,
  `montant_ligne` double(10,2)  NULL,
  `tva` double(10,2)  NULL,
  PRIMARY KEY (`rowid`)
)ENGINE=InnoDB;
    