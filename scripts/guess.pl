#!/usr/bin/perl -w


#$Id$
#fixes last names

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


#ok, who needs help? let's fix 'em
#TODO subroutine this. we want not "ins" but $tab
$rquery = "select * from ins where parentsid is null";
print "doing <$rquery>\n"; #XXX debug only
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($ritemref = $rqueryobj->fetchrow_hashref){
	%ritem = %$ritemref;

	$lostid = $ritem{'insid'};
	$lostlast = $ritem{'last'};
	$lostfirst = $ritem{'first'};
	$lostmiddle = $ritem{'middle'};

	print "found id $lostid: $lostlast, $lostfirst $lostmiddle\n"; #DEBUG

	$where = "where last like \"\%$lostlast\%\" and first like \"\%$lostfirst\%\" ";
	#ok, any matches?
	$fquery = "select count(parentsid) as howmany from parents $where";
	print "doing <$fquery>\n"; #debug
	$fqueryobj = $dbh->prepare($fquery) or die "can't prepare <$fquery>\n";
	$fqueryobj->execute() or die "couldn't execute $!\n";

	$fitemref = $fqueryobj->fetchrow_hashref;
	%fitem = %$fitemref;
	$fcount = $fitem{'howmany'};
	$fqueryobj->finish();

	print "um, there are $fcount matches\n"; #debug

	if($fcount < 1){
		print "NO match for $lostlast, $lostfirst $lostmiddle\n";
	} elsif ($fcount == 1){
		#do the query AGAIN, and use it!
		$mquery = "select parentsid from parents $where";
		$mqueryobj = $dbh->prepare($mquery) or die "can't prepare <$mquery>\n";
		$mqueryobj->execute() or die "couldn't execute $!\n";

		while ($mitemref = $mqueryobj->fetchrow_hashref){
			%mitem = %$mitemref;
			$id = $mitem{'parentsid'};
		}

		#ok, now replace the bitch
		$squery = "update ins set parentsid = $id where insid = $lostid";
		print "doing <$squery>\n";
		#print STDERR $dbh->do($squery) . "\n";
	} else {
		#ok *sigh* let the user pick... pick pick....
		print "ok, your choices are: ";
		print "type the id of the RIGHT replacement, or, return to give up\n";
		$mquery = "select parentsid as count from parents $where";
		$mqueryobj = $dbh->prepare($mquery) or die "can't prepare <$mquery>\n";
		$mqueryobj->execute() or die "couldn't execute $!\n";

		while ($mitemref = $mqueryobj->fetchrow_hashref){
			%mitem = %$mitemref;
			print " id: " , $mitem{'parentsid'}, " " , 
				$mitem{'last'}, ", " , $mitem{'first'}, " " , 
				$mitem{'middle'} , "\n";
		}
		$reply = <STDIN>;
		if($reply > 0){
			#ok, now replace the bitch
			$squery = "update ins set parentsid = $id where insid = $lostid";
			print "doing <$squery>\n";
			#print STDERR $dbh->do($squery) . "\n";
		}
	}
} # end while

$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

#EOF
