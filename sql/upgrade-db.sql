--- the thing i need to do in order to push the db live!

alter table groups drop column audit_user_id;
--run the deleteoldperms script (deleteoldperms.sql)
-- 	then seed the new USER perms! (at the end of seed.sql)
-- 	add workers table, (definition.sql)
-- 	then run the populate workers query (from queries.sql)
alter table parents drop column worker;	
-- 	add realms table (from definition.sql)
-- 	add the realms (seed.sql)
alter table events add column   realm_id int(32) default NULL;
alter table user_privileges add column   realm_id int(32) default NULL;
alter table table_permissions add column   realm_id int(32) default NULL;

-- 	new table_permissions use hardcopy stored in seed.sql

update user_privileges,realms set user_privileges.realm_id = realms.realm_id where user_privileges.realm = realms.realm;
update events,realms set events.realm_id = realms.realm_id where events.realm = realms.realm;
alter table events drop column realm;
alter table user_privileges drop column realm;

alter table enrollment add column monday tinyint(1);
alter table enrollment add column tuesday tinyint(1);
alter table enrollment add column wednesday tinyint(1);
alter table enrollment add column thursday tinyint(1);
alter table enrollment add column friday tinyint(1);
alter table kids add column  allergies varchar(255) default NULL;
alter table auction_donation_items drop column audit_user_id;
alter table insurance_information drop column audit_user_id;
update groups set name = "Members" where group_id  =1;


-- add new access_level table (definition.sql)
-- seed the access_levels (seed.sql)
-- add or replace the files table (definition.sql)

insert into user_privileges set realm_id=19, user_id  = 52, group_level = 800, user_level = 800;

alter table parents drop column email_address;
add the events for start of fall, etc, and the calendar_events too (seed.sql)
alter table blog_entry change column show_on_public_page  show_on_public_page enum('Unknown','Yes','No') default 'No';
alter table blog_entry change column   show_on_members_page show_on_members_page enum('Unknown','Yes','No') default 'Yes';

-- add events stuff from seeds

insert into calendar_events 
(event_id, school_year, event_date)
values
(10, '2005-2006', '2005-08-19'),
(20, '2005-2006', '2005-08-20'),
(11, '2005-2006', '2005-09-05'),
(2, '2005-2006', '2005-09-13 19:00:00'),
(12, '2005-2006', '2005-09-07 19:00:00'),
(13, '2005-2006', '2005-09-21 19:00:00'),
(2, '2005-2006', '2005-10-04 19:00:00'),
(11, '2005-2006', '2005-10-10'),
(13, '2005-2006', '2005-10-19 19:00:00'),
(14, '2005-2006', '2005-10-22'),
(2, '2005-2006', '2005-11-01 19:00:00'),
(11, '2005-2006', '2005-11-11'),
(13, '2005-2006', '2005-11-16 19:00:00'),
(16, '2005-2006', '2005-11-25'),
(17, '2005-2006', '2005-11-28'),
(2, '2005-2006', '2005-12-06 19:00:00'),
(13, '2005-2006', '2005-12-21 19:00:00'),
(16, '2005-2006', '2005-12-19'),
(17, '2005-2006', '2005-01-03');
--TODO: the rest of 'em


---CONGRATULATIONS! you're done making massive changes to the database.
