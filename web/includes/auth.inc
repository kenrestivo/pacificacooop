<!-- $Id$ -->
<?php

#	functions specific to auth

#  Copyright (C) 2003  ken restivo <ken@restivo.org>
#
#  some stuff lifted from
#  POST-NUKE Content Management System
#  Copyright (C) 2001 by the Post-Nuke Development Team.
#  http://www.postnuke.com/
#  ----------------------------------------------------------------------
#  Based on:
#  PHP-NUKE Web Portal System - http://phpnuke.org/
#  Thatware - http://thatware.org/
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


require_once("movetoshared.inc");


#thank you, phpnuke
define('ACCESS_INVALID', -1);
define('ACCESS_NONE', 0);
define('ACCESS_SUMMARY', 100);
define('ACCESS_VIEW', 200);
define('ACCESS_EDIT', 500);
define('ACCESS_ADD', 600);
define('ACCESS_DELETE', 700);
define('ACCESS_ADMIN', 800);

$accessnames = array(  
		  0 => _ACCESS_NONE,
		100 => _ACCESS_SUMMARY,
		200 => _ACCESS_VIEW,
		500 => _ACCESS_EDIT,
		600 => _ACCESS_ADD,
		700 => _ACCESS_DELETE,
		800 => _ACCESS_ADMIN
		);


#######################
#	SAVEPASS
#######################
function savePass($uid, $pass)
{
	$query  = sprintf("update users set pwd = '%s' where uid = %d --",
				$uid, md5($pass));
	mysql_query($query);
	$err = mysql_error();
	if($err){
		#TODO: log this, don't display it
		print "savePass(): [SECRET]: $err\n";
	}

} #END SAVEPASS
					

#######################
#	CHECKPASS
#######################
function checkPass($uid, $pass)
{
	// querying if user submitted the right password
	$query = sprintf("SELECT 1 FROM users WHERE uid='%d' AND pwd='%s';",
				$uid, md5($pass));
	mysql_query($query);
	$err = mysql_error();
	if($err){
		#TODO: log this, don't display it
		print "savePass(): [SECRET]: $err\n";
	}
				
	if (mysql_num_rows($result) > 0) {
		return generateToken();
	}                  

	return 0;
} #END CHECKPASS


#note bin2hex

?>
<!-- END AUTH -->
