<?php

#	<!-- $Id$ -->
#	the vital setup stuff that ALL files MUST have

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


	require_once("errors.inc");
	require_once("session.inc");


	setupErrors();
	sessionSetup();
	
	user_error("first.inc: ok, errors.inc, dbhost.inc, and session.inc have been loaded, tho i can't say in what order exactly", E_USER_NOTICE);	

?>
