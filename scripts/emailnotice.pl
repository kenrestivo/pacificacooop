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
# GNU General Public License for more details.  
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


use Time::Local; 
use POSIX;
use DBI;
use Mail::Sendmail;
use Getopt::Std;


#opt processing
getopts('vaesrd:') or &usage();

#arggh
if($opt_d){
	$checkdate = &humantounix($opt_d);
} else {
	#ok, now is the time to check.
	print STDERR "You may NOT send out emails without explicitly supplying a checkdate\n";
	exit 1;
	#$checkdate = time();
}

print "checking against $opt_d date $checkdate which is ", 
		strftime('%m/%d/%Y', localtime($checkdate)), "\n";



#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";


#approximate list of families
$rquery = "
	select families.name, families.familyid, families.phone,
			parents.email, parents.last, parents.first, parents.parentsid
		from families 
			left join parents on parents.familyid = families.familyid
		where parents.worker = 'Yes'
		group by families.name
";


#print "doing <$rquery>\n"; #XXX debug only
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($famref = $rqueryobj->fetchrow_hashref){
	$id = $famref->{'familyid'};

	$insarref = &getinsuranceinfo($id);
	$licarref = &getlicenseinfo($id);

	#TODO the entire architecture of this thing is botched!
	#	i MUST handle the case of more than one working parent!
	#	i.e. BOTH their drivers licenses must be up to date.

	# the report of people i need to call or write a note to!
	if($opt_r){
		if($opt_e ? 1 : !$famref->{'email'}){
			print &fieldTripReport($famref, $insarref, $licarref, 
					$checkdate, $opt_a ? 0 : 1);
		}
	} else {
		if($famref->{'email'}){
			&emailReminder($famref, $insarref, $licarref, 
				$checkdate, $opt_a ? 0 : 1);
		}
	}

} # end while


$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

########### END OF MAIN CODE



######################
#	GETLICENSEINFO
######################
sub getlicenseinfo()
{
	my $famid = shift;
	my $queryobj;
	my $itemref;
	my @results;
	my %item;
	my $query = "
		select unix_timestamp(max(lic.expires)) as exp, 
			lic.last, lic.first, lic.middle, lic.state
		from lic 
			left join parents on lic.parentsid = parents.parentsid 
		where parents.worker= 'Yes' 
			and parents.familyid = $famid
		group by parents.parentsid
		order by exp asc";
	#print "doing <$query>\n"; #XXX debug only
	$queryobj = $dbh->prepare($query) or die "can't prepare <$query>\n";
	$queryobj->execute() or die "couldn't execute $!\n";

	while ($itemref = $queryobj->fetchrow_hashref){
		%item = %$itemref; #store a local copy, because mysql will blow it away!
		push(@results, \%item); #yes, that's right, a referene
	} # end while


	return \@results; # ref to the results which is an array of refs!
}# END GETLICENSEINFO



######################
#	GETINSURANCEINFO
######################
sub getinsuranceinfo()
{
	my $famid = shift;
	my $queryobj;
	my $itemref;
	my %item;
	my @results;
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
		#printf("DEBUG famid: %s %s %s exp %s %s\n", 
		#	$famid, $item{'last'}, $item{'first'}, 
		#	strftime('%m/%d/%Y', localtime($item{'exp'})),
		#	$item{'policynum'});
		push(@results, \%item); #yes, that's right, a referene
	} # end while

	return \@results; # yes, a ref to the results

} #END GETINSURANCEINFO



######################
#	EMAILREMINDER
######################
sub emailReminder()
{
	my $famref = shift;
	my $insarref = shift;
	my $licarref = shift;
	my $checkdate = shift;
	my $onlyexpired = shift;
	my $insref;
	my $licref;
	my $m = "";
	my $if = 0; # insurance flag
	my $lf = 0; # license flag
	my $newbie = 0;

	#printf("DEBUG lic %d ins %d\n", 
	#	$licref->{'exp'}, $insref->{'exp'});
	#

	$m .= "Hello! My job this year is to keep track of the automobile insurance and driver's license information for the school.\n\n";

	#insurance
	foreach $insref (@$insarref){
		if($insref->{'exp'}){
			if($insref->{'exp'} < $checkdate){
				$m .= sprintf(
						"The '%s' insurance card on file for the %s family expired on %s.\n\n",

						$insref->{'companyname'},
						$famref->{'name'},
						strftime('%m/%d/%Y', localtime($insref->{'exp'})) 
					);
				$if++;
			}
		} else {
			$m .= sprintf("The school doesn't have any insurance card on file for the %s family (or, at least, I couldn't find it).\n\n",
						$famref->{'name'});
			$if++;
			$newbie++;
		}
	}
	
	#license
	foreach $licref (@$licarref){
		if($licref->{'exp'}){
			if($licref->{'exp'} < $checkdate){
				$m .= sprintf(
						"The copy of the %s driver's license on file for %s %s %s expired on %s.\n\n",
						$licref->{'state'},
						$licref->{'first'},
						$licref->{'middle'},
						$licref->{'last'},
						strftime('%m/%d/%Y', localtime($licref->{'exp'})) 
				);
				$lf++;
			}
		} else {
				$m .= sprintf(
						"%s couldn't find any driver's license on file for the working parent on the roster, %s %s.\n\n",
						$if ? "I also" : "I",
						$famref->{'first'},
						$famref->{'last'}
				);
			$lf++;
			$newbie++;
		}
	}

	if($newbie > 1){
		$m .= sprintf("Regulations require the school to have a copy of a valid driver's license and current auto insurance on file. It appears this has to be current in order for you to be allowed to drive your child on any field trips. The next field trip is scheduled for %s, so now is the time to get all this paperwork up-to-date.\n\n", 
			strftime('%A, %B %d', localtime($checkdate))
		);
	}

	$m .= "If you could, please place a copy of ";
	if($if){
		$m .= "your current insurance card (the one that most people keep in their car)"
	}
	if($lf && $if){
		$m .= " and ";
	}
	if($lf){
		$m .= "a copy of your current driver's license"
	}
	$m .= sprintf(" into my communications folder (Restivo, PM), before %s.\n\n",
			strftime('%A, %B %d', localtime($checkdate))
		);

	$m .= "My apologies for the impersonal, automatic computer-generated email. Please feel free to call me at 650-355-1317 with any questions.\n\nThanks!\n\n-ken\n";

	#ok, finish up.
	if($if || $lf){
		#confirm
		printf("\n--------------\n<%s>\nemail the above message to %s %s (%s)?\n",
			$m,
			$famref->{'first'},
			$famref->{'last'},
			$famref->{'email'},
			);
		$res = <STDIN>;
		if($res =~ /^[yY]/){
			%mail = ( 	To => $famref->{'email'},
						From => 'ken@restivo.org',
						Subject=> "Insurance Information for Co-Op",
						Message => $m,
					);
			#SEND!
			if($opt_s){
				&sendmail(%mail) or die $Mail::Sendmail::error;
				&updateNags($famref->{'parentsid'});
				printf("result: <%s>\n", $Mail::Sendmail::log);
			} else {
				print "-----\nNOTE!!! this is a dry run, no email will actually be sent\n";
			}
		}
		return 1;
	} else {
		return 0;
	}

} #END EMAILREMINDER


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
#	FIELDTRIPREPORT
#	inputs:
#	outputs: a tabular style report
######################
sub fieldTripReport()
{
	my $famref = shift;
	my $insarref = shift;
	my $licarref = shift;
	my $checkdate = shift;
	my $onlyexpired = shift;
	my $licref;
	my $insref;
	my $badness = "";
	my $flag = 0;
	my $insexp = 0; #flag
	my $licexp = 0; #flag

	#printf("DEBUG lic %d ins %d\n", 
	#	$licref->{'exp'}, $insref->{'exp'});

	#families
	$badness .= sprintf(
				"\n-------------\n%s family Insurance and License\n",
				$famref->{'name'} ? $famref->{'name'} : ""
			);
	$badness .= sprintf(
				" \tFamily Phone: %s\tEmail: %s\n",
				$famref->{'phone'} ? $famref->{'phone'} : "",
				$famref->{'email'} ? $famref->{'email'} : ""
			);

	#insurance
	foreach $insref (@$insarref){
		if($insref->{'exp'}){
			if($onlyexpired ? 1 : $insref->{'exp'} < $checkdate ){
				$badness .= sprintf(
						"\t- Insurance %s %s Company: %s Policy#: %s \n",
						$insref->{'exp'} < $checkdate  ? "EXPIRED" : "",
						strftime('%m/%d/%Y', localtime($insref->{'exp'})) ,
						$insref->{'companyname'},
						$insref->{'policynum'}
					);
				$insexp++;
			}
		} else {
			$badness .= "\t- No insurance information for this family\n";
			$insexp++;
		}
	}
		
	#license
	foreach $licref (@$licarref){
		if($licref->{'exp'}){
			if($onlyexpired ? 1 : $licref->{'exp'} < $checkdate){
				$badness .= sprintf(
						"\t- License expired: %s Driver's Name: %s %s %s \n",
						strftime('%m/%d/%Y', localtime($licref->{'exp'})) ,
						$licref->{'first'},
						$licref->{'middle'},
						$licref->{'last'}
				);
				$licexp++;
			}
		} else {
			$badness .= "\t- No license information for working parent\n";
			$licexp++;
		}
	}

	if($insexp || $licexp){
		return $badness;
	} else {
		return "";
	}
} # END FIELDTRIPREPORT

######################
#	UPDATENAGS
#	inputs:
#	outputs: a tabular style report
######################
sub updateNags()
{
	my $pid = shift;

	print "noting that we nagged them via email already\n";

	#why enum ('Insurance', 'Springfest', 'Other'),
	#how enum ('Email', 'Phone', 'CommsFolder', 'InPerson'),
    #parentsid int(32),
	#done datetime,

	$query = sprintf("insert into nags set 
				parentsid = '%s', why = 'Insurance', how = 'Email',
				done = now() ", $pid
			);
	#print "DEBUG doing <$query>\n";
	print STDERR $dbh->do($query) . "\n";

} #END UPDATENAGS

sub usage()
{
    print STDERR "usage: $0 -d date [-s -a -v -e -a -r]\n";
    print STDERR "\t-d date (in USA format mm/dd/yyyy)\n";
    print STDERR "\t-s send the actual email, not just show what would happen! \n";
    print STDERR "\t-v verbose \n";
    print STDERR "\t-e include email parents in report\n";
    print STDERR "\t-e include ALL parents, not just expireds!\n";
    print STDERR "\t-r report mode \n";
	exit 1;
}

#EOF
