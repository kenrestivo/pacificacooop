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
use DBI;
use Getopt::Std;


#my %fieldarray = {
	#Last Name
	#Mom Name*
	#Dad/Partner*
	#Child 
	#Birthday
	#Address
	#Phone
	#Email
	#M
	#Tu
	#W
	#Th
	#F
	#School Job
	#Springfest Job
#};

my $dbh;

getopts('dv') or &usage();

&main();

exit 0;

#END GLOBAL AREA
###################################################



sub usage()
{

	exit(1);
}


#######################
#	DEBUGSTRUCT
#	nifty utility function, 
#	to print the contents of ANY complex perl structure
#	TODO move this to an external utility library
#######################
sub debugStruct()
{
	my $whatsit = shift;
	my $level = shift;
	my $item;

	printf("\n%*s", -($level*4), "");
	if( $whatsit =~ /ARRAY/){
		printf("array of %d elements", scalar @$whatsit);
		foreach $item (@$whatsit){
			&debugStruct($item, $level + 1);
		}
	} 
	elsif( $whatsit =~ /HASH/){
		printf("hash of %d elements", scalar %$whatsit);
		$level++;
		foreach $item (sort(keys %$whatsit)) {
			printf("\n%*s", -($level*4), "");
			printf("key: <%s> ", $item);
			&debugStruct($whatsit->{$item}, $level + 1);
		}

	} 
	elsif( $whatsit =~ /SCALAR/){
		printf("scalar ref <%s>", $$whatsit);
	} else {
		printf("value <%s>", $whatsit);
	}

} #END DEBUGSTRUCT


sub checkHeaders(){
	my $rowref = shift;
	my $col;

	#check that my header rows haven't changed on me, and puke if they have
	#TODO in future, programatise the assignment of headers, so they can move

	printf("DEBUG the header cb, is a ref to an %s\n", ref($rowref));
	#&debugStruct($rowref);
}

sub unBaby()
{
	my $annoying = shift;
	$annoying =~ s/(.+?)\s*\(baby\)\s*/$1/;
	return $annoying;
}

sub checkNewFamily(){
	#look for a family. add a new one if it's not there, 
	#	and return the insertid
	#search in db. if kid isn't there, 
	#	look for family, it should add one if needed.
	#	then add the kid
	my $rowref = shift;
	my ($rquery , $rqueryobj , $ritemref, $query, $name) ;
	my %ritem;
	my $cnt = 0;

	$name = &unBaby($$rowref[0]);
	

	#i want EXACT matches on familyname, none of this %% crap
	$rquery = "select * from families where name like \"$name\" ";

	print "DEBUG doing <$rquery>\n"; #debug only
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		$cnt++;
	}

	if($cnt){
		print "DEBUG: yes, this family is in the db\n";
		return $$ritemref['familyid'];
	} 
	
	$query = sprintf("insert into families set 
			name = '%s' ,
			phone = '%s'
	",
		$name, $$rowref[6]
	);
	print "DEBUG doing <$query>\n";
	#print STDERR $dbh->do($query) . "\n";
	#return $dbh->{'mysql_insertid'};
}


sub checkOneParent(){
	my $rowref = shift;
	my $famid = shift;
	my $last = shift;
	my $first = shift;
	my $ptype = shift;
	my ($rquery , $rqueryobj , $ritemref, $query);
	my %ritem;
	my $cnt =  0;

	#search in db. if parent isn't there, 
	#	look for family, it should add one if needed.
	#	note: there are a few single moms here, so note that.
	$rquery = sprintf("select * from parents 
			where first like \"%%%s%%\" and last like \"%%%s%%\"
	",
		$first, $last
	) ;

	print "DEBUG doing <$rquery>\n"; #debug only
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	#TODO check for duplicates already in there?
	while ($ritemref = $rqueryobj->fetchrow_hashref){
		$cnt++;
	}

	if($cnt){
		#	AND check for parents which don't match the family name in there?
		#		i.e. if my $famid is NOT what's in the db!
		if($$ritemref['familyid'] != $famid){
			print "ERROR! $famid for $first $last has changed!\n";
			exit(1);
		}
		print "DEBUG: yes, this parent is in the db\n";
		return $$ritemref['parentsid'];
	} 
	#otherwise, add him or her!
	$query = sprintf("insert into parents set 
			familyid = %d ,
			last = '%s',
			first = '%s',
			ptype = '%s',
			email = '%s',
			worker = '%s',
	",
		$famid, $last, $first, $ptype, $$rowref[7],
		#TODO AACK!!! I WILL NEED TO CHECK WORKING PARENT HERE!!
		#	which means, i'll need the fucking CELL. dammit.
		#in the meantime, i make a stupid sexist guess
		$ptype eq 'Mom' ? 'Yes' : 'No'
	);
	print "DEBUG doing <$query>\n";
	#print STDERR $dbh->do($query) . "\n";
	#return $dbh->{'mysql_insertid'};
}

sub fixLastNames()
{
	my $last = shift;
	my $first = shift;
	my %name;

	$name{'first'} = $first;
	$name{'last'} = $last;

	#handle leigh ann and jo ann special cases. 
	if($first =~ /\w+\s+\w+/ && $first !~ /\s+[Aa]nn/){
		($name{'first'}, $name{'last'}) = split(/ +/, $first);
	}
	
	return \%name;
}

sub checkNewParents(){
	my $rowref = shift;
	my $famid = shift;
	my ($famname, $something, $nameref) ;
	my %ritem;
	my $cnt =  0;

	#*sigh* the annoyance of excel. why not create a "baby" checkbox. grr.
	$famname = &unBaby($$rowref[0]);

	#check the mom's name
	$nameref = &fixLastNames($famname, $$rowref[1]);
	$something = &checkOneParent($rowref, $famid,  
		$$nameref{'last'}, $$nameref{'first'}, 'Mom');


	#i have to namecheck the mom AND the dad's name. 
	#	seriously, it's the 21st centry
	#	TODO somehow guess dad or partner? um, how?
	$nameref = &fixLastNames($famname, $$rowref[2]);
	$something = &checkOneParent($rowref, $famid,  
		$$nameref{'last'}, $$nameref{'first'}, 'Dad');

	#TODO *do* something with $something! return it, check it, SOMETHING!
	
}

sub checkNewKids(){
	my $rowref = shift;
	my $session = shift;
	my ($rquery , $rqueryobj , $ritemref, $query);
	my ($kidsid, $name, $famid) ;
	my %ritem;
	my $cnt =  0;

	#search in db. if kid isn't there, 
	#	look for family, it should add one if needed.
	#	then add the kid
	$name = &unBaby($$rowref[0]);

	$rquery = sprintf("select * from kids 
			where first like \"%%%s%%\" and last like \"%%%s%%\"
	",
		$$rowref[3], $name
	);

	print "DEBUG doing <$rquery>\n"; #debug only
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	#TODO check for duplicates already in there?
	while ($ritemref = $rqueryobj->fetchrow_hashref){
		$cnt++;
	}

	if($cnt){
		print "DEBUG: yes, this kid is in the db\n";
		return $$ritemref['kidsid'];
	} 
	
	#otherwise, insert the new kid!
	#	first, get or insert its family
	$famid = &checkNewFamily($rowref);	
	if($famid < 1){
		print "ERROR! familyid $famid\n";
		exit(1);
	}

	#OK, add the little munchkin!
	$query = sprintf("insert into kids set 
			last = '%s' ,
			first = '%s' ,
			familyid = %d 
	",
		$name, $$rowref[3], $famid
	);
	print "DEBUG doing <$query>\n";
	#print STDERR $dbh->do($query) . "\n";
	#$kidsid = $dbh->{'mysql_insertid'};
	
	#add them to ATTENDANCE too! 
	#	i am assuming that, since they are NEW kids, 
	#	there aren't any entries for them in the attendance base yet!
	#if($kidsid < 1){
	#	print "ERROR! kidsid $kidsid\n";
	#	exit(1);
	#}
	$query = sprintf("insert into attendance set 
			kidsid = %d ,
			enrolid = '%s'
	",
		#XXX i have horribly hacked this to HARD CODE for 2003-2004 session!
		#	this MUST be fixed before the end of the school year!
		$kidsid, $session eq 'AM' ? 1 : 2
	);
	print "DEBUG doing <$query>\n";
	#print STDERR $dbh->do($query) . "\n";
	#return $dbh->{'mysql_insertid'};

	#add parent here too? why not, we know we need them/one
	&checkNewParents($rowref, $famid);	
	
}

sub checkChanges(){
	#search in db. compare all relevant fields
	#issue updates if needed
	#XXX should i call the newkids, etc here?
}


sub checkDeletes(){
	my $ws = shift;
	my $session = shift;
	my ($rquery , $rqueryobj , $ritemref) ;
	my %ritem;
	#this one goes through the logic BACKWARDSS!
	#it selects * from kids where they haven't dropped out,
	# then iterates through the sheet looking for them
	#if it doesn't find them, it drops them. (confirm first!!?)

	$rquery = "
		select kids.first, kids.last, families.name
			from attendance
			left join enrol on attendance.enrolid = enrol.enrolid
			left join kids on kids.kidsid = attendance.kidsid
			left join families on kids.familyid = families.familyid
	";

	print "DEBUG doing <$rquery>\n"; #debug only
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref; #allocate this, so it persists
		#TODO add callback opaque data for these: a ritemref!
		&iterateRows($ws, $session, \&checkNewKids, 'row', \%ritem);
	}
	
}


#################
#	ITERATESHEETS
#	do all the stuff to a sheet that needs be done
#################
sub iterateSheets()
{
	my $wb = shift;
	my $session = shift;
	my($ws, $sh);

	print "FILE  :", $wb->{File} , "\n";
	print "COUNT :", $wb->{SheetCount} , "\n";
	print "AUTHOR:", $wb->{Author} , "\n";

	for($sh=0; $sh < $wb->{SheetCount} ; $sh++) {
		$ws = $wb->{Worksheet}[$sh];
		
		#i only care about the schedule
		if($ws->{'Name'} !~ /Schedule/){
			next;
		}
		&iterateRows($ws, $session, \&checkHeaders, 'header');
		#&checkDeletes($ws, $session); #TODO add the callback data!
		&iterateRows($ws, $session, \&checkNewKids, 'row');
		&iterateRows($ws, $session, \&checkChanges, 'row');

	} 

} #END ITERATESHEETS


#################
#	VALIDROW
#	count that the first x of these have data in them
#	TODO stupid. don't pass $ws and $row, just pass $ws->{'Cells'}[$row] !
#		can i DO that? array/references?
#################
sub validRow()
{
	my $ws = shift;
	my $row = shift;
	my $col = shift;
	my $check = shift;
	my $cnt = 0;

	while($check--){
		#brutally ugly, but NECESSARY!. must exist, and must have data
		if($ws->{'Cells'}[$row][$col] && 
			$ws->{'Cells'}[$row][$col]->Value)
		{
			$cnt++;
		}
		$col++;
	}

	return $cnt;
} #END VALIDROW


#################
#	EXTRACT ROW
#	turn this annoying object-oriented peice of shit into a proper row!
#################
sub extractRow()
{
	my $ws = shift;
	my $rownum = shift;
	my $col = shift;
	my $check = shift;
	my $i ;
	my @row;
	my $cell;

	for($i = $col; $i < $check ; $i++){
		#brutally ugly, but NECESSARY!. must exist, and must have data
		$cell = $ws->{'Cells'}[$rownum][$i];
		if($cell) {
			$row[$i] = $cell->Value;
			printf("( $rownum , $i ) => %s\n", $cell->Value) ;
		}
	}

	return \@row;
} #END EXTRACTROW


#################
#	ITERATEROWS
#	iterate through a spreadsheet, separating out the good rows
#	and applying callback functions to them
###############
sub iterateRows()
{
	my $ws = shift;
	my $session = shift;
	my $checkCb = shift;
	my $type = shift;
	my $start = 0;
	my $end = 0;
	my $vr = 0;
	my($row, $maxrow, $col, $maxcol, $cell);

	$row = $ws->{'MinRow'} ;
	$maxrow = $ws->{'MaxRow'} ;
	printf("--------- SHEET:%s : from %d to %d rows\n", 
		$ws->{Name}, $row, $maxrow
	);

	while(defined $maxrow && $row <= $maxrow){
		$col = $ws->{'MinCol'} ;
		$maxcol = $ws->{'MaxCol'} ;
		
		#determine if we have a header row
		$vr = &validRow($ws, $row, $col, $maxcol);

		if ($vr == $maxcol){
			#this is my header row!
			#print "DEBUG this is the start row!\n";
			if($checkCb && $type eq 'header'){
				&$checkCb(&extractRow($ws, $row, $col, $maxcol), $session);
			}
			$start++;
		} else {
			#a blank line means END of data
			$end = $start && !$vr ? 1 : $end;

			#i only want to do stuff if i've already passed the start row
			if($start && !$end){
				if($checkCb && $type eq 'row'){
					printf("ROW $row ------- from %d to %d cols\n", 
						$col, $maxcol);
					&$checkCb(&extractRow($ws, $row, $col, $maxcol), $session);
				}
			}
		}
		$row++;
	}
} #END ITERATEROWS



#################
# MAIN
#################
sub main()
{

	$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
		or die "can't connect to database $!\n";

	my $xls = new Spreadsheet::ParseExcel;
	my $wb ;

	$wb = $xls->Parse('../imports/PM.xls');
	&iterateSheets($wb, 'PM');

	#$wb = $xls->Parse('../imports/AM.xls');
	#&iterateSheets($wb, 'AM');

	$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

} #END MAIN


#EOF
