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
insert into chart_of_accounts set
            account_number = 1,
            join_to_table = "families",
            description = "SpringFest 10-names forfeit fee";


insert into chart_of_accounts set
            account_number = 2,
            join_to_table = "families",
            description = "SpringFest food/quilt fee";

insert into chart_of_accounts set
            account_number = 3,
            join_to_table = "families",
            description = "SpringFest auction item forfeit fee";

insert into chart_of_accounts set
            account_number = 4,
            join_to_table = "families",
            description = "SpringFest Misc Family Cash donation";

insert into chart_of_accounts set
            account_number = 5,
            join_to_table = "companies",
            description = "SpringFest Program Ad Insertion";

insert into chart_of_accounts set
            account_number = 6,
            join_to_table = "leads,companies",
            description = "SpringFest Ticket Purchase";

insert into chart_of_accounts set
            account_number = 7,
            join_to_table = "companies,leads",
            description = "SpringFest Sponsorship Package";

insert into chart_of_accounts set
            account_number = 8,
            join_to_table = "raffle_locations",
            description = "SpringFest Raffle Ticket Sales";

insert into chart_of_accounts set
            account_number = 9,
            join_to_table = "companies",
            description = "SpringFest Misc Solicitation Cash Donation";


insert into chart_of_accounts set
            account_number = 10,
            join_to_table = "leads",
            description = "SpringFest Misc Invitations Cash Donation";

insert into chart_of_accounts set
            account_number = 11,
            join_to_table = "auction_purchases",
            description = "SpringFest Auction Package Purchases";

-- groups
insert into groups set
            groupid = 1,
            name = "Parents";

insert into groups set
            groupid = 2,
            name = "Teachers";

-- events
insert into events set
            event_id = 1,
            description = "Christmas Tree Field Trip",
            notes = "Christmas tree farm. All insurance and drivers licenses must be up-to-date.",
            url = 'insurance.php'
;

insert into events set
            event_id = 2,
            description = "Parent Education Meeting",
            notes = "Mandatory meeting for all parents"
;

insert into events set
            event_id = 3,
            realm = 'nag',
            description = "Springfest Invitation Names Due",
            notes = "Each family must enter a list of 10 people to be invited to Springfest ",
            url = '10names.php'
;

insert into events set
            event_id = 4,
            realm = 'nag',
            description = "Springfest Auction Donation Items Due",
            url = 'auction.php',
            notes = "Each family must enter at least 1 auction donation online"
;

insert into events set
            event_id = 5,
            realm = 'enhancement',
            description = "Fall Enhancement Cutoff Date",
            url = 'enhancement.php',
            notes = "Each family must complete their fall enhancement hours before this date"
;


insert into events set
            event_id = 6,
            realm = 'enhancement',
            description = "Spring Enhancement Cutoff Date",
            url = 'enhancement.php',
            notes = "Each family must complete their spring enhancement hours before this date"
;


-- calendar items for this first online year. TEMP!
insert into calendar_events set
            event_id = 4,
            school_year = '2003-2004',
            event_date = "2003-12-18"
;

insert into calendar_events set
            event_id = 3,
            school_year = '2003-2004',
            event_date = "2003-11-04"
;

insert into calendar_events set
            event_id = 1,
            school_year = '2003-2004',
            event_date = "2003-12-12"
;

insert into calendar_events set
            event_id = 5,
            school_year = '2004-2005',
            event_date = "2004-12-31"
;

insert into calendar_events set
            event_id = 6,
            school_year = '2004-2005',
            event_date = "2005-06-30"
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
insert into territories set
            territory_id = 6,
            description = "Pedro Point"
;
insert into territories set
            territory_id = 7,
            description = "Palmetto"
;



-- projects
insert into enhancement_projects set
            enhancement_project_id = 1,
            project_name = "Special Indulgence Granted by Board";

insert into enhancement_projects set
            enhancement_project_id = 2,
            project_name = "Work Party";

insert into enhancement_projects set
            enhancement_project_id = 3,
            project_name = "Tour Guide";

insert into enhancement_projects set
            enhancement_project_id = 4,
            project_name = "Teacher Substitute";


insert into enhancement_projects set
            enhancement_project_id = 4,
            project_name = "Teacher Substitute";

---- sources
insert into sources  set
            source_id = 1,
            description = "Springfest 10-names entry";

insert into sources  set
            source_id = 2,
            description = "Old Alumni List";

insert into sources  set
            source_id = 3,
            description = "Misc Advertising";

insert into sources set
            source_id = 4,
            description = "Springfest Flyer";

insert into sources set
            source_id = 5,
            description = "Tribune Ad";

insert into sources set
            source_id = 6,
            description = "Tribune Article";

insert into sources set
            source_id = 7,
            description = "Ken Temporary Alumni Hack";
 
insert into sources set
            source_id = 8,
            description = "VIP List";

--- ad sizes
insert into ad_sizes set
    ad_size_description = "Back Page or Inside Front/Back Cover",
    ad_price = 250,
    school_year = '2004-2005';

insert into ad_sizes set
    ad_size_description = "Full Page",
    ad_price = 150,
    school_year = '2004-2005';


insert into ad_sizes set
    ad_size_description = "1/2 Page",
    ad_price =  85,
    school_year = '2004-2005';


insert into ad_sizes set
    ad_size_description = "1/4 Page",
    ad_price = 50,
    school_year = '2004-2005';


insert into ad_sizes set
    ad_size_description = "Business Card",
    ad_price = 30,
    school_year = '2004-2005';



--- sponsorship types
insert into sponsorship_types set
	sponsorship_type_id = 1,
	sponsorship_name = 'Angel',
	sponsorship_price = 1000,
    school_year = '2004-2005';

insert into sponsorship_types set
	sponsorship_type_id = 2,
	sponsorship_name = 'Champion',
	sponsorship_price = 500,
    school_year = '2004-2005';


insert into sponsorship_types set
	sponsorship_type_id = 3,
	sponsorship_name = 'Patron',
	sponsorship_price = 250,
    school_year = '2004-2005';


insert into sponsorship_types set
	sponsorship_type_id = 4,
	sponsorship_name = 'Friend',
	sponsorship_price = 150,
    school_year = '2004-2005';


-- tickets
insert into ticket_type set
            ticket_type_id = 1,
            description = "Paid for",
            paid_flag = 1;

insert into ticket_type set
            ticket_type_id = 2,
            description = "Included with Donation",
            paid_flag = 1;

insert into ticket_type set
            ticket_type_id = 3,
            description = "Member",
            paid_flag = 0;

insert into ticket_type set
            ticket_type_id = 4,
            description = "VIP",
            paid_flag = 0;

insert into ticket_type set
            ticket_type_id = 5,
            description = "Comp/Freebie",
            paid_flag = 0;

insert into ticket_type set
            ticket_type_id = 6,
            description = "Other",
            paid_flag = 0;

-- EOF
