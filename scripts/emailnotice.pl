#!/usr/bin/perl -w


#$Id$
#fixes last names

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
# GNU General Public License for more details.                                  #
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


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
