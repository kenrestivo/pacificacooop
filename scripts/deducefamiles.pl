#!/usr/bin/perl -w


#$Id$
# deduces who families are, by grouping the kids names, then deposits the parents in.

use DBI;

#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";


#approximate list of families
$rquery = "select last from kids group by last order by last";
print "doing <$rquery>\n"; #XXX debug only
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($ritemref = $rqueryobj->fetchrow_hashref){
	%ritem = %$ritemref;
	push(@fams, $ritem{'last'});
} # end while

#create (and save) the family id's for them.
foreach $fam (@fams){
	$query = "insert into families set name = \'$fam\'";
	print "doing <$query>\n";
	print STDERR $dbh->do($query) . "\n";
	$id = $dbh->{'mysql_insertid'}; #weird shorthand, not always good

	#ok, do the updating
	foreach $tab ('parents', 'kids'){
		$squery = "update $tab set familyid = $id where last = \'$fam\'";
		print "doing <$squery>\n";
		print STDERR $dbh->do($squery) . "\n";
	}
}

$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

#EOF
