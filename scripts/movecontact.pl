#!/usr/bin/perl -w


#$Id$
#move the email addresses from kids db to parents db
#move the phone from the kids to the family db

use DBI;

#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";

&movethem('email', 'kids', 'parents');
&movethem('phone', 'kids', 'families');

$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

##END OF MAIN CODE
###############

sub movethem {
	$col = shift;
	$from = shift;
	$tab = shift;
	#approximate list of families
	$rquery = "select * from $from where $col is not null";
	#print "doing <$rquery>\n"; #XXX debug only
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		%ritem = %$ritemref;
		$changer = $ritem{$col};
		$id = $ritem{'familyid'};

		#ok, fix em!
		$query = "update $tab set $col = \"$changer\" where familyid = $id";
		print "doing <$query>\n";
		print STDERR $dbh->do($query) . "\n";
	} # end while
}
