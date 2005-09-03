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
INSERT INTO `realms` 
(realm_id, realm, meta_realm_id, short_description)
VALUES 
(1,'auction', 20,'Auctions'),
(2,'calendar',NULL, 'Calendar'),
(3,'enhancement',NULL, 'Enhancement'),
(4,'flyers', 20,'Flyers'),
(5,'insurance',NULL,'Insurance'),
(6,'invitations', 20,'Invitations'),
(7,'invitations_cash', 20,'RSVPs'),
(8,'jobs',NULL,'Jobs'),
(9,'money',NULL,'Fees'),
(10,'nag', 20,'Reminders'),
(11,'packaging', 20,'Packaging'),
(12,'program', 20,'Program'),
(13,'raffle', 20,'Raffles'),
(14,'roster',NULL,'Membership'),
(15,'solicitation', 20,'Solicitation'),
(16,'solicit_money', 20,'Solicitation-redundant'),
(17,'thankyou', 20,'Thank You'),
(18,'tickets', 20,'Tickets'),
(19,'user',NULL,'System'),
(20, 'springfest',NULL,'Springfest'),
(21, 'blog',NULL,'News');



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

insert into events set
            event_id = 8,
            realm_id = 2,
            description = "Start of Fall Semester",
            notes = "The first day of the Fall Semester"
;

insert into events set
            event_id = 9,
            realm_id = 2,
            description = "Start of Spring Semester",
            notes = "The first day of the Spring Semester"
;

insert into events (event_id, description, realm_id)
values
(10, 'All paperwork due to 1st V.P.',2),
(11, 'School Holiday',2),
(12, 'New parent orientation',2),
(13, 'Board Meeting PCNS',2),
(14, 'Trike-a-thon',2),
(15, 'Alternate parents day',2),
(16, 'School out for Recess',2),
(17, 'School Resumes',2),
(18, 'KOA Family Campout',2),
(19, 'Bug School',2);
(20, 'Work Party',3);



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

insert into calendar_events set
            event_id = 8,
            school_year = '2005-2006',
            event_date = "2005-09-12"
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

---- table perms backup. i've edited these, the script is wrong.
INSERT INTO `table_permissions` 
(table_permissions_id , table_name , field_name , group_id , realm_id , 
user_level , group_level)
values
(1,'enrollment',NULL,NULL,14,200,200),
(9,'enhancement_hours',NULL,NULL,3,NULL,NULL),
(10,'enhancement_hours','family_id',NULL,3,200,200),
(11,'enhancement_hours','parent_id',NULL,3,200,600),
(12,'enhancement_hours',NULL,NULL,3,NULL,300),
(13,'enhancement_hours','family_id',NULL,3,0,600),
(14,'leads',NULL,NULL,6,NULL,NULL),
(15,'leads','family_id',NULL,6,0,600),
(16,'leads','source_id',NULL,6,0,500),
(17,'leads','lead_id',NULL,6,0,200),
(18,'auction_donation_items',NULL,NULL,1,NULL,NULL),
(19,'auction_donation_items','family_id',NULL,1,0,600),
(20,'auction_donation_items','date_received',NULL,1,200,200),
(21,'income',NULL,NULL,7,NULL,200),
(22,'income','family_id',NULL,7,0,200),
(23,'income',NULL,NULL,9,NULL,NULL),
(24,'income','family_id',NULL,9,200,600),
(25,'packages',NULL,NULL,11,NULL,NULL),
(26,'auction_donation_items',NULL,NULL,11,NULL,600),
(27,'ads',NULL,NULL,12,NULL,200),
(28,'ads','company_id',NULL,12,200,200),
(29,'ads','ad_size_id',NULL,12,200,200),
(30,'ads','artwork_provided',NULL,12,200,200),
(31,'ads','family_id',NULL,12,200,200),
(32,'companies',NULL,NULL,15,NULL,NULL),
(33,'auction_donation_items',NULL,NULL,15,NULL,NULL),
(34,'auction_donation_items','company_id',NULL,15,200,600),
(35,'auction_donation_items','date_received',NULL,15,200,200),
(36,'auction_donation_items','family_id',NULL,15,0,600),
(37,'auction_donation_items','thank_you_id',NULL,15,200,200),
(38,'income',NULL,NULL,16,NULL,200),
(39,'income','company_id',NULL,16,600,600),
(40,'income','family_id',NULL,16,0,600),
(41,'income','thank_you_id',NULL,16,200,200),
(42,'solicitation_calls',NULL,NULL,15,NULL,200),
(43,'solicitation_calls','family_id',NULL,15,0,600),
(44,'ads',NULL,NULL,15,NULL,200),
(45,'ads','artwork_received',NULL,15,200,200),
(46,'ads','family_id',NULL,15,0,600),
(47,'in_kind_donations',NULL,NULL,15,NULL,NULL),
(48,'in_kind_donations','company_id',NULL,15,600,600),
(49,'in_kind_donations','family_id',NULL,15,0,600),
(50,'in_kind_donations','thank_you_id',NULL,15,200,200),
(51,'thank_you',NULL,NULL,17,NULL,NULL),
(52,'thank_you','family_id',NULL,17,200,600),
(53,'tickets',NULL,NULL,18,NULL,200),
(54,'springfest_attendees',NULL,NULL,18,NULL,200),
(55,'raffle_locations',NULL,NULL,13,NULL,NULL),
(56,'income',NULL,NULL,13,NULL,NULL),
(57,'income','family_id',NULL,13,0,600),
(58,'companies',NULL,NULL,4,NULL,NULL),
(59,'flyer_deliveries',NULL,NULL,4,NULL,NULL),
(60,'flyer_deliveries','company_id',NULL,4,600,600),
(61,'flyer_deliveries','family_id',NULL,4,0,600),
(62,'families',NULL,NULL,10,NULL,NULL),
(65,'income',NULL,NULL,9,NULL,500),
(66,'income','account_number',NULL,9,200,200),
(67,'income','family_id',NULL,9,200,200),
(68,'income','company_id',NULL,9,200,200),
(69,'income','raffle_location_id',NULL,9,200,200),
(70,'income','income_id',NULL,9,200,200),
(71,'income',NULL,NULL,9,NULL,300),
(72,'job_assignments',NULL,NULL,8,NULL,200),
(73,'kids',NULL,NULL,14,NULL,NULL),
(74,'parents',NULL,NULL,14,NULL,NULL),
(75,'workers',NULL,NULL,14,NULL,NULL),
(76, 'blog_entry', 'show_on_members_page', null, 21, 600, 200),
(77,'blog_entry', 'show_on_public_page', null, 21, 200, 700),
(79,'blog_entry', null, null, 21, null, null),
(80,'job_descriptions', null, null, 8, null, null),
(81, 'files', null, null, 21, null, null),
(82, 'audit_trail', null, null, 19, null, null);

insert into table_permissions set table_name = 'users', realm_id = 19;
insert into table_permissions set table_name = 'groups', realm_id = 19;
--insert into table_permissions set table_name = 'realms', realm_id = 19;
--insert into table_permissions set table_name = 'access_levels', realm_id = 19, user_level = 0, group_level = 0;

insert into table_permissions set table_name = 'table_permissions', realm_id = 19;
insert into table_permissions set table_name = 'events', realm_id = 2, group_level = 500, menu_level = 500;

insert into table_permissions set table_name = 'calendar_events', realm_id = 2;
insert into table_permissions set table_name = 'calendar_events', 
field_name = 'keep_event_hidden_until_date', realm_id = 2, group_level = 600, user_level  = 0;

INSERT INTO `table_permissions` 
(table_name , field_name , group_id , realm_id , 
user_level , group_level, menu_level)
values
('enhancement_projects',NULL,NULL,3,NULL,500,500);
('nag_indulgences',NULL,NULL,10,NULL,600, 700),
('workers','workday',NULL,14,0,200,200),
('nag_indulgences','family_id',NULL,10,0,600,700);


-- here are more, auto-generated by schoolyearifyperms.py
insert into table_permissions
(field_name, table_name, realm_id, user_level, group_level)
values
('school_year', 'enrollment', 14, 200, 200),
('school_year', 'enhancement_hours', 3, 200, 200),
('school_year', 'leads', 6, 200, 200),
('school_year', 'auction_donation_items', 1, 200, 200),
('school_year', 'income', 7, 200, 200),
('school_year', 'income', 9, 200, 200),
('school_year', 'auction_donation_items', 11, 200, 200),
('school_year', 'ads', 12, 200, 200),
('school_year', 'auction_donation_items', 15, 200, 200),
('school_year', 'income', 16, 200, 200),
('school_year', 'solicitation_calls', 15, 200, 200),
('school_year', 'ads', 15, 200, 200),
('school_year', 'in_kind_donations', 15, 200, 200),
('school_year', 'tickets', 18, 200, 200),
('school_year', 'springfest_attendees', 18, 200, 200),
('school_year', 'income', 13, 200, 200),
('school_year', 'flyer_deliveries', 4, 200, 200),
('school_year', 'nag_indulgences', 10, 200, 200),
('school_year', 'packages', 11, 200, 200),
('school_year', 'calendar_events', 2, 200, 200),
('school_year', 'files', 21, 200, 200),
('school_year', 'job_assignments', 8, 200, 200),
('school_year', 'workers', 14, 200, 200),
('family_id', 'blog_entry', 21, 200, 200),
('family_id', 'companies', 4, 200, 200),
('family_id', 'companies', 15, 200, 200),
('family_id', 'job_assignments', 8, 200, 200),
('family_id', 'kids', 14, 200, 200),
('family_id', 'parents', 14, 200, 200),
('family_id', 'tickets', 18, 200, 200),
('family_id', 'users', 19, 200, 200);

-- still more
insert into table_permissions
(field_name, table_name, realm_id, user_level, group_level)
values
(NULL, 'report_permissions', 19, 200, 200);



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
group_id = 2,
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
group_level = 200,
user_level = 200,
realm_id = 19; 


insert into user_privileges set
group_id = 2,
group_level = 200,
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
group_level = 200,
user_level = 700,
realm_id = 21; 

----- access levels
insert into access_levels 
(access_level_id, short_name, description, const_name)
values
(-1, NULL, 'Invalid', 'ACCESS_INVALID'),
(0, NULL, 'None', 'ACCESS_NONE'),
(100, 'summary', 'Summarize', 'ACCESS_SUMMARY'),
(200, 'view', 'View', 'ACCESS_VIEW'),
(300, 'viewmult', 'View Multiple (do not use)', 'ACCESS_VIEW_MULT_HACK'),
(500, 'edit', 'Edit', 'ACCESS_EDIT'),
(600, 'add', 'Create', 'ACCESS_ADD'),
(700, 'confirmdelete', 'Delete', 'ACCESS_DELETE'),
(800, NULL, 'Administer permissions for', 'ACCESS_ADMIN');


------ first blog entry
insert into blog_entry set
family_id = 56,
short_title = "Fall Session starts September 12",
body = "There may still be a very few openings for Fall. Call 355-3272 for more information. You may also fill out a wait-list application.",
show_on_public_page  = 'yes';


-- EOF
