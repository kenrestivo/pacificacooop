#!/usr/bin/perl -w


#$Id$
#fixes last names
#this is SO fucking ugly... i can't stand it. things i hate about sql

use DBI;

#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";


$fcount = 0;

#list of families, in a backwards hash (look up name, get id)
$rquery = "select * from families";
#print "doing <$rquery>\n"; #XXX debug only
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($ritemref = $rqueryobj->fetchrow_hashref){
	%ritem = %$ritemref;
	$fam{$ritem{'name'}} = $ritem{'familyid'};
} # end while

fixem('ins');
fixem('lic');

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
	$rquery = "select * from $tab where parentsid is null";
	print "doing <$rquery>\n"; #XXX debug only
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;

		&checkformatches($tab, 
				$ritem{$tab . "id"}, 
				$ritem{'last'}, 
				$ritem{'first'}, 
				$ritem{'middle'});

	} # end while
} #end FIXEM


#####################
# CHECKFORMATCHES
#####################
sub checkformatches {
	my $tab = shift;
	my $lostid = shift;
	my $lostlast = shift;
	my $lostfirst = shift;
	my $lostmiddle = shift;

	print "found id $lostid: $lostlast, $lostfirst $lostmiddle\n"; #DEBUG
	
	#count how many last/first matches
	my $where = "where last like \"\%$lostlast\%\" and first like \"\%$lostfirst\%\" ";
	#NOTE! $where is used often, DO NOT just stuff the string in the checkcount()!
	my $fcount = &checkcount($where);
	if($fcount < 1){
		#if 0; count last-only matches
		print "NO match for $lostlast, $lostfirst $lostmiddle\n";
		$where = "where last like \"\%$lostlast\%\"";
		$fcount = &checkcount($where);
	}

	if($fcount == 1){
		#if 1; just do it
		&useonly($tab, $where, $lostid);
		return;
	}

	if($fcount > 1){
		#if > 1; multichoose
		print "multiple choice for $lostlast, $lostfirst $lostmiddle:\n";
		&choosemulti($tab, $where, $lostid);
		return;
	}

	if($fcount < 1){
		print "NO match for $lostlast AT ALL\n";
	}

} # END CHECKFORMATCHES



#####################
# CHECKCOUNT
#####################
sub checkcount {
	my $where = shift;
	my $fquery = "select count(parentsid) as howmany from parents $where";
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
		push(@choices, $mitem{'parentsid'});
		print "\tid: " , $mitem{'parentsid'}, " " , 
			$mitem{'last'}, ", " , $mitem{'first'}, "\n";
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
	my $mquery = "select parentsid from parents $where";
	my $mqueryobj = $dbh->prepare($mquery) 
		or die "can't prepare <$mquery>\n";
	$mqueryobj->execute() or die "couldn't execute $!\n";

	while (my $mitemref = $mqueryobj->fetchrow_hashref){
		my %mitem = %$mitemref;
		$id = $mitem{'parentsid'};
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
	my $squery = "update $tab set parentsid = $id where " . $tab . "id = $lostid";
	print "doing <$squery>\n";
	print STDERR $dbh->do($squery) . "\n";
} #END REPLACE

#EOF
