<?php
##<!-- $Id: .dbhost.inc,v 1.3 2003/10/12 02:24:50 ken Exp $ -->
#this file is here so that we can run the site on multiple machines
#without having to change the .globals.inc file all the damn time
#this file is intended to be in the .cvsignore so that it doesn't get updated

#  Copyright (C) 2003  ken restivo <ken@restivo.org>
# 
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2 of the License, or
#  (at your option) any later version.
# 
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details. 
# 
#  You should have received a copy of the GNU General Public License
#  along with this program; if not, write to the Free Software
#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

	#change these to match the host that you move the app to.
	#useful for testing


    global $dbuser ;
    global $dbpwd ;
    global $dbhost ;

    $dbuser = "springfest";
    $dbpwd = "92xPi9"; 
    $dbhost =  "pacsql.db";

define(COOP_LOCAL_PEAR_PATH, '/nfsn/content/paccoop/htdocs/members/managed_pear/lib');


define(COOP_ABSOLUTE_FILE_PATH, '/nfsn/content/paccoop/htdocs/members');
define(COOP_ABSOLUTE_URL_PATH, '/members');
define(COOP_ABSOLUTE_URL_PATH_PEAR_DATA, '/members/managed_pear/data');


	#$coop_sendto =  "ken@restivo.org";

##<!-- END DBHOST -->
?>
