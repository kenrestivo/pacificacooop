#!/usr/bin/perl -w

#$Id$
#gathers the expired or blank licenses, and prints them, optionally emailing them

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


use Time::Local; 
use POSIX;
use DBI;

#arggh
$in = shift;
if($in){
	$checkdate = &humantounix($in);
} else {
	#ok, now is the time to check.
	$checkdate = time();
}

print "checking against $in date $checkdate which is ", 
		strftime('%m/%d/%Y', localtime($checkdate)), "\n";


#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";


#approximate list of families
$rquery = "
	select families.name, families.familyid, families.phone,
			parents.email, parents.last, parents.first
		from families 
			left join parents on parents.familyid = families.familyid
		where parents.worker = 'Yes'
		group by families.name
";


#print "doing <$rquery>\n"; #XXX debug only
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($famref = $rqueryobj->fetchrow_hashref){
	$id = $$famref{'familyid'};
	$flag  = 0;

	$insref = &getinsuranceinfo($id);
	$licref = &getlicenseinfo($id);

	#only send 'em if they're null or too late
	if(!$$insref{'exp'} || $$insref{'exp'} < $checkdate){
		$flag++;
	}

	if(!$$licref{'exp'} || $$licref{'exp'} < $checkdate){
		$flag++;
	}

	if($flag){
		&expiredReport($famref, $insref, $licref);
	}

} # end while


$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

#EOF

sub getlicenseinfo()
{
	my $famid = shift;
	my $queryobj;
	my $itemref;
	my %item;
	my $query = "
		select unix_timestamp(max(lic.expires)) as exp, 
			lic.last, lic.first, lic.middle
		from lic 
			left join parents on lic.parentsid = parents.parentsid 
		where parents.worker= 'Yes' and parents.familyid = $famid
		group by parents.parentsid
		order by exp asc";
	#print "doing <$query>\n"; #XXX debug only
	$queryobj = $dbh->prepare($query) or die "can't prepare <$query>\n";
	$queryobj->execute() or die "couldn't execute $!\n";

	while ($itemref = $queryobj->fetchrow_hashref){
		%item = %$itemref; #store a local copy, because mysql will blow it away!
	} # end while

	if($queryobj->rows() > 1){
		print "\tERROR! more than one row returned for lic on $famid\n";
	}

	return \%item;
}

sub getinsuranceinfo()
{
	my $famid = shift;
	my $queryobj;
	my $itemref;
	my %item;
	my $query = "
		select unix_timestamp(max(ins.expires)) as exp, parents.familyid, 
			ins.policynum, ins.companyname, ins.last, ins.first
			from ins 
				left join parents on ins.parentsid = parents.parentsid 
			where parents.familyid = $famid
			group by parents.parentsid
			order by exp asc
		";

	#print "doing <$query>\n"; #XXX debug only
	$queryobj = $dbh->prepare($query) or die "can't prepare <$query>\n";
	$queryobj->execute() or die "couldn't execute $!\n";

	while ($itemref = $queryobj->fetchrow_hashref){
		%item = %$itemref; #store a local copy, because mysql will blow it away!
	} # end while

	if($queryobj->rows() > 1){
		print "\tERROR! more than one row returned for ins on <$famid>\n";
	}

	return \%item;

}

######################
#	EMAILREMINDER
######################
sub emailreminder()
{
	my $ritemref = shift;
	my %ritem = %$ritemref;

	print "Subject: Insurance Information for Co-Op\n\n";
	print "Hello! My job this year is to keep track of the driver's license and auto insurance information for the school. This is actually an automated email that is being sent by a computer program. I know, it's impersonal, but, computers are good for automating repetitive tasks like this. My apologies.\n\n";
	print "According this new program-- which may be completely wrong (I wrote itmyself) your auto insurance is with " . $ritem{'companyname'} . " and expired on " . $ritem{'expires'} . "\n\n";
	print "Regulations require us to have a copy of a valid driver's license and current auto insurance on file. It appears this has to be current in order for you to be allowed to drive your child on any field trips. The next field trip is scheduled for the end of October, so, now is a good time to get all this paperwork up-to-date.\n\n";
	print "If you could please place a copy of your current insurance card (the one that you keep in your car) into my communications folder, that would be great.\n\n";
	print "Again, sorry for the impersonal email. Please feel free to call me at 650-355-1317 with any questions.\n\nThanks!\n\n-ken";
}


######################
#	HUMANTOUNIX
#	takes in a HUMAN time dd/mm/yyyy and conerts to unix
######################
sub humantounix()
{
	my $humandate = shift;
	my $mon;
	my $day;
	my $yr;
	my $nix;
	($mon, $day, $yr) = ($humandate =~ /(\d+)\/(\d+)\/(\d{4})/);
	#print "date is $mon/$day/$yr\n";
		
	return timelocal(0,0,0, $day, $mon - 1, $yr);
}


######################
#	EXPIREDREPORT
#	inputs:
#	outputs: a tabular style report
######################
sub expiredReport()
{
	my $famref = shift;
	my $insref = shift;
	my $licref = shift;
	my $badness = "";

	#families
	$badness .= sprintf(
				"%s %s %s \n-------------\n",
				$$famref{'name'} ? $$famref{'name'} : "",
				$$famref{'phone'} ? $$famref{'phone'} : "",
				$$famref{'email'} ? $$famref{'email'} : ""
			);

	#insurance
	if($$insref{'exp'} < $checkdate){
			$badness .= sprintf(
					"\tins %s company %s policy %s \n",
					strftime('%m/%d/%Y', localtime($$insref{'exp'})) ,
					$$insref{'companyname'},
					$$insref{'policynum'}
				);
	} else {
			$badness .= "\tno insururance info\n";
	}
	
	#license
	if($$licref{'exp'} < $checkdate){
		$badness .= sprintf(
				"\tlic %s driver %s %s %s \n",
				strftime('%m/%d/%Y', localtime($$licref{'exp'})) ,
				$$licref{'first'},
				$$licref{'middle'},
				$$licref{'last'}
		);
	} else {
			$badness .= "\tno license info\n";
	}
} # END EXPIREDREPORT

#EOF
