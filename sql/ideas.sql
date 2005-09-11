--	$Id$	

-- things that will go into definition or seed perhaps later

create table subscriptions(
subscription_id int(32) primary key not null unique auto_increment,
user_id int(32),
news tinyint(1),
alerts tinyint(1),
events tinyint(1)
);


-- what to do about parent ed make-up stuff?


create table kudos(
kudos_id int(32) primary key not null unique auto_increment,
parent_id int(32),
event_date date,
notes longtext
);
