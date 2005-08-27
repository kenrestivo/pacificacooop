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


--- realms
INSERT INTO `realms` VALUES 
(1,'auction'),
(2,'calendar'),
(3,'enhancement'),
(4,'flyers'),
(5,'insurance'),
(6,'invitations'),
(7,'invitations_cash'),
(8,'jobs'),
(9,'money'),
(10,'nag'),
(11,'packaging'),
(12,'program'),
(13,'raffle'),
(14,'roster'),
(15,'solicitation'),
(16,'solicit_money'),
(17,'thankyou'),
(18,'tickets'),
(19,'user');



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
            realm_id = 10,
            description = "Springfest Invitation Names Due",
            notes = "Each family must enter a list of 10 people to be invited to Springfest ",
            url = '10names.php'
;

insert into events set
            event_id = 4,
            realm_id = 10,
            description = "Springfest Auction Donation Items Due",
            url = 'auction.php',
            notes = "Each family must enter at least 1 auction donation online"
;

insert into events set
            event_id = 5,
            realm_id = 3,
            description = "Fall Enhancement Cutoff Date",
            url = 'enhancement.php',
            notes = "Each family must complete their fall enhancement hours before this date"
;


insert into events set
            event_id = 6,
            realm_id = 3,
            description = "Spring Enhancement Cutoff Date",
            url = 'enhancement.php',
            notes = "Each family must complete their spring enhancement hours before this date"
;

insert into events set
            event_id = 7,
            realm_id = 10,
            description = "Springfest",
            url = 'public_auction.php',
            notes = "The date of this year's Springfest event"
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

insert into calendar_events set
            event_id = 7,
            school_year = '2004-2005',
            event_date = "2005-03-19"
;

insert into calendar_events set
            event_id = 5,
            school_year = '2005-2006',
            event_date = "2005-12-31"
;

insert into calendar_events set
            event_id = 6,
            school_year = '2005-2006',
            event_date = "2006-06-30"
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

insert into sources set
            source_id = 9,
            description = "Solicitation";

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

insert into sponsorship_types set
	sponsorship_type_id = 5,
	sponsorship_name = 'Angel',
	sponsorship_price = 1000,
    school_year = '2003-2004';

insert into sponsorship_types set
	sponsorship_type_id = 6,
	sponsorship_name = 'Champion',
	sponsorship_price = 500,
    school_year = '2003-2004';


insert into sponsorship_types set
	sponsorship_type_id = 7,
	sponsorship_name = 'Patron',
	sponsorship_price = 250,
    school_year = '2003-2004';


insert into sponsorship_types set
	sponsorship_type_id = 8,
	sponsorship_name = 'Friend',
	sponsorship_price = 150,
    school_year = '2003-2004';


-- tickets
insert into ticket_type set
            ticket_type_id = 1,
            description = "Paid for",
            jointable_hack = 'leads,companies', 
            paid_flag = 'Yes';

insert into ticket_type set
            ticket_type_id = 2,
            description = "Included with Donation",
            jointable_hack = 'leads,companies', 
            paid_flag = 'Yes';

insert into ticket_type set
            ticket_type_id = 3,
            description = "Member",
            jointable_hack = 'families',    
            paid_flag = 'No';

insert into ticket_type set
            ticket_type_id = 4,
            description = "Comp/Freebie/VIP",
            jointable_hack = 'leads,companies', 
            paid_flag = 'No';

insert into ticket_type set
            ticket_type_id = 5,
            description = "Other",
            paid_flag = 'No';

-----counter table
insert into counters set
            column_name = 'paddle_number',
            school_year = '2003-2004',
            counter = 907;

insert into counters set
            column_name = 'paddle_number',
            school_year = '2004-2005',
            counter = 1;

---- table perms backup
INSERT INTO `table_permissions` 
(table_permissions_id , table_name , field_name , group_id , realm_id , 
user_level , group_level)
VALUES 
(1,'enrollment','family_id',NULL,14,200,200),
(2,'enrollment','kid_id',NULL,14,200,200),
(3,'enrollment','am_pm_session',NULL,14,200,600),
(4,'enrollment','start_date',NULL,14,0,600),
(5,'enrollment','dropout_date',NULL,14,0,700),
(6,'enhancement_projects',NULL,NULL,3,NULL,500),
(7,'enhancement_hours','family_id',NULL,3,200,200),
(8,'enhancement_hours','parent_id',NULL,3,200,600),
(9,'enhancement_hours',NULL,NULL,3,NULL,300),
(10,'enhancement_hours','family_id',NULL,3,0,600),
(11,'leads','family_id',NULL,6,0,600),
(12,'leads','source_id',NULL,6,0,500),
(13,'leads','lead_id',NULL,6,0,200),
(14,'auction_donation_items','family_id',NULL,1,0,600),
(15,'auction_donation_items','date_received',NULL,1,200,200),
(16,'income',NULL,NULL,7,NULL,200),
(17,'income','family_id',NULL,7,0,200),
(18,'income','family_id',NULL,9,200,600),
(19,'auction_donation_items',NULL,NULL,11,NULL,600),
(20,'ads',NULL,NULL,12,NULL,200),
(21,'ads','company_id',NULL,12,200,200),
(22,'ads','ad_size_id',NULL,12,200,200),
(23,'ads','artwork_provided',NULL,12,200,200),
(24,'ads','family_id',NULL,12,200,200),
(25,'auction_donation_items','company_id',NULL,15,200,600),
(26,'auction_donation_items','date_received',NULL,15,200,200),
(27,'auction_donation_items','family_id',NULL,15,0,600),
(28,'auction_donation_items','thank_you_id',NULL,15,200,200),
(29,'income',NULL,NULL,16,NULL,200),
(30,'income','company_id',NULL,16,600,600),
(31,'income','family_id',NULL,16,0,600),
(32,'income','thank_you_id',NULL,16,200,200),
(33,'solicitation_calls',NULL,NULL,15,NULL,200),
(34,'solicitation_calls','family_id',NULL,15,0,600),
(35,'ads',NULL,NULL,15,NULL,200),
(36,'ads','artwork_received',NULL,15,200,200),
(37,'ads','family_id',NULL,15,0,600),
(38,'in_kind_donations','company_id',NULL,15,600,600),
(39,'in_kind_donations','family_id',NULL,15,0,600),
(40,'in_kind_donations','thank_you_id',NULL,15,200,200),
(41,'thank_you','family_id',NULL,17,200,600),
(42,'tickets',NULL,NULL,18,NULL,200),
(43,'springfest_attendees',NULL,NULL,18,NULL,200),
(44,'income','family_id',NULL,13,0,600),
(45,'flyer_deliveries','company_id',NULL,4,600,600),
(46,'flyer_deliveries','family_id',NULL,4,0,600),
(47,'nag_indulgences',NULL,NULL,10,NULL,600),
(48,'nag_indulgences','family_id',NULL,10,0,600),
(49,'income',NULL,NULL,9,NULL,500),
(50,'income','account_number',NULL,9,200,200),
(51,'income','family_id',NULL,9,200,200),
(52,'income','company_id',NULL,9,200,200),
(53,'income','raffle_location_id',NULL,9,200,200),
(54,'income','income_id',NULL,9,200,200),
(55,'income',NULL,NULL,9,NULL,300);


-- default group perms
insert into user_privileges set
group_id = 1,
group_level = 100,
user_level = 700,
realm_id = 6; 

insert into user_privileges set
group_id = 1,
group_level = 100,
user_level = 200,
realm_id  = 7; 

insert into user_privileges set
group_id = 1,
group_level = 200,
user_level = 700,
realm_id = 1; 

insert into user_privileges set
group_id = 1,
group_level = 200,
user_level = 200,
realm_id = 2; 

insert into user_privileges set
group_id = 1,
group_level = 0,
user_level = 200,
realm_id = 9; 

insert into user_privileges set
group_id = 1,
group_level = 0,
user_level = 200,
realm_id = 5; 


insert into user_privileges set
group_id = 1,
group_level = 200,
user_level = 500,
realm_id = 14; 

insert into user_privileges set
group_id = 1,
group_level = 200,
user_level = 500,
realm_id = 8; 


insert into user_privileges set
group_id = 1,
group_level = 0,
user_level = 200,
realm_id = 19; 


insert into user_privileges set
group_id = 1,
group_level = 0,
user_level = 200,
realm_id = 3; 


insert into user_privileges set
group_id = 2,
group_level = 200,
user_level = 500,
realm_id = 14; 


insert into user_privileges set
group_id = 2,
group_level = 700,
user_level = 200,
realm_id = 2; 


insert into user_privileges set
group_id = 2,
group_level = 0,
user_level = 200,
realm_id = 9; 


insert into user_privileges set
group_id = 2,
group_level = 100,
user_level = 700,
realm_id = 1; 


insert into user_privileges set
group_id = 2,
group_level = 700,
user_level = 500,
realm_id = 5; 


insert into user_privileges set
group_id = 2,
group_level = 200,
user_level = 200,
realm_id = 8; 


insert into user_privileges set
group_id = 2,
group_level = 200,
user_level = 200,
realm_id = 3; 


insert into user_privileges set
group_id = 2,
group_level = 0,
user_level = 200,
realm_id = 19; 

-- EOF
