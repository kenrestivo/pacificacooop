--  $Id$
--  initial semi-permanent seed data for coop database

-- Copyright (C) 2003  ken restivo <ken@restivo.org>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details. 
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


use coop;

-- accounts
insert into coa set
			acctnum = 1,
			description = "SpringFest 10-names forfeit fee";


insert into coa set
			acctnum = 2,
			description = "SpringFest food/quilt fee";

insert into coa set
			acctnum = 3,
			description = "SpringFest auction item forfeit fee";

insert into coa set
			acctnum = 4,
			description = "SpringFest Misc Cash donation";

insert into coa set
			acctnum = 5,
			description = "SpringFest Program Ad Insertion";

insert into coa set
			acctnum = 6,
			description = "SpringFest Ticket Purchase";

insert into coa set
			acctnum = 7,
			description = "SpringFest Sponsorship Package";


-- groups
insert into groups set
			groupid = 1,
			name = "Parents";

insert into groups set
			groupid = 2,
			name = "Teachers";

-- events
insert into events set
			eventid = 1,
			description = "Christmas Tree Field Trip";
			notes = "Christmas tree farm. All insurance and drivers licenses must be up-to-date.",
			url = 'insurance.php'
;

insert into events set
			eventid = 2,
			description = "Parent Ed Meeting";

insert into events set
			eventid = 3,
			description = "Springfest Invitation Names Due";
			notes = "Everyone must enter a list of 10 people to be invited to Springfest ",
			url = '10names.php'
;

insert into events set
			eventid = 4,
			description = "Springfest Auction Donation Items Due",
			url = 'auction.php',
			notes = "Everyone must enter at least 1 auction donation online"
;

-- calendar items for this first online year. TEMP!
insert into cal set
			eventid = 4,
			eventdate = "2003-12-18",
;

insert into cal set
			eventid = 3,
			eventdate = "2003-11-04"
;

insert into cal set
			eventid = 1,
			eventdate = "2003-12-12"
;

-- the territories
insert into territories set
			territory_id = 1,
			description = "Linda Mar"
;

insert into territories set
			territory_id = 2,
			description = "Manor"
;

insert into territories set
			territory_id = 3,
			description = "Eureka Square"
;

insert into territories set
			territory_id = 4,
			description = "Rockaway/Vallemar"
;

insert into territories set
			territory_id = 5,
			description = "Corporate/Financial/Government"
;


-- EOF
