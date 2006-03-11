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


CREATE TABLE sent_audits (
  sent_audit_id int(32) primary key NOT NULL unique auto_increment,
  user_id int(32) default NULL,
  audit_trail_id int(32) NOT NULL unique auto_increment
) ;
