--  $Id$
--  database schema for co-op database

-- Copyright (C) 2003,2004  ken restivo <ken@restivo.org>
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


CREATE DATABASE /*!32312 IF NOT EXISTS*/ coop;

USE coop;

--
-- Table structure for table `auction_donation_items`
--

CREATE TABLE auction_donation_items (
  auction_donation_item_id int(32) NOT NULL unique auto_increment,
  item_description longtext,
  quantity int(5) default NULL,
  item_value decimal(9,2) default NULL,
  date_received date default NULL,
  location_in_garage varchar(255) default NULL,
  item_type enum('Unknown','Actual Item','Gift Certificate') default NULL,
  package_id int(32) default NULL,
  PRIMARY KEY  (auction_donation_item_id),
) ;

--
-- Table structure for table `auction_items_families_join`
--

CREATE TABLE auction_items_families_join (
  auction_items_families_join_id int(32) NOT NULL unique auto_increment,
  auction_donation_item_id int(32) default NULL,
  family_id int(32) default NULL,
  PRIMARY KEY  (auction_items_families_join_id),
) ;

--
-- Table structure for table `audit_trail`
--

CREATE TABLE audit_trail (
  audit_trail_id int(32) NOT NULL unique auto_increment,
  table_name varchar(255) default NULL,
  index_id int(32) default NULL,
  audit_user_id int(32) default NULL,
  updated timestamp(14) NOT NULL,
  PRIMARY KEY  (audit_trail_id),
) ;

--
-- Table structure for table `blog_entry`
--

CREATE TABLE blog_entry (
  blog_entry_id int(32) NOT NULL unique auto_increment,
  parent_id int(32) default NULL,
  short_title varchar(255) default NULL,
  body longtext,
  show_on_members_page enum('Unknown','Yes','No') default NULL,
  show_on_public_page enum('Unknown','Yes','No') default NULL,
  PRIMARY KEY  (blog_entry_id),
) ;

--
-- Table structure for table `calendar_events`
--

CREATE TABLE calendar_events (
  calendar_event_id int(32) NOT NULL unique auto_increment,
  event_id int(32) default NULL,
  status enum('Active','Tentative','Cancelled') default 'Active',
  keep_event_hidden_until_date datetime default NULL,
  event_date datetime default NULL,
  PRIMARY KEY  (calendar_event_id),
) ;

--
-- Table structure for table `chart_of_accounts`
--

CREATE TABLE chart_of_accounts (
  acctnum int(32) NOT NULL unique default '0',
  description varchar(255) default NULL,
  account_type enum('Income','Expense','Equity') default NULL,
  join_to_table varchar(255) default NULL,
  PRIMARY KEY  (acctnum),
) ;

--
-- Table structure for table `companies`
--

CREATE TABLE companies (
  company_id int(32) NOT NULL unique auto_increment,
  company_name varchar(255) default NULL,
  address1 varchar(255) default NULL,
  address2 varchar(255) default NULL,
  city varchar(255) default NULL,
  state varchar(255) default NULL,
  zip varchar(255) default NULL,
  country varchar(255) default NULL,
  phone varchar(255) default NULL,
  fax varchar(255) default NULL,
  email varchar(255) default NULL,
  territory_id int(32) default NULL,
  familyid int(32) default NULL,
  do_not_contact datetime default NULL,
  flyer_ok enum('Unknown','Yes','No') default NULL,
	PRIMARY KEY (company_id)
) ;

--
-- Table structure for table `companies_auction_join`
--

CREATE TABLE companies_auction_join (
  companies_auction_join_id int(32) NOT NULL unique auto_increment,
  auction_donation_item_id int(32) default NULL,
  company_id int(32) default NULL,
  familyid int(32) default NULL,
  PRIMARY KEY  (companies_auction_join_id),
) ;

--
-- Table structure for table `companies_income_join`
--

CREATE TABLE companies_income_join (
  companies_income_join_id int(32) NOT NULL unique auto_increment,
  income_id int(32) default NULL,
  company_id int(32) default NULL,
  familyid int(32) default NULL,
  PRIMARY KEY  (companies_income_join_id),
) ;

--
-- Table structure for table `company_contacts`
--

CREATE TABLE company_contacts (
  company_contact_id int(32) NOT NULL unique auto_increment,
  first_name varchar(255) default NULL,
  last_name varchar(255) default NULL,
  company_id int(32) default NULL,
  PRIMARY KEY  (company_contact_id),
) ;

--
-- Table structure for table `drivers_licenses`
--

CREATE TABLE drivers_licenses (
  drivers_license_id int(32) NOT NULL unique auto_increment,
  last_name varchar(255) default NULL,
  first_name varchar(255) default NULL,
  middle_name varchar(255) default NULL,
  state varchar(40) default NULL,
  license_number varchar(100) default NULL,
  expiration_date date default NULL,
  parent_id int(32) default NULL,
  PRIMARY KEY  (drivers_license_id),
) ;

--
-- Table structure for table `enhancement_hours`
--

CREATE TABLE enhancement_hours (
  enhancement_hour_id int(32) NOT NULL unique auto_increment,
  parent_id int(32) default NULL,
  enhancement_project_id int(32) NOT NULL default '0',
  work_date date default NULL,
  hours decimal(4,2) default NULL,
  school_year varchar(50) default NULL,
  PRIMARY KEY  (enhancement_hour_id),
) ;

--
-- Table structure for table `enhancement_projects`
--

CREATE TABLE enhancement_projects (
  enhancement_project_id int(32) NOT NULL unique auto_increment,
  project_name varchar(255) default NULL,
  project_description longtext,
  project_complete date default NULL,
  PRIMARY KEY  (enhancement_project_id),
) ;

--
-- Table structure for table `enrollment`
--

CREATE TABLE enrollment (
  enrollment_id int(32) NOT NULL unique auto_increment,
  kid_id int(32) default NULL,
  school_year varchar(50) default NULL,
  am_pm_session enum('AM','PM') default NULL,
  start_date date default NULL,
  dropout_date date default NULL,
  PRIMARY KEY  (enrollment_id),
) ;

--
-- Table structure for table `events`
--

CREATE TABLE events (
  event_id int(32) NOT NULL unique auto_increment,
  description varchar(255) default NULL,
  notes longtext,
  url varchar(255) default NULL,
  PRIMARY KEY  (event_id),
) ;

--
-- Table structure for table `families`
--

CREATE TABLE families (
  family_id int(32) NOT NULL unique auto_increment,
  name varchar(255) default NULL,
  phone varchar(20) default NULL,
  PRIMARY KEY  (family_id),
) ;

--
-- Table structure for table `families_income_join`
--

CREATE TABLE families_income_join (
  families_income_join_id int(32) NOT NULL unique auto_increment,
  income_id int(32) default NULL,
  family_id int(32) default NULL,
  PRIMARY KEY  (families_income_join_id),
) ;

--
-- Table structure for table `flyer_deliveries`
--

CREATE TABLE flyer_deliveries (
  flyer_delivery_id int(32) NOT NULL unique auto_increment,
  flyer_type varchar(255) default NULL,
  delivered_date date default NULL,
  familyid int(32) default NULL,
  company_id int(32) default NULL,
  PRIMARY KEY  (flyer_delivery_id),
) ;

--
-- Table structure for table `groups`
--

CREATE TABLE groups (
  group_id int(32) NOT NULL unique auto_increment,
  name varchar(55) default NULL,
  PRIMARY KEY  (group_id),
) ;

--
-- Table structure for table `income`
--

CREATE TABLE income (
  income_id int(32) NOT NULL unique auto_increment,
  check_number varchar(255) default NULL,
  check_date date default NULL,
  bookkeeper_date date default NULL,
  payer varchar(255) default NULL,
  account_number int(32) default NULL,
  cleared_date date default NULL,
  payment_amount decimal(9,2) default NULL,
  note varchar(255) default NULL,
  PRIMARY KEY  (income_id),
) ;

--
-- Table structure for table `insurance_information`
--

CREATE TABLE insurance_information (
  insurance_information_id int(32) NOT NULL unique auto_increment,
  last_name varchar(255) default NULL,
  first_name varchar(255) default NULL,
  middle_name varchar(255) default NULL,
  policy_number varchar(255) default NULL,
  policy_expiration_date date default NULL,
  companyname varchar(255) default NULL,
  naic int(5) default NULL,
  parent_id int(32) default NULL,
  PRIMARY KEY  (insurance_information_id),
) ;

--
-- Table structure for table `invitation_rsvps`
--

CREATE TABLE invitation_rsvps (
  invitation_rsvps_id int(32) NOT NULL unique auto_increment,
  income_id int(32) default NULL,
  ticket_quantity int(5) default NULL,
  lead_id int(32) default NULL,
  PRIMARY KEY  (invitation_rsvps_id),
) ;

--
-- Table structure for table `kids`
--

CREATE TABLE kids (
  kid_id int(32) NOT NULL unique auto_increment,
  last_name varchar(255) default NULL,
  first_name varchar(255) default NULL,
  family_id int(32) default NULL,
  PRIMARY KEY  (kid_id),
) ;

--
-- Table structure for table `leads`
--

CREATE TABLE leads (
  lead_id int(32) NOT NULL unique auto_increment,
  last_name varchar(255) default NULL,
  first_name varchar(255) default NULL,
  salutation varchar(50) default NULL,
  title varchar(255) default NULL,
  company varchar(255) default NULL,
  address1 varchar(255) default NULL,
  address2 varchar(255) default NULL,
  city varchar(255) default NULL,
  state varchar(255) default NULL,
  zip varchar(255) default NULL,
  country varchar(255) default NULL,
  phone varchar(255) default NULL,
  relation enum('Relative','Friend','Coworker','Alumni','Other') default NULL,
  source_id int(32) default NULL,
  family_id int(32) default NULL,
  do_not_contact date default NULL,
  audit_user_id int(32) default NULL,
  entered datetime default NULL,
  updated timestamp(14) NOT NULL,
  PRIMARY KEY  (lead_id),
) ;

--
-- Table structure for table `nag_indulgences`
--

CREATE TABLE nag_indulgences (
  nag_indulgence_id int(32) NOT NULL unique auto_increment,
  note varchar(255) default NULL,
  granted_date date default NULL,
  indulgence_type enum('Everything','Invitations','Family Auctions','Quilt Fee','Solicitation Auctions') default NULL,
  family_id int(32) default NULL,
  PRIMARY KEY  (nag_indulgence_id),
) ;

--
-- Table structure for table `nags`
--

CREATE TABLE nags (
  nag_id int(32) NOT NULL unique auto_increment,
  which_event enum('Insurance','Springfest','Other') default NULL,
  method_of_contact enum('Email','Phone','CommsFolder','InPerson') default NULL,
  family_id int(32) default NULL,
  user_id int(32) default NULL,
  done datetime default NULL,
  PRIMARY KEY  (nag_id),
) ;

--
-- Table structure for table `packages`
--

CREATE TABLE packages (
  package_id int(32) NOT NULL unique auto_increment,
  package_type enum('Unknown','Live','Silent','Balloon','Ignore','Flat Fee') default NULL,
  package_number varchar(20) default NULL,
  package_title varchar(255) default NULL,
  package_description longtext,
  donated_by_text varchar(255) default NULL,
  starting_bid decimal(9,2) default NULL,
  bid_increment decimal(9,2) default NULL,
  package_value decimal(9,2) default NULL,
  item_type enum('Unknown','Actual Item','Gift Certificate') default NULL,
  PRIMARY KEY  (package_id),
) ;

--
-- Table structure for table `parents`
--

CREATE TABLE parents (
  parent_id int(32) NOT NULL unique auto_increment,
  last_name varchar(255) default NULL,
  first_name varchar(255) default NULL,
  type enum('Mom','Dad','Partner') default NULL,
  worker enum('Yes','No') default NULL,
  family_id int(32) default NULL,
  email_address varchar(255) default NULL,
  PRIMARY KEY  (parent_id),
) ;

--
-- Table structure for table `raffle_income_join`
--

CREATE TABLE raffle_income_join (
  raffle_income_join_id int(32) NOT NULL unique auto_increment,
  raffle_location_id int(32) default NULL,
  familyid int(32) default NULL,
  income_id int(32) default NULL,
  PRIMARY KEY  (raffle_income_join_id),
) ;

--
-- Table structure for table `raffle_locations`
--

CREATE TABLE raffle_locations (
  raffle_location_id int(32) NOT NULL unique auto_increment,
  location_name varchar(255) default NULL,
  start_date date default NULL,
  end_date date default NULL,
  description varchar(255) default NULL,
  PRIMARY KEY  (raffle_location_id),
) ;

--
-- Table structure for table `session_info`
--

CREATE TABLE session_info (
  session_id varchar(32) NOT NULL unique default '',
  ip_addr varchar(20) default NULL,
  entered datetime default NULL,
  updated timestamp(14) NOT NULL,
  user_id int(32) default NULL,
  vars blob,
  PRIMARY KEY  (session_id),
) ;

--
-- Table structure for table `sources`
--

CREATE TABLE sources (
  source_id int(32) NOT NULL unique auto_increment,
  description varchar(255) default NULL,
  PRIMARY KEY  (source_id),
) ;

--
-- Table structure for table `springfest_attendees`
--

CREATE TABLE springfest_attendees (
  springfest_attendee_id int(32) NOT NULL unique auto_increment,
  lead_id int(32) default NULL,
  company_id int(32) default NULL,
  PRIMARY KEY  (springfest_attendee_id),
) ;

--
-- Table structure for table `territories`
--

CREATE TABLE territories (
  territory_id int(32) NOT NULL unique auto_increment,
  description varchar(255) default NULL,
  PRIMARY KEY  (territory_id),
) ;

--
-- Table structure for table `territories_families_join`
--

CREATE TABLE territories_families_join (
  territories_families_id int(32) NOT NULL unique auto_increment,
  territory_id int(32) default NULL,
  family_id int(32) default NULL,
  semester varchar(50) default NULL,
  PRIMARY KEY  (territories_families_id),
) ;

--
-- Table structure for table `user_privileges`
--

CREATE TABLE user_privileges (
  privilege_id int(32) NOT NULL unique auto_increment,
  user_id int(32) default NULL,
  group_id int(32) default NULL,
  realm varchar(55) default NULL,
  user_level int(5) default NULL,
  group_level int(5) default NULL,
  PRIMARY KEY  (privilege_id),
) ;

--
-- Table structure for table `users`
--

CREATE TABLE users (
  user_id int(32) NOT NULL unique auto_increment,
  password varchar(255) default NULL,
  name varchar(255) default NULL,
  family_id int(32) default NULL,
  PRIMARY KEY  (user_id),
) ;

--
-- Table structure for table `users_groups_join`
--

CREATE TABLE users_groups_join (
  users_groups_join_id int(32) NOT NULL unique auto_increment,
  user_id int(32) default NULL,
  group_id int(32) default NULL,
  PRIMARY KEY  (users_groups_join_id),
 ) ;

--
-- Table structure for table `veh`
--

CREATE TABLE vehicles (
  vid_number varchar(17) NOT NULL default '',
  insurance_information_id int(32) default NULL,
  PRIMARY KEY  (vid_number)
) ;

