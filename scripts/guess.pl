#!/usr/bin/perl -w


#$Id$
#fixes last_name names
#this is SO fucking ugly... i can't stand it. things i hate about sql
#one-shot, mostly. now that i have popup's and much of this guessing code in the data entry screen itself, i shouldn't need this one anymore


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

use DBI;

#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";


#init globs
$fcount = 0;

#list of families, in a backwards hash (look up name, get id)
$rquery = "select * from families";
#print "doing <$rquery>\n"; #XXX debug only
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($ritemref = $rqueryobj->fetchrow_hashref){
	%ritem = %$ritemref;
	$fam{$ritem{'name'}} = $ritem{'family_id'};
} # end while

#well, this here does most of the work!
foreach $i ('ins', 'lic'){
	&fixem($i);
}

#all the ones that failed utterly
for $aref ( @nomatch ) {
	print " @$aref \n";
}

#ok, now go through all the ones that needed user input.
#TODO make this a gtk list thing! this rules!
for $aref ( @multis ) {
	print " @$aref \n";
	&choosemulti(@$aref);
}

$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

#END OF MAIN CODE
####################

####################
#	FIXEM
####################
sub fixem {
	my $tab = shift;
	#ok, who needs help? let's fix 'em
	#TODO subroutine this. we want not "ins" but $tab
	$rquery = "select * from $tab where parent_id  = 0 or parent_id is null";
	print "doing <$rquery>\n"; #XXX debug only
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;

	#XXX this multis global thing is bullshit.
		&checkformatches($tab, 
				$ritem{$tab . "id"}, 
				$ritem{'last_name'}, 
				$ritem{'first_name'}, 
				$ritem{'middle_name'});

	} # end while
} #end FIXEM


#####################
# CHECKFORMATCHES
#####################
sub checkformatches {
	my $tab = shift;
	my $lostid = shift;
	my $lostlast_name = shift;
	my $lostfirst_name = shift;
	my $lostmiddle_name = shift;

	print "found id $lostid: $lostlast_name, $lostfirst_name $lostmiddle_name\n"; #DEBUG
	
	#count how many last_name/first_name matches
	my $where = "where last_name like \"\%$lostlast_name\%\" and first_name like \"\%$lostfirst_name\%\" ";
	#NOTE! $where is used often, DO NOT just stuff the string in the checkcount()!
	my $fcount = &checkcount($where);
	if($fcount < 1){
		#if 0; count last_name-only matches
		print "NO match for $lostlast_name, $lostfirst_name $lostmiddle_name\n";
		$where = "where last_name like \"\%$lostlast_name\%\"";
		$fcount = &checkcount($where);
	}

	if($fcount == 1){
		#if 1; just do it
		&useonly($tab, $where, $lostid);
		return;
	}

	if($fcount > 1){
		#if > 1; multichoose
		print "several matches for $where\n";
		# instead of calling this here, build an array of args
		# and push it onto an array. then do these all in batch at end
		# XXX this multis global is bullshit. pass by ref instead
		push(@multis, [ ($tab, $where, $lostid) ]);
		return;
	}

	if($fcount < 1){
		print "NO match for $lostlast_name AT ALL\n";
		#XXX shall i push the data? or justid?
		push(@nomatch, [ ($tab, $lostid, $lostlast_name, $lostfirst_name, $lostmiddle_name) ]); 
	}

} # END CHECKFORMATCHES



#####################
# CHECKCOUNT
#####################
sub checkcount {
	my $where = shift;
	my $fquery = "select count(parent_id) as howmany from parents $where";
	print "doing <$fquery>\n"; #debug
	my $fqueryobj = $dbh->prepare($fquery) or die "can't prepare <$fquery>\n";
	$fqueryobj->execute() or die "couldn't execute $!\n";

	my $fitemref = $fqueryobj->fetchrow_hashref;
	my %fitem = %$fitemref;
	my $fcount = $fitem{'howmany'};
	$fqueryobj->finish();
	print "um, there are $fcount matches\n"; #debug
	return $fcount;
}  #END CHECKCOUNT


#####################
# CHOOSEMULTI
#####################
sub choosemulti {
	my $tab = shift;
	my $where = shift;
	my $lostid = shift;
	my $id;
	my @choices ;
	my $reply = "";
	my $nreply = 0;

	#ok *sigh* let the user pick... pick pick....
	print "type id of the RIGHT replacement, or, return to give up\n";
	my $mquery = "select * from parents $where";
	my $mqueryobj = $dbh->prepare($mquery) 
		or die "can't prepare <$mquery>\n";
	$mqueryobj->execute() or die "couldn't execute $!\n";

	while (my $mitemref = $mqueryobj->fetchrow_hashref){
		my %mitem = %$mitemref;
		push(@choices, $mitem{'parent_id'});
		print "\tid: " , $mitem{'parent_id'}, " " , 
			$mitem{'last_name'}, ", " , $mitem{'first_name'}, "\n";
	}
	$reply = <STDIN>;
	chomp $reply;
	$nreply = $reply =~ /^\d+$/ ? $reply : 0;
	if($nreply > 0){ #XXX check that the reply is valid, it was in @choices!
		if(grep(/$nreply/, @choices)){
			&replace($tab, $nreply, $lostid);
		}
	}
}  #END CHOOSEMULTI


#####################
# USEONLY
#####################
sub useonly {
	my $tab = shift;
	my $where = shift;
	my $lostid = shift;
	my $id;
	#do the query AGAIN, and use it!
	my $mquery = "select parent_id from parents $where";
	my $mqueryobj = $dbh->prepare($mquery) 
		or die "can't prepare <$mquery>\n";
	$mqueryobj->execute() or die "couldn't execute $!\n";

	while (my $mitemref = $mqueryobj->fetchrow_hashref){
		my %mitem = %$mitemref;
		$id = $mitem{'parent_id'};
	}

	&replace($tab, $id, $lostid);
} #END USEONLY


#####################
# REPLACE
#####################
sub replace {
	my $tab = shift;
	my $id = shift;
	my $lostid = shift;
	#ok, now replace the bitch
	my $squery = "update $tab set parent_id = $id where " . $tab . "id = $lostid";
	print "doing <$squery>\n";
	print STDERR $dbh->do($squery) . "\n";
} #END REPLACE

#EOF
