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
    poilicynum varchar(255),
    vidnum varchar(255),
    expires date,
    companyname varchar(255),
    naic varchar(255),
    primary key (insid)
);


create table coop.license(
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


-- EOF
