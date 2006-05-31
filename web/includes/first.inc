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

define('DB_DATAOBJECT_NO_OVERLOAD', true);



// NOTE! i want the ISP's version of PEAR to override my local beta crap

require_once('settings/dbhost.inc');
require_once('settings/globals.inc');


/// THIS IS THE KEY TO THE WHOLE APPLICATION!
/// if this one function doesn't happen, ain't nothing else happening!
set_include_path(implode(PATH_SEPARATOR, 
                         array(dirname(__FILE__), // hmm. instead of ABS_PATH?
                               'local_pear',
                               COOP_LOCAL_PEAR_PATH,
                               get_include_path())));


require_once("includes/session-init.php");

setupDB();
setupErrors(); //*sigh* have to do this SECOND since it looks at dbhost :-/

sessionSetup();

user_error(sprintf("first.inc: session <%s> auth <%s> set-up",
                   session_id(), $_SESSION['auth']['state']), E_USER_NOTICE);	




// end of main code


?>
