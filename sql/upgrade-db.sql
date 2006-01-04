--

-- add table permisssions: auction stuff to System realm

-- add access levels to system realm?


-- aha oh ho. nag indulgences privs are fuxored

-- HOW? go fix.






--- XXX ONLY WHEN PUSHING THE MINUTES STUFF LIVE!!!
create table minutes(
minutes_id int(32) primary key not null unique auto_increment,
calendar_event_id int(32),
body longtext
);

-- add to calendar events realm
-- view allyears perms for people too! in calendar realm