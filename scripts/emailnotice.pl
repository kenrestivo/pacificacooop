#!/usr/bin/perl -w


#$Id$
#fixes last names

use DBI;

#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";


#approximate list of families
$rquery = " <INSERT HERE WHEN YOU GET YOUR SHIT TOGETHER>";
print "doing <$rquery>\n"; #XXX debug only
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($ritemref = $rqueryobj->fetchrow_hashref){
	%ritem = %$ritemref;
	print "Subject: Insurance Information for Co-Op\n\n";
	print "Hello! My job this year is to keep track of the driver's license and auto insurance information for the school. This is actually an automated email that is being sent by a computer program. I know, it's impersonal, but, computers are good for automating repetitive tasks like this. My apologies.\n\n";
	print "According my new program-- which may be completely wrong (I wrote itmyself) your auto insurance is with " . $ritem{'companyname'} . " and expired on " . $ritem{'expires'} . "\n\n";
	print "Regulations require us to have a copy of a valid driver's license and current auto insurance on file. It appears this has to be current in order for you to be allowed to drive your child on any field trips. The next field trip is scheduled for the end of October, so, now is a good time to get all this paperwork up-to-date.\n\n";
	print "If you could please place a copy of your current insurance card (the one that you keep in your car) into my communications folder, that would be great.\n\n";
	print "Again, sorry for the impersonal email. Please feel free to call me at 650-355-1317 with any questions.\n\nThanks!\n\n-ken";

} # end while


$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

#EOF
