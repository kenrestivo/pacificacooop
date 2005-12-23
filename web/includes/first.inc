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


$content_type = 'text/html';
$doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
               "http://www.w3.org/TR/html4/loose.dtd">';

$hdr= apache_request_headers();
if(strstr( $hdr['Accept'], 'application/xhtml+xml')){
    $content_type = 'application/xhtml+xml';
    $doctype ='<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
               "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
}

header('Content-Type: ' . $content_type, true);

 // global with meta tags and stylesheets
 $metalinks = sprintf('
<meta http-equiv="Content-Type" 
 				content="%s;charset=utf-8" %s>
<link rel="stylesheet" href="main.css" title="main" />
', 
                      $content_type,
                      strstr($content_type, 'xml') ? '/' :'');



// end of main code


?>
