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

-- the user/passwords used by the web view page AND my update tool..
-- these MUST be done manually for db's not named coop!!
-- TODO: i have to grant all to myself on this db! duh.
grant select, update, insert, delete, create on coop.* to input@'%' 
	identified by 'test'; 
grant select, update, insert, delete on coop.* to input@localhost 
	identified by 'test';
grant select, insert, update, delete, create on coop.* to springfest@'%'
    identified by '92xPi9';
grant select, insert, update, delete on coop.* to springfest@localhost
    identified by '92xPi9';

use mysql;
update user set password = old_password('test') where User = 'input';
update user set password = old_password('92xPi9') where User = 'springfest';
flush privileges;

-- EOF
