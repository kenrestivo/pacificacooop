--

-- add table permisssions: auction stuff to System realm

-- add access levels to system realm?

-- -1 for job ass/descr: except limit to VIEW on previous years assignment

--  everyone should see all previosu years jobs, board members delete all prev

-- aha oh ho. nag indulgences privs are fuxored



create table minutes(
minutes_id int(32) primary key not null unique auto_increment,
calendar_event_id int(32),
body longtext
);
