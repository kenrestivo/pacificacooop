--

-- add table permisssions: auction stuff to System realm

-- add access levels to system realm?


-- aha oh ho. nag indulgences privs are fuxored
-- HOW? go fix.


-- springfest chairs (and erin, and petra) past tickets


-- add sponsorshiptypes to the perms table, admin only, then test it out please

-- add sources

-- run the remove query? WHAT remove query?

----------------------------------------------------
--------- rsvp/ticket shit
INSERT INTO table_permissions (table_name , field_name
, realm_id , user_level , group_level , menu_level , year_level )
VALUES
('leads_income_join' , NULL, 7 , -1 , -1 , -1 , -1 );
-- rsvp
UPDATE user_privileges SET user_id = 0 , year_level = 200
, menu_level = -1 WHERE user_privileges.privilege_id = 1114;
 UPDATE user_privileges SET year_level = 200 , menu_level
= -1 WHERE user_privileges.privilege_id = 543;
UPDATE user_privileges SET user_id = 57 , group_id = 0 ,
year_level = 200 , menu_level = -1 WHERE user_privileges.privilege_id = 548;
-- tickets
UPDATE user_privileges SET year_level = 200 , menu_level
= -1 WHERE user_privileges.privilege_id = 880;
INSERT INTO user_privileges (user_id , group_id ,
user_level , group_level , realm_id , year_level , menu_level )
VALUES 
( 57 , 0 , 700 , 700 , 18 , 200 , -1 );
-- paddles
INSERT INTO user_privileges (user_id , group_id ,
user_level , group_level , realm_id , year_level , menu_level ) 
VALUES ( 0 , 1 , 200 , 100 , 18 , 200 , 0 );

-- also add ticket perms for susan depriest too


-- ignore for paddles??
-- UPDATE table_permissions SET field_name = NULL ,
-- group_level = -1 WHERE table_permissions.table_permissions_id = 54;



