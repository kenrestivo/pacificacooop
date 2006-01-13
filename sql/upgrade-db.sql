--

-- add table permisssions: auction stuff to System realm

-- add access levels to system realm?


-- aha oh ho. nag indulgences privs are fuxored
-- HOW? go fix.


-- springfest chairs (and erin, and petra) past tickets

insert into sponsorship_types (sponsorship_name, sponsorship_description, sponsorship_price, school_year) select sponsorship_name, sponsorship_description, sponsorship_price, '2005-2006' from sponsorship_types where school_year = '2004-2005';