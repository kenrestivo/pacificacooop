<?php
#  Copyright (C) 2003  ken restivo <ken@restivo.org>
#<!-- $Id$ -->
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


$paypal_debug = 0;

//move to object/foo.php?
//XXX these value and quantity required need to move to year settings!


/// XXX also separate POLICY from SYSTEM constants.
/// and move the policy ones to the db so peple can edit it!

define('COOP_NAMES_FORFEIT_FEE', 1);
define('COOP_NAMES_DUE_EVENT', 3);
define('COOP_NAMES_QUANTITY_REQUIRED', 5);

define('COOP_AUCTION_FORFEIT_FEE', 3);
define('COOP_AUCTION_DUE_EVENT', 25);
define('COOP_AUCTION_VALUE_REQUIRED', 50);

define('COOP_FOOD_FEE', 2);
define('COOP_FOOD_FEE_DUE_EVENT', 26);
define('COOP_FOOD_VALUE_REQUIRED', 50);
define('COOP_FOOD_FEE_DISCOUNT_EVENT', 27);
define('COOP_FOOD_DISCOUNT_VALUE_REQUIRED', 45);

define('COOP_SPRINGFEST_EVENT_DATE', 7);


define('COOP_DEFAULT_BID_INCREMENT_DIVISOR', 10);
define('COOP_DEFAULT_STARTING_BID_DIVISOR', 2);
define('COOP_DEFAULT_BID_INCREMENT_CLAMP', 10);
define('COOP_DEFAULT_STARTING_BID_CLAMP', 10);

define('COOP_PACKAGE_TYPE_LIVE', 1);
define('COOP_PACKAGE_TYPE_SILENT', 2);

define('COOP_TICKET_TYPE_PAID', 1);
// XXX this should NOT be a global! needs to be different each year!
define('COOP_SPRINGFEST_TICKET_PRICE', 30);


define('COOP_MAX_SELECT_COUNT', 150);
define('COOP_MAX_LONGTEXT_DISPLAY', 60);
define('COOP_MAX_AUDIT_SUMMARY_DISPLAY', 300);

define('COOP_MAX_PAGER', 50);

/// defaults for phptal. relative to the root dir
define('PHPTAL_CACHE_DIR', 'cache/');
define('PHPTAL_REPOSITORY', 'templates');

define('COOP_FOUNDED', 1962);
define('COOP_FIRST_SPRINGFEST', 1972);

define('COOP_PARENT_ED_EVENT', 2);
define('COOP_FALL_CUTOFF_DATE', 5);
define('COOP_SPRING_CUTOFF_DATE', 6);


?>
