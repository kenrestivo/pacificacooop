#!/usr/bin/perl -w


#$Id$
#attempt at gleaning info from the rasta, for matching it up with the db

# Copyright (C) 2003  ken restivo <ken@restivo.org>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.                                  #
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

use strict;
use Spreadsheet::ParseExcel;
my $xls = new Spreadsheet::ParseExcel;

#1.1 Normal Excel97
my $bk = $xls->Parse('../imports/PM.xls');

my($row, $col, $wk, $cell);
print "FILE  :", $bk->{File} , "\n";
print "COUNT :", $bk->{SheetCount} , "\n";
print "AUTHOR:", $bk->{Author} , "\n";

for(my $sh=0; $sh < $bk->{SheetCount} ; $sh++) {
																					$wk = $bk->{Worksheet}[$sh];
	print "--------- SHEET:", $wk->{Name}, "\n";

	for(my $row = $wk->{MinRow} ;
		defined $wk->{MaxRow} && $row <= $wk->{MaxRow} ; 
		$row++) 
	{
		print "ROW $row -------\n";
		for(my $col = $wk->{MinCol} ;
			defined $wk->{MaxCol} && $col <= $wk->{MaxCol} 
			; $col++) 
		{
			$cell = $wk->{Cells}[$row][$col];
			print "( $row , $col ) =>", $cell->Value, "\n" if($cell);
		}
	}
}


#EOF
