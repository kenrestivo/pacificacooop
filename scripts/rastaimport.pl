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
# GNU General Public License for more details. 
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

use strict;
use Spreadsheet::ParseExcel;
my $xls = new Spreadsheet::ParseExcel;

my $wb = $xls->Parse('../imports/PM.xls');

my($row, $maxrow, $col, $maxcol, $ws, $cell, $sh);
print "FILE  :", $wb->{File} , "\n";
print "COUNT :", $wb->{SheetCount} , "\n";
print "AUTHOR:", $wb->{Author} , "\n";


for($sh=0; $sh < $wb->{SheetCount} ; $sh++) {
	$ws = $wb->{Worksheet}[$sh];
	
	#i only care about the schedule
	if($ws->{'Name'} !~ /Schedule/){
		next;
	}

	my $start = 0;
	my $end = 0;
	my $vr = 0;

	$row = $ws->{'MinRow'} ;
	$maxrow = $ws->{'MaxRow'} ;
	printf("--------- SHEET:%s : from %d to %d rows\n", 
		$ws->{Name}, $row, $maxrow
	);

	while(defined $maxrow && $row <= $maxrow){
		$col = $ws->{'MinCol'} ;
		$maxcol = $ws->{'MaxCol'} ;
		
		$vr = &validRow($ws, $row, $col, $maxcol);

		if ($vr == $maxcol){
			#this is my header row!
			#print "DEBUG this is the start row!\n";
			$start++;
		}
		#a blank line means END of data
		$end = $start && !$vr ? 1 : $end;

		#i only want to do stuff if i've already passed the start row
		if($start && !$end){
			printf("ROW $row ------- from %d to %d cols\n", $col, $maxcol);

			while(defined $maxcol && $col <= $maxcol){
				$cell = $ws->{'Cells'}[$row][$col];
				if($cell){
					printf("( $row , $col ) => %s\n", $cell->Value) ;
				}
				$col++;
			}
		}
		$row++;
	}
} #END MAIN

exit 0;
###################################################


#################
#	VALIDROW
#	count that the first x of these have data in them
#################
sub validRow()
{
	my $ws = shift;
	my $row = shift;
	my $col = shift;
	my $check = shift;
	my $cnt = 0;

	while($check--){
		#brutally ugly, but necessary. must exist, and must have data
		if($ws->{'Cells'}[$row][$col] && $ws->{'Cells'}[$row][$col]->Value){
			$cnt++;
		}
		$col++;
	}

	return $cnt;
} #END VALIDROW

#EOF
