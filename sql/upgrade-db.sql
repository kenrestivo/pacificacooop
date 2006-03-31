--

-- add table permisssions: auction stuff to System realm

-- add access levels to system realm?


-- aha oh ho. nag indulgences privs are fuxored
-- HOW? go fix.


-- springfest chairs (and erin, and petra) past tickets


-- add sponsorshiptypes to the perms table, admin only, then test it out please

-- add sources

-- run the remove query? WHAT remove query?



-- ignore for paddles??
-- UPDATE table_permissions SET field_name = NULL ,
-- group_level = -1 WHERE table_permissions.table_permissions_id = 54;



--- POLLS
-- add votes/question/answer tables (definition)
 INSERT INTO realms (short_description , meta_realm_id ) VALUES
('Polls' , 0 );

INSERT INTO user_privileges (user_id , group_id ,
user_level , group_level , realm_id , year_level , menu_level ) VALUES 
( 0 , 3 , 600 , 200 , 25 , 200 , -1 );
INSERT INTO user_privileges (user_id , group_id ,
user_level , group_level , realm_id , year_level , menu_level ) VALUES 
( 0 , 1 , 600 , 0 , 25 , 200 , -1 );



-- thankyou's
-- thankyoutemplate table (definition.sql)
-- seed the templates (seed.sql)



