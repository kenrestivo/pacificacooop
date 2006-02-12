--

-- add table permisssions: auction stuff to System realm

-- add access levels to system realm?


-- aha oh ho. nag indulgences privs are fuxored
-- HOW? go fix.


-- springfest chairs (and erin, and petra) past tickets


-- add sponsorshiptypes to the perms table, admin only, then test it out please

-- add sources

-- run the remove query? WHAT remove query?


-- add package types (definition)
-- seed package types (seed)
alter table packages add column   package_type_id int(32) default NULL;
-- run the update query (queries)
-- THEN push the code live
-- add table perms (packaging realm)
alter table packages drop column package_type;




-- add table rsvps
-- past year perms for (tickets rsvps paddles)
-- all family view perms for tickets and rsvps, all years too2
-- erin/debbie perms for tickets/rsvps/paddles too

