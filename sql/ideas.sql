--	$Id$	




-- what to do about parent ed make-up stuff?


create table kudos(
kudos_id int(32) primary key not null unique auto_increment,
parent_id int(32),
event_date date,
notes longtext
);

