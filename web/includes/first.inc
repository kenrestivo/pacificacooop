<?php

/*
	<!-- $Id$ -->
	the vital setup stuff that ALL files MUST have

  Copyright (C) 2003,2004  ken restivo <ken@restivo.org>
 
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
 
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details. 
 
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

//THIS STUFF MUST EXECUTE BEFORE ANY HTML IS SENT!!!! BEFORE HEADERS, ANYTHING!

define('DB_DATAOBJECT_NO_OVERLOAD', true);
// NOTE! i want the ISP's version of PEAR to override my local beta crap
set_include_path(implode(PATH_SEPARATOR, 
                         array('local_pear',
                               COOP_LOCAL_PEAR_PATH,
                               get_include_path())));

require_once('globals.inc');
require_once("session-init.php");

setupDB();
setupErrors(); //*sigh* have to do this SECOND since it looks at dbhost :-/

sessionSetup();

user_error(sprintf("first.inc: session <%s> auth <%s> set-up",
                   session_id(), $_SESSION['auth']['state']), E_USER_NOTICE);	

 // global with meta tags and stylesheets
 $metalinks = '
<meta http-equiv="content-type" 
 				content="text/html; charset=iso-8859-1">
				<link rel="stylesheet" href="main.css" title="main">
<!--[if gte IE 5.5000]>
<script type="text/javascript" src="lib/pngfix.js"></script>
<![endif]-->
';

//$doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
$doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
              "http://www.w3.org/TR/html4/loose.dtd">';
//$doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
// $doctype = '
// <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
// "http://www.w3.org/TR/html4/loose.dtd"> ';
// $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
//        "http://www.w3.org/TR/html4/strict.dtd">';
// $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
//        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
// end of main code


?>
