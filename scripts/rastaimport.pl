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
my $oExcel = new Spreadsheet::ParseExcel;

#1.1 Normal Excel97
my $oBook = $oExcel->Parse('Excel/Test97.xls');
my($iR, $iC, $oWkS, $oWkC);
print "FILE  :", $oBook->{File} , "\n";
print "COUNT :", $oBook->{SheetCount} , "\n";
print "AUTHOR:", $oBook->{Author} , "\n";
for(my $iSheet=0; $iSheet < $oBook->{SheetCount} ; $iSheet++) {
																					$oWkS = $oBook->{Worksheet}[$iSheet];
	print "--------- SHEET:", $oWkS->{Name}, "\n";
	for(my $iR = $oWkS->{MinRow} ;
	defined $oWkS->{MaxRow} && $iR <= $oWkS->{MaxRow} ; $iR++) {
		for(my $iC = $oWkS->{MinCol} ;
			defined $oWkS->{MaxCol} && $iC <= $oWkS->{MaxCol} 
			; $iC++) 
		{
			$oWkC = $oWkS->{Cells}[$iR][$iC];
			print "( $iR , $iC ) =>", $oWkC->Value, "\n" if($oWkC);
		}
	}
}


#EOF
