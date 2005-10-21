
--- do it

update  leads set address1 = null where address1 like '%(%';
alter table leads drop column school_year;
alter table leads drop column family_id;

-- the import 2004-2005 invitations query (queries.sql)

update table_permissions set user_level = null, group_level = null, menu_level = null, year_level = null where table_name = 'invitations';

-- new realm: people
-- perms for people!
-- invitation perms : delete/none/view
-- perms for label_printed:  view/view