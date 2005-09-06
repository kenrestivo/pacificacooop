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



INSERT INTO `events` VALUES 
(1,'Christmas Tree Field Trip','Christmas tree farm. All insurance and drivers licenses must be up-to-date.','insurance.php',NULL),
(2,'Parent Education Meeting','Attendance is mandatory for all families',NULL,2),
(3,'Springfest Invitation Names Due','Each family must enter a list of 10 people to be invited to Springfest ','10names.php',10),
(4,'Springfest Auction Donation Items Due','Each family must enter at least 1 auction donation online','auction.php',10),
(5,'Fall Enhancement Cutoff Date','Each family must complete their fall enhancement hours before this date','enhancement.php',3),
(6,'Spring Enhancement Cutoff Date','Each family must complete their spring enhancement hours before this date','enhancement.php',3),
(7,'Springfest','Springfest is our biggest fundraiser of the year.','public_auction.php',10),
(8,'Start of Fall Semester','The first day of the Fall Semester','calendar.php',2),
(9,'Start of Spring Semester','The first day of the Spring Semester','calendar.php',2),
(10,'All paperwork due to 1st V.P.',NULL,NULL,2),
(11,'School Holiday',NULL,NULL,2),
(12,'New parent orientation','All new parents must attend this.',NULL,2),
(13,'Board Meeting','Board meetings are open to membership, and are held at the school.',NULL,2),
(14,'Trike-a-thon','Our Fall Fundraiser',NULL,2),
(15,'Alternate parents day',NULL,NULL,2),
(16,'School out for Recess',NULL,NULL,2),
(17,'School Resumes',NULL,NULL,2),
(18,'KOA Family Campout',NULL,NULL,2),
(19,'Bug School Begins',NULL,NULL,2),
(20,'Work Party','Earn your required enhancement hours by working on the school and playground',NULL,3),
(21,'Tuition Due','Please pay promptly to avoid fees!',NULL,9),
 (22,'Last day of school','The school year ends here. Have a great summer!',NULL,2);



INSERT INTO `calendar_events` VALUES 
(1,4,'Active',NULL,'2003-12-18 00:00:00','2003-2004','No'),
(2,3,'Active',NULL,'2003-11-04 00:00:00','2003-2004','No'),
(3,1,'Active',NULL,'2003-12-12 00:00:00','2003-2004','No'),
(4,5,'Active',NULL,'2005-02-01 00:00:00','2004-2005','No'),
(5,6,'Active',NULL,'2005-06-30 00:00:00','2004-2005','No'),
(6,7,'Active',NULL,'2005-03-19 00:00:00','2004-2005','No'),
(90,5,'Active',NULL,'2006-02-01 00:00:00','2005-2006','No'),
(89,6,'Active',NULL,'2006-06-30 00:00:00','2005-2006','No'),
(88,2,'Active',NULL,'2006-02-07 19:00:00','2005-2006','No'),
(87,13,'Active',NULL,'2006-01-18 19:00:00','2005-2006','No'),
(86,17,'Active',NULL,'2006-01-03 00:00:00','2005-2006','No'),
(85,16,'Active',NULL,'2005-12-19 00:00:00','2005-2006','No'),
(84,13,'Active',NULL,'2005-12-21 19:00:00','2005-2006','No'),
(83,2,'Active',NULL,'2005-12-06 19:00:00','2005-2006','No'),
(82,17,'Active',NULL,'2005-11-28 00:00:00','2005-2006','No'),
(81,16,'Active',NULL,'2005-11-25 00:00:00','2005-2006','No'),
(80,13,'Active',NULL,'2005-11-16 19:00:00','2005-2006','No'),
(79,11,'Active',NULL,'2005-11-11 00:00:00','2005-2006','No'),
(78,2,'Active',NULL,'2005-11-01 19:00:00','2005-2006','No'),
(77,14,'Active',NULL,'2005-10-22 00:00:00','2005-2006','Yes'),
(76,13,'Active',NULL,'2005-10-19 19:00:00','2005-2006','No'),
(75,11,'Active',NULL,'2005-10-10 00:00:00','2005-2006','No'),
(74,2,'Active',NULL,'2005-10-04 19:00:00','2005-2006','No'),
(73,13,'Active',NULL,'2005-09-21 19:00:00','2005-2006','No'),
(72,12,'Active',NULL,'2005-09-07 19:00:00','2005-2006','No'),
(71,2,'Active',NULL,'2005-09-13 19:00:00','2005-2006','No'),
(70,11,'Active',NULL,'2005-09-05 00:00:00','2005-2006','No'),
(69,20,'Active',NULL,'2005-08-20 00:00:00','2005-2006','No'),
(68,10,'Active',NULL,'2005-08-19 00:00:00','2005-2006','No'),
(91,11,'Active',NULL,'2006-02-20 00:00:00','2005-2006','No'),
(92,13,'Active',NULL,'2006-02-22 00:00:00','2005-2006','No'),
(93,13,'Active',NULL,'2006-03-22 19:00:00','2005-2006','No'),
(94,7,'Active',NULL,'2006-03-25 00:00:00','2005-2006','Yes'),
(95,2,'Active',NULL,'2006-04-04 19:00:00','2005-2006','No'),
(96,16,'Active',NULL,'2006-04-10 00:00:00','2005-2006','No'),
(97,17,'Active',NULL,'2006-04-17 00:00:00','2005-2006','No'),
(98,13,'Active',NULL,'2006-04-19 19:00:00','2005-2006','No'),
(99,2,'Active',NULL,'2006-05-02 19:00:00','2005-2006','No'),
(100,13,'Active',NULL,'2006-05-17 19:00:00','2005-2006','No'),
(101,11,'Active',NULL,'2006-05-29 00:00:00','2005-2006','No'),
(102,2,'Active',NULL,'2006-06-06 00:00:00','2005-2006','No'),
(103,13,'Active',NULL,'2006-06-14 19:00:00','2005-2006','No'),
(104,22,'Active',NULL,'2006-06-14 00:00:00','2005-2006','No'),
 (105,18,'Active',NULL,'2006-06-16 00:00:00','2005-2006','No');


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

---- ITEMPORARILY YANKED THESE. BECAUSE, i have coding issues
INSERT INTO `table_permissions` 
(table_permissions_id , table_name , field_name , group_id , realm_id , 
user_level , group_level)
values
(18,'auction_donation_items',NULL,NULL,1,NULL,NULL),
(19,'auction_donation_items','family_id',NULL,1,0,600),
(20,'auction_donation_items','date_received',NULL,1,200,200),
(21,'income',NULL,NULL,7,NULL,200),
(22,'income','family_id',NULL,7,0,200),
(23,'income',NULL,NULL,9,NULL,NULL),
(24,'income','family_id',NULL,9,200,600),
(26,'auction_donation_items',NULL,NULL,11,NULL,600),
(33,'auction_donation_items',NULL,NULL,15,NULL,NULL),
(34,'auction_donation_items','company_id',NULL,15,200,600),
(35,'auction_donation_items','date_received',NULL,15,200,200),
(36,'auction_donation_items','family_id',NULL,15,0,600),
(37,'auction_donation_items','thank_you_id',NULL,15,200,200),
(38,'income',NULL,NULL,16,NULL,200),
(39,'income','company_id',NULL,16,600,600),
(40,'income','family_id',NULL,16,0,600),
(41,'income','thank_you_id',NULL,16,200,200),
(47,'in_kind_donations',NULL,NULL,15,NULL,NULL),
(48,'in_kind_donations','company_id',NULL,15,600,600),
(49,'in_kind_donations','family_id',NULL,15,0,600),
(50,'in_kind_donations','thank_you_id',NULL,15,200,200),
(56,'income',NULL,NULL,13,NULL,NULL),
(57,'income','family_id',NULL,13,0,600),
(65,'income',NULL,NULL,9,NULL,500),
(66,'income','account_number',NULL,9,200,200),
(67,'income','family_id',NULL,9,200,200),
(68,'income','company_id',NULL,9,200,200),
(69,'income','raffle_location_id',NULL,9,200,200),
(70,'income','income_id',NULL,9,200,200),
(71,'income',NULL,NULL,9,NULL,300),



---- table perms backup. i've edited these, the script is wrong.
INSERT INTO `table_permissions` 
(table_permissions_id , table_name , field_name  , realm_id , 
user_level , group_level)
values
(1,'enrollment',NULL,14,200,200),
(9,'enhancement_hours',NULL,3,NULL,NULL),
(10,'enhancement_hours','family_id',3,200,200),
(11,'enhancement_hours','parent_id',3,200,600),
(12,'enhancement_hours',NULL,3,NULL,300),
(13,'enhancement_hours','family_id',3,0,600),
(14,'leads',NULL,6,NULL,NULL),
(15,'leads','family_id',6,0,600),
(16,'leads','source_id',6,0,500),
(17,'leads','lead_id',6,0,200),
(25,'packages',NULL,11,NULL,NULL),
(27,'ads',NULL,12,NULL,200),
(28,'ads','company_id',12,200,200),
(29,'ads','ad_size_id',12,200,200),
(30,'ads','artwork_provided',12,200,200),
(31,'ads','family_id',12,200,200),
(32,'companies',NULL,15,NULL,NULL),
(42,'solicitation_calls',NULL,15,NULL,200),
(43,'solicitation_calls','family_id',15,0,600),
(44,'ads',NULL,15,NULL,200),
(45,'ads','artwork_received',15,200,200),
(46,'ads','family_id',15,0,600),
(51,'thank_you',NULL,17,NULL,NULL),
(52,'thank_you','family_id',17,200,600),
(53,'tickets',NULL,18,NULL,200),
(54,'springfest_attendees',NULL,18,NULL,200),
(55,'raffle_locations',NULL,13,NULL,NULL),
(58,'companies',NULL,4,NULL,NULL),
(59,'flyer_deliveries',NULL,4,NULL,NULL),
(60,'flyer_deliveries','company_id',4,600,600),
(61,'flyer_deliveries','family_id',4,0,600),
(72,'job_assignments',NULL,8,200,200),
(73,'kids',NULL,14,NULL,NULL),
(74,'parents',NULL,14,NULL,NULL),
(75,'workers',NULL,14,NULL,NULL),
(76, 'blog_entry', 'show_on_members_page',  21, 600, 200),
(77,'blog_entry', 'show_on_public_page',  21, 200, 700),
(79,'blog_entry', null,  21, null, null),
(80,'job_descriptions', null,  8, null, null),
(81, 'files', null, 21, null, null),
(82, 'audit_trail', null,  19, null, null),
(83,'families',NULL,14,500,200),
(84,'calendar_events', 'show_on_public_page',  2, 0, 500),
(85,'calendar_events', NULL, 2, NULL, NULL),
(86,'calendar_events', 'keep_event_hidden_until_date', 2, 0, 600)
;

insert into table_permissions set table_name = 'users', realm_id = 19;
insert into table_permissions set table_name = 'groups', realm_id = 19;
insert into table_permissions set table_name = 'user_privileges', realm_id = 19;
insert into table_permissions set table_name = 'realms', realm_id = 19;
--insert into table_permissions set table_name = 'access_levels', realm_id = 19, user_level = 0, group_level = 0;

insert into table_permissions set table_name = 'table_permissions', realm_id = 19;
insert into table_permissions set table_name = 'events', realm_id = 2, group_level = 500, menu_level = 500;


INSERT INTO `table_permissions` 
(table_name , field_name ,  realm_id , 
user_level , group_level, menu_level)
values
('enhancement_projects',NULL,3,NULL,500,500);
('nag_indulgences',NULL,10,NULL,600, 700),
('workers','workday',14,0,200,200),
('nag_indulgences','family_id',10,0,600,700);
 
INSERT INTO table_permissions (table_name ,
field_name , realm_id , user_level , group_level , 
menu_level , year_level ) 
VALUES
('files' , 'disk_filename' , 21 , 200 , 200 , 0 , 0 ),
('files' , 'file_date' , 21 , 200 , 200 , 0 , 0 ),
('files' , 'upload_date' , 21 , 200 , 200 , 0 , 0 ),
('files' , 'file_size' , 21 , 200 , 200 , 0 , 0 ),
('files' , 'mime_type' , 21 , 200 , 200 , 0 , 0 )
;



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

--- NOTE DIFFERENT ORDER!
 INSERT INTO table_permissions (table_name ,
realm_id , user_level , group_level , menu_level )
VALUES 
('auction_purchases' , 11 , 500 , 500 , 500 )


-- reports
INSERT INTO `report_permissions` 
(report_permissions_id, report_name, page, realm_id, user_level, 
group_level, menu_level)
VALUES 
(1,'Summary','enhancement_summary.php',3,0,0,700),
(2,'Summary','carriereport.php',9,0,0,700),
(3,'Summary','nag.php',10,0,0,200),
(4,'Summary','solicit_summary.php',15,0,0,200);


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
