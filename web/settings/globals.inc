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

//XXX these value and quantity required need to move to year settings!


define('COOP_MAX_SELECT_COUNT', 150);

?>
