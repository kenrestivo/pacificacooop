-- fix it
drop table access_levels;

-- use the new definition for accesslevels (definition.sql)
-- seed the new accesslevels (seed.sql)

--- do it

-- table perms: fees income change from solicitation to fees
-- table perms: add family fees
-- user perms: fees income change from solicitation to fees

-- restivo family gets admin perms  (springfest gets them removed?)

-- add table permisssions: user instructions and auction stuff to System realm

-- UNNECESSARY! my new trick works! update table_permissions set user_level = null, group_level = null, menu_level = null, year_level = null where table_name = 'families_income_join';




