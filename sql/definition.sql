--  $Id$
--  database schema for co-op insurance database

drop database if exists coop;
create database coop;
use coop;

-- insurance information
create table coop.ins(
    insid int(32) not null auto_increment,
    last varchar(255),
    first varchar(255),
    middle varchar(255),
    policynum varchar(255),
    vidnum varchar(17),
    expires date,
    companyname varchar(255),
    naic int(5),
    primary key (insid)
);

-- vehicle information (one-to-many to insurance)
create table coop.veh(
    vehid int(32) not null auto_increment,
    insid int(32),
    vidnum varchar(17),
    primary key (vehid)
);

-- license information
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

-- children
create table coop.kids(
    kidsid int(32) not null auto_increment,
    last varchar(255),
    first varchar(255),
    address varchar(255),
    city varchar(255),
    zip varchar(20),
    phone varchar(20),
	email varchar(255),
	sess enum ('AM', 'PM'),
    primary key (kidsid)
);

-- enrollment info (one-to-many to kids)
create table coop.enrol(
    enrolid int(32) not null auto_increment,
	semester varchar(50),
	sess enum ('AM', 'PM'),
	kidsid int(32),
    primary key (enrolid)
);

-- parents who have insurance and/or licenses. (many-to-many to kids)
create table coop.parents(
    parentsid int(32) not null auto_increment,
    parentslast varchar(255),
    parentsfirst varchar(255),
    primary key (parentsid)
);

-- glue table for many-to-many: kids and parents
create table coop.kpglue (
    kpglueid int(32) not null auto_increment,
	kidsid int(32),
	parensid int(32),
    primary key (kpglueid)
);

-- the user/passwords used by the web view page AND my update tool..
grant select, update, insert, delete on coop.* to input@'%' 
	identified by 'test'; 
grant select, update, insert, delete on coop.* to input@localhost 
	identified by 'test';


-- EOF
