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

INSERT INTO `table_permissions` 
( table_name , field_name , group_id , realm_id , 
user_level , group_level) values
('blog_entry', 'show_on_members_page', null, 21, 500, NULL),
('blog_entry', 'show_on_public_page', null, 21, NULL, 700),
('blog_entry', 'family_id', null, 21, NULL, 200),
('blog_entry', null, null, 21, null, null),
('job_descriptions', null, null, 8, null, null),
('files', null, null, 21, null, null);

-- add new access_level table (definition.sql)
-- seed the access_levels (seed.sql)
-- add or replace the files table (definition.sql)

insert into user_privileges set realm_id=19, user_id  = 52, group_level = 800, user_level = 800;
insert into table_permissions set table_name = 'users', realm_id = 19;
insert into table_permissions set table_name = 'groups', realm_id = 19;
insert into table_permissions set table_name = 'users_groups_join', realm_id = 19;
insert into table_permissions set table_name = 'realms', realm_id = 19;
insert into table_permissions set table_name = 'access_levels', realm_id = 19;
insert into table_permissions set table_name = 'user_privileges', realm_id = 19;
insert into table_permissions set table_name = 'table_permissions', realm_id = 19;
insert into table_permissions set table_name = 'events', realm_id = 2, group_level = 500;
insert into table_permissions set table_name = 'calendar_events', realm_id = 2;
insert into table_permissions set table_name = 'calendar_events', 
field_name = 'keep_event_hidden_until_date', realm_id = 2, group_level = 600, user_level  = 0;

alter table parents drop column email_address;
add the events for start of fall, etc, and the calendar_events too (seed.sql)
alter table blog_entry change column show_on_public_page  show_on_public_page enum('Unknown','Yes','No') default 'No';
alter table blog_entry change column   show_on_members_page show_on_members_page enum('Unknown','Yes','No') default 'Yes';

-- add events stuff from seeds

insert into calendar_events 
(event_id, school_year, event_date)
values
(10, '2005-2006', '08-19-2005'),
(20, '2005-2006', '08-20-2005'),
(11, '2005-2006', '09-05-2005'),
(2, '2005-2006', '09-13-2005'),
(13, '2005-2006', '09-21-2005'),
(2, '2005-2006', '10-04-2005'),
(11, '2005-2006', '10-10-2005'),
(13, '2005-2006', '10-19-2005'),
(14, '2005-2006', '10-22-2005'),
(2, '2005-2006', '11-01-2005'),
(11, '2005-2006', '11-11-2005'),
(13, '2005-2006', '11-16-2005'),
(16, '2005-2006', '11-25-2005'),
(17, '2005-2006', '11-28-2005'),
(2, '2005-2006', '12-06-2005'),
(13, '2005-2006', '12-21-2005'),
(16, '2005-2006', '12-19-2005'),
(17, '2005-2006', '01-03-2006');
--TODO: the rest of 'em


-- fix the user_privs for realm_id = 2. i squared them away in local db.

-- GO BACK AND PUT SOME OF THESE IN SEEDS!
-- i.e. the table permissions almost certaily belong in there.

---CONGRATULATIONS! you're done making massive changes to the database.
