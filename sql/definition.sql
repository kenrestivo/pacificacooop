--  $Id$
--  database schema for co-op database

-- Copyright (C) 2003  ken restivo <ken@restivo.org>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details. 
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


-- set this to something else if i'm changing it's name!
drop database if exists coop;
create database coop;
use coop;

-- insurance information
create table ins(
    insid int(32) not null unique auto_increment,
    last varchar(255),
    first varchar(255),
    middle varchar(255),
    policynum varchar(255),
    expires date,
    companyname varchar(255),
    naic int(5),
	parentsid int(32),
    primary key (insid)
);

-- TODO: this is future. 
-- insureds information, (many-to-many parents to insurance)
-- create table insureds(
--     insureds int(32) not null auto_increment,
    -- last varchar(255),
    -- first varchar(255),
    -- middle varchar(255),
    -- insid int(32) ,
	-- parentsid int(32),
    -- primary key (insuredsid)
-- );

-- vehicle information (one-to-many to insurance)
create table veh(
    vidnum varchar(17) not null,
    insid int(32),
    primary key (vidnum)
);

-- license information
create table lic(
    licid int(32) not null unique auto_increment,
    last varchar(255),
    first varchar(255),
    middle varchar(255),
    state varchar(40),
    licensenum varchar(100),
    expires date,
	parentsid int(32),
    primary key (licid)
);

-- children
create table kids(
    kidsid int(32) not null unique auto_increment,
    last varchar(255),
    first varchar(255),
	familyid int(32),
    primary key (kidsid)
);

-- enrollment info (many-to-many to kids)
create table enrol(
    enrolid int(32) not null unique auto_increment,
	semester varchar(50),
	sess enum ('AM', 'PM'),
    primary key (enrolid)
);

-- glue table for many-to-many: kids to enrolmment sessions
create table attendance (
    attendanceid int(32) not null unique auto_increment,
	kidsid int(32),
	enrolid int(32),
	dropout date,
    primary key (attendanceid)
);

-- parents who have insurance and/or licenses. (many-to-many to kids)
create table parents(
    parentsid int(32) not null unique auto_increment,
    last varchar(255),
    first varchar(255),
	ptype enum ('Mom', 'Dad', 'Partner'),
	worker enum ('Yes', 'No'),
	familyid int(32),
	email varchar(255),
    primary key (parentsid)
);

-- main table for many-to-many: kids and parents
create table families (
    familyid int(32) not null unique auto_increment,
	name varchar(255),
    phone varchar(20),
    primary key (familyid)
);


-- main table for leads. this will groooowww over time, i'm sure.
-- if there's a familyid, it came through a member, otherwise not.
-- 'source' field will only be "springfest" this time around
-- you'll know WHICH springfest by comparing the 'entered' date
create table leads (
    leadsid int(32) not null unique auto_increment,
	last varchar(255),
	first varchar(255),
	salut varchar(50),
	title varchar(255),
	company varchar(255),
	addr varchar(255),
	addrcont varchar(255),
	city varchar(255),
	state varchar(255),
	zip varchar(255),
	country varchar(255),
	phone varchar(255),
	relation enum ('Relative','Friend', 'Coworker', 'Alumni', 'Other'),
	source enum ('Springfest', 'Other'),
    familyid int(32),
	audit_user_id int(32), -- XXX remove after first mailing!
	entered datetime, -- XXX remove after first mailing!
	updated timestamp, -- XXX remove after first mailing!
	primary key (leadsid)
);
	
-- table to keep record of who, why, how, when i have nagged
create table nags (
    nagsid int(32) not null unique auto_increment,
	why enum ('Insurance', 'Springfest', 'Other'),
	how enum ('Email', 'Phone', 'CommsFolder', 'InPerson'),
    familyid int(32),
    naguid int(32),
	done datetime, -- XXX reduntant! remove!
	primary key (nagsid)
);

	
-- chart of accounts
create table coa (
    acctnum int(32) not null unique,
	description varchar(255),
	acctype enum ('Income', 'Expense', 'Equity'),
	primary key (acctnum)
);

	
-- income tracking
create table inc (
    incid int(32) not null unique auto_increment,
	checknum varchar(255),
	checkdate date,
	payer varchar(255),
    acctnum int(32),
    amount decimal(9,2),
	note varchar(255),
	primary key (incid)
);


-- glue table for many-to-many: families to income
create table figlue (
    figlueid int(32) not null unique auto_increment,
	incid int(32),
	familyid int(32),
    primary key (figlueid)
);

-- glue table for many-to-many: leads to income
--- TODO: need separate one for businesses? or same one?
create table liglue (
    liglueid int(32) not null unique auto_increment,
	incid int(32),
	leadsid int(32),
    primary key (liglueid)
);

-- users
create table users (
    userid int(32) not null unique auto_increment,
	password varchar(255),
	name varchar(255),
	familyid int(32),
    primary key (userid)
);

-- groups
create table groups (
    groupid int(32) not null unique auto_increment,
	name varchar(55),
    primary key (groupid)
);

-- glue table for many to many: users and groups
create table groupmembers (
    memberid int(32) not null unique auto_increment,
    userid int(32),
    groupid int(32),
    primary key (memberid)
);

-- privs
create table privs (
    privid int(32) not null unique auto_increment,
    userid int(32),
    groupid int(32),
	realm varchar(55),
	userlevel int(5),
	grouplevel int(5),
    primary key (privid)
);

-- auction donation item
create table auction (
    auctionid int(32) not null unique auto_increment,
	description longtext,
    amount decimal(9,2),
	date_received date,
	location_in_garage varchar(255),
    primary key (auctionid)
);

-- glue table for many-to-many: families to auction items
create table faglue (
    faglueid int(32) not null unique auto_increment,
	auctionid int(32),
	familyid int(32),
    primary key (faglueid)
);
	
-- events
create table events (
    eventid int(32) not null unique auto_increment,
	description varchar(255),
    notes longtext,
    url varchar(255),
	primary key (eventid)
);
	
-- calendar
create table cal (
    calid int(32) not null unique auto_increment,
	eventid int(32),
	status enum ('Active', 'Tentative', 'Cancelled') default 'Active',
	hideuntil datetime,
	eventdate datetime,
	primary key (calid)
);

-- company table
create table companies (
    company_id int(32) not null unique auto_increment,
	company_name varchar(255),
	address1 varchar(255),
	address2 varchar(255),
	city varchar(255),
	state varchar(255),
	zip varchar(255),
	country varchar(255),
	phone varchar(255),
	fax varchar(255),
	email varchar(255),
	territory_id int(32),
	do_not_contact datetime,
	primary key (company_id)
);

-- glue table for many-to-many: companies to auction items
-- TODO: use NEW table and fieldnames!
create table companies_auction_join (
    saglueid int(32) not null unique auto_increment,
	auctionid int(32),
	company_id int(32),
    primary key (saglueid)
);


-- session info
create table session_info (
	session_id varchar(32) not null unique,
	ip_addr varchar(20),
	entered datetime,
	updated  timestamp,
	user_id int(32),
	vars blob,
	primary key (session_id)
);


-- company detail
create table company_contacts(
    company_contact_id int(32) not null unique auto_increment,
	first_name varchar(255),
	last_name varchar(255),
	company_id int(32),
    primary key (company_contact_id)
);


-- territories
create table territories(
    territory_id int(32) not null unique auto_increment,
	description varchar(255),
    primary key (territory_id)
);

-- audit trail
create table audit_trail(
    audit_trail_id int(32) not null unique auto_increment,
	table_name varchar(255),
	index_id int(32),
    audit_user_id int(32),
	updated timestamp,
    primary key (audit_trail_id)
);


-- the user/passwords used by the web view page AND my update tool..
-- these MUST be done manually for db's not named coop!!
--TODO: i have to grant all to myself on this db! duh.
grant select, update, insert, delete on coop.* to input@'%' 
	identified by 'test'; 
grant select, update, insert, delete on coop.* to input@localhost 
	identified by 'test';
grant select, insert, update, delete on coop.* to springfest@'%'
    identified by '92xPi9';
grant select, insert, update, delete on coop.* to springfest@localhost
    identified by '92xPi9';


-- EOF
