--  $Id$
--  database schema for co-op insurance database

drop database if exists coop;
create database coop;
use coop;

create table coop.ins(
    insid int(32) not null auto_increment,
    last varchar(255),
    first varchar(255),
    middle varchar(255),
    policynum varchar(255),
    vidnum varchar(17),
    expires date,
    companyname varchar(255),
    naic int(20),
    primary key (insid)
);


create table coop.lic(
    licid int(32) not null auto_increment,
    last varchar(255),
    first varchar(255),
    middle varchar(255),
    state varchar(40),
    licensenum varchar(100),
    expires date,
    primary key (licid)
);


create table coop.kids(
    kidsid int(32) not null auto_increment,
    last varchar(255),
    first varchar(255),
    address varchar(255),
    city varchar(255),
    zip varchar(20),
    phone varchar(20),
	sess enum ('AM', 'PM'),
    primary key (kidsid)
);

create table coop.parents(
    parentsid int(32) not null auto_increment,
    parentslast varchar(255),
    parentsfirst varchar(255),
    kidslast varchar(255),
    kidsfirst varchar(255),
    primary key (parentsid)
);

-- the user/passwords used by the web view page AND my update tool..
grant select, update, insert, delete on coop.* to input@'%' 
	identified by 'test'; 
grant select, update, insert, delete on coop.* to input@localhost 
	identified by 'test';


-- EOF
