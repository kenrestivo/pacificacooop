#!/usr/bin/perl -w


#$Id$
#deciphers hypenated last names

use DBI;

#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";


#approximate list of families
$rquery = "select * from parents where first like \"% %\"";
#print "doing <$rquery>\n"; #XXX debug only
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($ritemref = $rqueryobj->fetchrow_hashref){
	%ritem = %$ritemref;
	$oldlast = $ritem{'last'};
	$first = $ritem{'first'};
	$id = $ritem{'parentsid'};

	($newfirst, $newlast) = split(/ +/, $first);
	print "shall i change <$oldlast, $first> into <$newlast, $newfirst>?\n";
	$reply = <STDIN>;

	if($reply !~ /[nN]/){
		#ok, fix em!
		$query = "update parents set last = \'$newlast\', first = \'$newfirst\' where parentsid = $id";
		print "doing <$query>\n";
		print STDERR $dbh->do($query) . "\n";
	}
} # end while


$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

#EOF
