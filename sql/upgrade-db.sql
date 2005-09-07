-- HEREIT IS!
--- the thing i need to do in order to push the db live!

alter table groups drop column audit_user_id;

--run the deleteoldperms script (deleteoldperms.sql)

-- make sure there aren't any ugly things in there! 
--i.e. w/o any user/group/realm

-- 	add workers table, (definition.sql)

-- 	then run the populate workers query (from queries.sql)

-- add job_assignments table (definition)

alter table parents drop column worker;	

-- 	add realms table (from definition.sql)

-- add users_groups_join table (definition)

insert into users_groups_join (group_id, user_id) 
select 1, user_id from users where family_id > 0

insert into users_groups_join (group_id, user_id) 
select 2, user_id from users where family_id < 1


-- 	add the realms (seed.sql)

alter table events add column   realm_id int(32) default NULL;
alter table user_privileges add column   realm_id int(32) default NULL;
alter table user_privileges add column   year_level  int(32) default NULL;
alter table user_privileges add column   menu_level  int(32) default NULL;

-- add table permissions table (definition)

-- 	new table_permissions use hardcopy stored in seed.sql

update user_privileges,realms set user_privileges.realm_id = realms.realm_id where user_privileges.realm = realms.realm;
update events,realms set events.realm_id = realms.realm_id where events.realm = realms.realm;

-- 	then seed the new USER perms! (at the end of seed.sql)


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


--add the events for start of fall, etc, and the calendar_events too (seed.sql)


alter table calendar_events add column show_on_public_page enum('Unknown','Yes','No') default 'No';


-- add events and calendar events stuff from seeds

alter table blog_entry change column show_on_public_page  show_on_public_page enum('Unknown','Yes','No') default 'No';
alter table blog_entry change column   show_on_members_page show_on_members_page enum('Unknown','Yes','No') default 'Yes';



insert into user_privileges
(user_id, group_id, realm_id, user_level, group_level, menu_level, year_level)
values
(4, NULL, 14, 800, 800, 800, 800);
update user_privileges set user_level = 700 where user_id = 4 and realm_id = 3;

--create a ROOT user!
insert into users set name = 'System Admin';
select user_id from  users where name = 'System Admin';
-- insert into user_privileges (user_id, group_id, realm_id, user_level, group_level, menu_level, year_level) select YOUPICKIT, NULL, realm_id, 800, 800, 800, 800 from realms;


-- finally, import the new rasta, if that hasn't already been done.

---CONGRATULATIONS! you're done making massive changes to the database.
