<?php

/*
	<!-- $Id$ -->
	the vital setup stuff that ALL files MUST have

  Copyright (C) 2003  ken restivo <ken@restivo.org>
 
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

require_once("PEAR.php");

function parseIniFile($filename)
{
	$config = parse_ini_file($filename,TRUE);
	foreach($config as $class=>$values) {
		$options = &PEAR::getStaticProperty($class,'options');
		$options = $values;
	}
	// to hack around dbobject trashing my settings
	global $_DB_DATAOBJECT_FORMBUILDER;
	$_DB_DATAOBJECT_FORMBUILDER['CONFIG'] = 
		$config['DB_DataObject_FormBuilder'];

}

function hackDBURL()
{
    //NOTE! this function, for whatever reason, is a memory pig.
	// TODO: if i can blow off old mysql, and use only pear, this can go away
	// override database url with my crufty old caraap!
	global $dburl;
	$options = &PEAR::getStaticProperty('DB_DataObject','options');
	$options['database'] = $dburl;

}

parseIniFile('coop-dbobj.ini');
require_once("session-init.php");
setupDB(1);
hackDBURL();

?>