<?php

	#  Copyright (C) 2004-2005  ken restivo <ken@restivo.org>
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

//$Id$

chdir('../'); // FOR TEST 

require_once('CoopPage.php');
require_once('COOP/NewDispatcher.php');
require_once "HTML/Template/PHPTAL.php";
require_once "lib/phptal_filters.php";

$cp = new coopPage( $debug);


$template = new PHPTAL("wholepagetest.xhtml");


$template->setAll ($cp->context);

// execute template
$template->addOutputFilter(new XML_to_HTML());
print  $template->execute();

//$cp->printDebug($cp->title. $cp->heading);

$cp->finalDebug();

?>