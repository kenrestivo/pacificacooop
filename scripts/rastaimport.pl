#!/usr/bin/perl -w


#$Id$
#attempt at gleaning info from the rasta, for matching it up with the db

# Copyright (C) 2003,,2004  ken restivo <ken@restivo.org>
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


#this wants to be an anonymous hash array
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
my $validrowmin = 8;


our($opt_t, $opt_v, $schoolyear); #loathe perl
getopts('vt') or &usage();
$schoolyear= "2004-2005";
#&main();
print "this is the old version. it's all changed now\n";
exit 0;

#END GLOBAL AREA
###################################################



#######################
#	USAGE
#######################
sub usage()
{
    print STDERR "usage: $0 [-t -v verbositylevel ] \n";
	#TODO pass in session as a param, and REQUIRE it!
    print STDERR "\t-t debug (don't actually DO anything!)\n";
    print STDERR "\t-v verbose \n";

	exit(1);
} #END USAGE


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


#######################
#	CHECKHEADERS
#######################
sub checkHeaders()
{
	my $rowref = shift;
	my $col;

	#check that my header rows haven't changed on me, and puke if they have
	#TODO in future, programatise the assignment of headers, so they can move

	if($opt_v){
		printf("DEBUG the header cb, is a ref to an %s\n", ref($rowref));
		#&debugStruct($rowref);
	}
} #END CHECKHEADERS


#######################
#	UNBABY
#######################
sub unBaby()
{
	my $annoying = shift;
	#remove the (baby) and (nickname) crap
	$annoying =~ s/(.+?)\s*\(.*\)\s*/$1/;
	#*sigh* clean up data entry screwups
	$annoying =~ s/^\s*(.+?)\s*$/$1/;
	#TODO eliminate doublespaces within! i.e. "  " to " "
	return($annoying);
} #END UNBABY


#######################
#	CHECKNEWFAMILY
#######################
sub checkNewFamily()
{
	#look for a family. add a new one if it's not there, 
	#	and return the insertid
	#search in db. if kid isn't there, 
	#	look for family, it should add one if needed.
	#	then add the kid
	my $rowref = shift;
	my ($rquery , $rqueryobj , $ritemref, $query, $name) ;
	my %ritem;
	my $cnt = 0;
	my $perlsucks = 0;

	if($opt_v){
		print "DEBUG checkNewFamily() called\n";
	}

	$name = $$rowref[0];
	

	#i want EXACT matches on familyname, none of this %% crap
	# XXX auugh! what if i get duplicate family names, i.e. smith, jones?
	$rquery = "select * from families where name like \"$name\" ";

	if($opt_v){
		print "DEBUG doing <$rquery>\n"; #debug only
	}
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;
		if($opt_v){
			printf("DEBUG %d %s %s\n",
				$ritem{'family_id'},
				$ritem{'name'},
				$ritem{'phone'}
			);
		}
		$cnt++;
	}

	if($cnt){
		if($opt_v){
			print "DEBUG: yes, this family is in the db\n";
		}
		return($ritem{'family_id'});
	} 
	
	$query = sprintf("insert into families set 
			name = '%s' ,
			phone = '%s'
	",
		$name, $$rowref[6]
	);
	if($opt_v){
		print "DEBUG doing <$query>\n";
	}
	unless ($opt_t){
		print STDERR $dbh->do($query) . "\n";
		return($dbh->{'mysql_insertid'});
	}
} #END CHECKNEWFAMILY


#######################
#	CHECKONEPARENT
#######################
sub checkOneParent()
{
	my $rowref = shift;
	my $famid = shift;
	my $last_name = shift;
	my $first_name = shift;
	my $type = shift;
	my ($rquery , $rqueryobj , $ritemref, $query);
	my %ritem;
	my $cnt =  0;

	#search in db. if parent isn't there, 
	#	look for family, it should add one if needed.
	#	note: there are a few single moms here, so note that.
	#	note the weird select! i do NOT want to falsely match bad first_name/last_name
	$rquery = sprintf("select * from parents 
			where (first_name like \"%%%s%%\" and last_name like \"%%%s%%\")
			or (first_name like \"%%%s%%\" and family_id = %d)
	",
		$first_name, $last_name, $first_name, $famid
	) ;

	if($opt_v){
		print "DEBUG doing <$rquery>\n"; #debug only
	}
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	#TODO check for duplicates already in there?
	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;
		$cnt++;
	}

	if($cnt){
		#	AND check for parents which don't match the family name in there?
		#		i.e. if my $famid is NOT what's in the db!
		if($ritem{'family_id'} != $famid){
			printf("ERROR! $first_name $last_name has changed from famid %d to famid %d!\n",
				  $famid, $ritem{'family_id'} );
			exit(1);
		}
		if($opt_v){
			print "DEBUG: yes, this parent is in the db\n";
		}
		return($ritem{'parent_id'});
	} 
	#otherwise, add him or her!
	$query = sprintf("insert into parents set 
			family_id = %d ,
			last_name = '%s',
			first_name = '%s',
			type = '%s',
			email_address = '%s',
			worker = '%s'
	",
		$famid, $last_name, $first_name, $type, $$rowref[7],
		#TODO AACK!!! I WILL NEED TO CHECK WORKING PARENT HERE!!
		#	which means, i'll need the fucking CELL. dammit.
		#in the meantime, i have to make a stupid sexist guess
		$type eq 'Mom' ? 'Yes' : 'No'
	);
	if($opt_v){
		print "DEBUG doing <$query>\n";
	}
	unless ($opt_t){
		print STDERR $dbh->do($query) . "\n";
		return($dbh->{'mysql_insertid'});
	}
} #END CHECKONEPARENT



#######################
#	FIXLASTNAMES
#######################
sub fixLastNames()
{
	my $last_name = shift;
	my $first_name = shift;
	my %name;

	$name{'first_name'} = $first_name;
	$name{'last_name'} = $last_name;

	#handle leigh ann and jo ann special cases. 
	if($first_name =~ /\w+\s+\w+/ && $first_name !~ /\s+[Aa]nn/){
		($name{'first_name'}, $name{'last_name'}) = split(/ +/, $first_name);
	}
	
	return \%name;
} #END FIXLASTNAMES


#######################
#	CHECKNEWPARENTS
#######################
sub checkNewParents()
{
	my $rowref = shift;
	my $session = shift;
	my ($famname, $something, $nameref, $famid) ;
	my %ritem;
	my $cnt =  0;

	if($opt_v){
		print "DEBUG checkNewParents($session) called\n";
	}

	$famid = &checkNewFamily($rowref);
	if($famid < 1){
		print "ERROR! family_id $famid\n";
		exit(1);
	}

	#*sigh* the annoyance of excel. why not create a "baby" checkbox. grr.
	$famname = $$rowref[0];

	#check the mom's name
	$nameref = &fixLastNames($famname, $$rowref[1]);
	$something = &checkOneParent($rowref, $famid,  
		$$nameref{'last_name'}, $$nameref{'first_name'}, 'Mom');


	#i have to namecheck the mom AND the dad's name. 
	#	seriously, it's the 21st centry
	#	TODO somehow guess dad or partner? um, how?
	$nameref = &fixLastNames($famname, $$rowref[2]);
	$something = &checkOneParent($rowref, $famid,  
		$$nameref{'last_name'}, $$nameref{'first_name'}, 'Dad');

	#TODO *do* something with $something! return it, check it, SOMETHING!
	
} #END CHECKNEWPARENTS

#######################
#	CHECKNEWKIDS
#######################
sub checkNewKids()
{
	my $rowref = shift;
	my $session = shift;
	my ($rquery , $rqueryobj , $ritemref, $query);
	my ($kid_id, $name) ;
	my %ritem;
	my $cnt =  0;
	my $famid =  0;

	if($opt_v){
		print "DEBUG checkNewKids($session) called\n";
	}

	#search in db. if kid isn't there, 
	#	look for family, it should add one if needed.
	#	then add the kid
	$name = $$rowref[0];

	##XXX must check for am/pm session! kid could be in wrong session!
	# in which case you'll want to un-drop the kid.
	$rquery = sprintf("select * from kids 
			where first_name like \"%%%s%%\" and last_name like \"%%%s%%\"
	",
		$$rowref[3], $name
	);

	if($opt_v){
		print "DEBUG doing <$rquery>\n"; #debug only
	}
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	#TODO check for duplicates already in there?
	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;
		$cnt++;
	}

	if($cnt){
		if($opt_v){
			print "DEBUG: yes, this kid is in the db\n";
		}
		#TODO check its attendance! and add/change its attendance here!
		#	i.e. move it from AM to PM
		return $ritem{'kid_id'};
	} 
	
	#otherwise, insert the new kid!
	#	first_name, get or insert its family
	$famid = &checkNewFamily($rowref);
	if($famid < 1){
		print "ERROR! family_id $famid\n";
		exit(1);
	}

	#OK, add the little munchkin!
	$query = sprintf("insert into kids set 
			last_name = '%s' ,
			first_name = '%s' ,
			family_id = %d 
	",
		$name, $$rowref[3], $famid
	);
	if($opt_v){
		print "DEBUG doing <$query>\n";
	}
	unless ($opt_t){
		print STDERR $dbh->do($query) . "\n";
		$kid_id = $dbh->{'mysql_insertid'};
	}
	
	#add them to ATTENDANCE too! 
	#	i am assuming that, since they are NEW kids, 
	#	there aren't any entries for them in the attendance base yet!
	#if($kid_id < 1){
	#	print "ERROR! kid_id $kid_id\n";
	#	exit(1);
	#}
	$query = sprintf("insert into attendance set 
			kid_id = %d ,
			enrolid = '%s'
	",
		#XXX i have horribly hacked this to HARD CODE for 2003-2004 session!
		#	this MUST be fixed before the end of the school year!
		#		i.e. "1" and "2" are SPECIFIC to this calendar year. DON'T DO IT!
		$kid_id, $session eq 'AM' ? 1 : 2
	);
	if($opt_v){
		print "DEBUG doing <$query>\n";
	}
	unless ($opt_t){
		print STDERR $dbh->do($query) . "\n";
		return $dbh->{'mysql_insertid'};
	}

	#add parent here too? why not, we know we need them/one
	# &checkNewParents($rowref, $session);	
  
} #END CHECKNEWKIDS


#######################
#	CHECKCHANGES
#######################
sub checkChanges(){
	#search in db. compare all relevant fields
	#issue updates if needed
	#the DANGER is that there is no uniqueid to compare against... trouble.
	#ASSUMPTION: newkids has already been called, as have deletes!
	my $rowref = shift;
	my $session = shift;
	my ($rquery , $rqueryobj , $ritemref, $query) ;
	my %ritem;
	my $cnt = 0;

	$rquery = sprintf("
		select kids.first_name, kids.last_name, kids.kid_id, kids.family_id, 
				enrol.school_year, enrol.sess, attendance.dropout
			from attendance
				left join enrol on attendance.enrolid = enrol.enrolid
				left join kids on kids.kid_id = attendance.kid_id
			where enrol.school_year = '%s'  
				and last_name like \"%%%s%%\" and first_name like \"%%%s%%\"
	",
			$schoolyear, $$rowref[0], $$rowref[3]
	);

	if($opt_v){
		print "DEBUG doing <$rquery>\n"; #debug only
	}
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref; #allocate this, so it persists
		$cnt++;
	}
	if ($cnt > 1){
		printf("ERROR! %s %s in same session with same name twice!\n",
			$ritem{'first_name'}, $ritem{'last_name'}
		);
	} elsif($cnt == 1){
		if($opt_v){
			print "DEBUG: yes, this kid is in the db\n";
		}

		#compare/change:
		#	attendance.enrolid/dropout
		#		ok, we found the kid. compare its current session/dropout status	
		#		and update it (change session, undrop if they're back) if needed
		if( $session eq $ritem{'sess'} || $ritem{'dropout'} ){
			#TODO change the session, and undrop them (they ARE enrolled)
			#	ugh.. deal with school_year (i.e. 2003-2004)
			printf("%s %s was dropped out. un-dropping them\n",
				$ritem{'first_name'}, $ritem{'last_name'}
			);
		}
		#XXX this query is brain-dead. fix it.
		if( $session ne $ritem{'sess'} || !$ritem{'dropout'} ){
			#TODO gurf
			#	ugh.. deal with school_year (i.e. 2003-2004)
			printf ("%s %s wasn't enrolled in this session. adding them\n",
				$ritem{'first_name'}, $ritem{'last_name'}
			);
		}


		#	family.phone
		

		#	parents.email_address, parents.worker

	}


} #END CHECKCHANGES


#######################
#	DELETEREVERSE
#######################
sub deleteReverse()
{
	my $ws = shift;
	my $session = shift;
	my ($rquery , $rqueryobj , $ritemref, $query) ;
	my ($row, $maxrow, $col, $maxcol, $vr, $start, $end, $cnt) ;
	my %ritem;
	#this one goes through the logic BACKWARDSS!
	#it selects * from kids where they haven't dropped out,
	# then iterates through the sheet looking for them

	$rquery = "
		select kids.first_name, kids.last_name, kids.kid_id, enrol.school_year, enrol.sess
			from attendance
			left join enrol on attendance.enrolid = enrol.enrolid
			left join kids on kids.kid_id = attendance.kid_id
			where enrol.school_year = '$schoolyear'  
				and enrol.sess = '$session'
				and attendance.dropout is null
	";

	if($opt_v){
		print "DEBUG doing <$rquery>\n"; #debug only
	}
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref; #allocate this, so it persists

		if($opt_v && $opt_v > 1){
			printf("DEBUG iterating thru, looking for %s %s\n",
					$ritem{'first_name'}, $ritem{'last_name'} );
		}

		#ok, iterate through the sheet looking for this kid.
		$row = $ws->{'MinRow'} ;
		$maxrow = $ws->{'MaxRow'} ;
		$cnt = 0; #none found so far!
		$start = 0;
		$end = 0; 

		while(defined $maxrow && $row <= $maxrow){
			$col = $ws->{'MinCol'} ;
			$maxcol = $ws->{'MaxCol'} ;
			
			#determine if we have a header row
			$vr = &validRow($ws, $row, $col, $maxcol);

			if ($vr > $validrowmin){
				#this is my header row!
				$start++;
				if($start == 1){
					if($opt_v){
						print "DEBUG deleteRev() this is the start row!\n";
					}
				}
			}
			#a blank line means END of data
			$end = $start && !$vr ? 1 : $end;

			#i only want to do stuff if i've already passed the start row
			if($start > 1 && !$end){
				if($opt_v && $opt_v > 1){
					printf("DEBUG\tlookign at %s %s for match\n",
						$ws->{'Cells'}[$row][3]->Value,
						&unBaby($ws->{'Cells'}[$row][0]->Value) 
					);
				}
				#FINALLY! count occurences of this thing!
				if($ritem{'first_name'} eq 
					&unBaby($ws->{'Cells'}[$row][3]->Value) &&
					$ritem{'last_name'} eq 
						&unBaby($ws->{'Cells'}[$row][0]->Value) )
				{
					if($opt_v){
						printf("DEBUG found %s %s !\n",
								$ritem{'first_name'}, $ritem{'last_name'} );
					}
					$cnt++;
				}
			}

			$row++;
		} #end spreadsheet walk
		#ok, what do to if it's not there?
		if($cnt < 1){
			#it's been dropped
			printf("%s %s has been dropped OR moved from $session !\n",
				$ritem{'first_name'}, $ritem{'last_name'} );
			#TODO check 'sess' versus $session and deduce that they moved!
			#	HANDLE THIS RIGHT! do i drop them here and then add them later?
			$query = sprintf("update attendance set 
					dropout = now() 
					where kid_id = %d
			",
				#XXX i have horribly hacked this to 
				#	HARD CODE for 2003-2004 session!
				#	this MUST be fixed before the end of the school year!
				$ritem{'kid_id'}, $session eq 'AM' ? 1 : 2
			);
			if($opt_v){
				print "DEBUG doing <$query>\n";
			}
			unless ($opt_t){
				print STDERR $dbh->do($query) . "\n";
				return $dbh->{'mysql_insertid'};
			}


		} elsif ($cnt > 1){
			#error! we have TWO matches??!
			printf("ERROR %s %s is in the roster twice??!\n",
				$ritem{'first_name'}, $ritem{'last_name'} );
			exit(1);
		}
	} #end ritem walk
	
} #END DELETEREVERSE


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
		print "checking headers on $session...\n";
		&iterateRows($ws, $session, \&checkHeaders, 'header');
		print "dropping the deleted ones in $session...\n";
		&deleteReverse($ws, $session); #go backwards and see waz up
		print "checking new kids in $session...\n";
		&iterateRows($ws, $session, \&checkNewKids, 'row');
		print "checking new parents $session...\n";
		&iterateRows($ws, $session, \&checkNewParents, 'row');
		print "checking for changes in $session...\n";
		&iterateRows($ws, $session, \&checkChanges, 'row');

	} 

} #END ITERATESHEETS


#################
#	VALIDROW
#	count that the first_name x of these have data in them
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
			$row[$i] = &unBaby($cell->Value);
			#	look up nifty perl tricks for this
			if($opt_v){
				printf("extractRow ( $rownum , $i ) => %s\n", $cell->Value) ;
			}
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
	my $cbdata = shift;
	my $start = 0;
	my $end = 0;
	my $vr = 0;
	my($row, $maxrow, $col, $maxcol, $cell);

	$row = $ws->{'MinRow'} ;
	$maxrow = $ws->{'MaxRow'} ;
	if($opt_v){
		printf("--------- iterateRows: %s : from %d to %d rows\n", 
			$ws->{Name}, $row, $maxrow
		);
	}

	while(defined $maxrow && $row <= $maxrow){
		$col = $ws->{'MinCol'} ;
		$maxcol = $ws->{'MaxCol'} ;
		
		#determine if we have a header row
		$vr = &validRow($ws, $row, $col, $maxcol);

		if($opt_v){
			printf("iterateRows $row ------- from %d to %d cols, %d with data\n", 
				$col, $maxcol, $vr);
		}

		if ($vr > $validrowmin){
			#this is my header row!
			$start++;
			if($start == 1){
				if($opt_v){
					print "DEBUG this is the start row!\n";
				}
				if($checkCb && $type eq 'header'){
					&$checkCb(&extractRow($ws, $row, $col, $maxcol), $session);
				}
			}
		}

		$end = $start && $vr < 1 ? 1 : $end;

		if($start > 1 && !$end) {
			#a blank line means END of data

			#i only want to do stuff if i've already passed the start row
			if($checkCb && $type eq 'row'){
				&$checkCb(&extractRow($ws, $row, $col, $maxcol), $session);
			} elsif ($checkCb && $type eq 'rev' && $cbdata){
				&$checkCb(&extractRow($ws, $row, $col, $maxcol), 
					$session, $cbdata);
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

	$wb = $xls->Parse('../imports/AM.xls');
	&iterateSheets($wb, 'AM');

	$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

} #END MAIN


#EOF
