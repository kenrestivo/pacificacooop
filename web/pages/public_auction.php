<?php 

#  Copyright (C) 2004  ken restivo <ken@restivo.org>
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

require_once("first.inc");
require_once("shared.inc");

$sy = findSchoolYear();
$tmp = explode('-', $sy);
$sfyear = $tmp[1];

printf('<html> <head> 
	<title>Pacifica Co-Op Nursery School - Springfest Auction Items</title>
	<link href="main.css" rel="stylesheet" type="text/css">
	</head>

	<body>

	<h2>Springfest Auction Items</h2>

	<p>Here are some fabulous items that will be auctioned off 
			at the %s Springfest!</p>',
	   $sfyear);


print "<table width='100%' border='1' cellspacing='0'>";

print "<tr valign='top' align='center'>
		<td><strong>Item Number</strong></td>
		<td><strong>Item Name</strong></td>
		<td><strong>Description</strong></td>
		<td><strong>Value</strong></td>
	</tr> ";

$q = sprintf('select package_number, package_title, package_description,
        package_value
        from packages
		where (package_type like "Live" 
			or package_type like "Silent")
and school_year = "%s"
        order by package_type, package_number, package_title, 
			package_description', $sy);

$listq = mysql_query($q);
$i = 0;
$err = mysql_error();
if($err){
	user_error("public_auction($title): [$q]: $err", E_USER_ERROR);
}
while($row = mysql_fetch_assoc($listq)){
	$tdrow = array();
	while ( list( $key, $val ) = each($row)) {
		if($key == 'package_value'){
			if($val < 1){
				$tdrow[] = "Priceless";
			} else {
				$tdrow[] = sprintf("$%0.2f",$val);
			}
		} else {
			$tdrow[] = $val;
		}
	}
	tdArray($tdrow);
}
print "</table>
	<p><a href='../index.html'>Home</a></p>
	</body>
	</html>
";

?>

<!-- PUBLIC AUCTION -->
