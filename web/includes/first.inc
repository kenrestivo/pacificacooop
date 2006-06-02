<?php

/*
	<!-- $Id$ -->
	the vital setup stuff that ALL files MUST have

  Copyright (C) 2003-2006  ken restivo <ken@restivo.org>
 
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
// this is the entry point into the application.  welcome, weary traveller!

define('DB_DATAOBJECT_NO_OVERLOAD', true);



// NOTE! i want the ISP's version of PEAR to override my local beta crap

require_once(dirname(__FILE__)  . '/../settings/dbhost.inc');  // database and anything cvs'able
require_once(dirname(__FILE__)  . '/../settings/no_cvs.inc'); /// for live/dev, these are NOT in CVS
require_once(dirname(__FILE__)  . '/../settings/globals.inc'); // other globals, put AFTER above!
require_once(dirname(__FILE__)  . '/session-init.php'); // include functions i need  below:


/// THIS IS THE KEY TO THE WHOLE APPLICATION!
/// if this one function doesn't happen, ain't nothing else happening!
set_include_path(
    implode(
        PATH_SEPARATOR, 
        // XXXX don't do ../, it's stupid.
        // instead, explode the path, and grab all but last, and implode it
        // PHP probably has some path management functions to do this too
        array(dirname(__FILE__)  . '/..', 
              dirname(__FILE__) .  '/../local_pear',
              COOP_LOCAL_PEAR_PATH,
              get_include_path())));




setupDB();
setupErrors(); //*sigh* have to do this SECOND since it looks at dbhost :-/

sessionSetup();

user_error(sprintf("first.inc: session <%s> auth <%s> set-up",
                   session_id(), $_SESSION['auth']['state']), E_USER_NOTICE);	




// end of main code


?>
