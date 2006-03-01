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


update packages set package_number = cast(package_number as signed);
alter table packages change column package_number package_number int(5) default NULL;
alter table package_types add column prefix varchar(3);

update package_types set prefix = 'L' where package_type_id = 1;
update package_types set prefix = 'S' where package_type_id = 2;
update package_types set prefix = 'D' where package_type_id = 3;
update package_types set prefix = 'B' where package_type_id = 4;
update package_types set prefix = 'F' where package_type_id = 5;
update package_types set prefix = 'U' where package_type_id = 6;

