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
use strict 'refs';


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
	select families.name, families.familyid, families.phone 
		from families 
	order by families.name
";


$opt_v && print "main(): doing <$rquery>\n"; #debug only

$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($famref = $rqueryobj->fetchrow_hashref){
	$id = $famref->{'familyid'};

	$insarref = &getinsuranceinfo($id);
	$pararref = &getworkers($id);
	$massivearref = &getlicenseinfo($pararref);

	#TODO the entire architecture of this thing is botched!
	#	i MUST handle the case of more than one working parent!
	#	i.e. BOTH their drivers licenses must be up to date.

	# the report of people i need to call or write a note to!
	if($opt_r){
		#TODO maybe take a filename on command line, to output the report
		print &fieldTripReport($famref, $insarref, $massivearref, 
				$checkdate, $opt_a ? 0 : 1);
	} else {
		&emailReminder($famref, $insarref, $massivearref, 
			$checkdate, $opt_a ? 0 : 1);
	}

} # end while


$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

########### END OF MAIN CODE

######################
#	GETWORKERS
#	i can have more than one worker. grr.
######################
sub getworkers()
{
	my $famid = shift;
	my @results;
	my $item;
	my $itemref;
	my $query;

	#get array of working parent(s)
	$query = "
		select parents.email, parents.last, parents.first, 
				parents.parentsid
			from parents
			where parents.worker = 'Yes'
			and familyid = $famid
	";


	$opt_v && print "getworkers(): doing <$query>\n"; #debug only
	$queryobj = $dbh->prepare($query) or die "can't prepare <$query>\n";
	$queryobj->execute() or die "couldn't execute $!\n";


	while ($itemref = $queryobj->fetchrow_hashref){
		$item = &itemhack($itemref); #store a local copy, because mysql will blow it away!
		if($opt_v){
			printf("getworkers(): famid %d parent %s %s pid %d\n",
				$famid,
				$item->{'last'}, $item->{'first'},
				$item->{'parentsid'});
		}
		push(@results, $item); #yes, that's right, a referene
	} # end while

	$opt_v && printf("getworkers(): returning %d workers\n", scalar @results);

	return \@results; # ref to the results which is an array of refs!

}  #END GETWORKERS


sub itemhack()
{
	my $itemref = shift;
	my %item = %$itemref;
	return \%item;
}

###############################################################
#	GETLICENSEINFO
#	returns: a reference, 
#			to an array, 
#				of hashes 
#					of a reference
#						to a hash of parents fields (
#					and a reference
#						to an array
#							of references
#								to  hashes of license fields
###############################################################
sub getlicenseinfo()
{
	my $pararref = shift;
	my $pars;
	my @total;

	foreach $pars (@$pararref){
		#this is so bizarre
		$everthang{'licarref'} = &parentlicensehack($pars);
		$everthang{'parref'} = $pars;
		push(@total, &itemhack(\%everthang)); #yes, that's right, a referene
	}

	$opt_v && printf("getlicenseinfo(): returning %d total\n", 
		scalar @total);

	$opt_v && &debugstruct(\@total, 0);

	return \@total; # massive.
}# END GETLICENSEINFO

sub parentlicensehack()
{
	my $pars = shift;
	my $itemref;
	my @results;
	my $item;
	my $query;
	my $queryobj;
	my $pid;

	$pid = $pars->{'parentsid'};
	$query = "
		select unix_timestamp(max(lic.expires)) as exp, 
			lic.last, lic.first, lic.middle, lic.state, lic.licensenum
		from lic 
			where lic.parentsid = $pid
		group by lic.parentsid
		order by exp asc";
	$opt_v && print "parentlicensehack(): doing <$query>\n"; #debug only
	$queryobj = $dbh->prepare($query) or die "can't prepare <$query>\n";
	$queryobj->execute() or die "couldn't execute $!\n";
	$opt_v && printf("parentlicensehack(): checking licenses for  %s %s <%d>\n", 
			$pars->{'first'}, $pars->{'last'}, $pid
			);

	while ($itemref = $queryobj->fetchrow_hashref){
		$item = &itemhack($itemref);
		if($opt_v){
			printf("parentlicensehack(): returned exp %s for %s %s num %s\n",
				$item->{'exp'},
				$item->{'first'}, $item->{'last'},
				$item->{'licensenum'}
			);
		}
		push(@results, $item); #yes, that's right, a referene
	} # end while
	$opt_v && printf("parentlicensehack(): returning %d licenses for parent %d\n", 
				scalar @results, $pid);
	return \@results;

}

sub debugstruct()
{
	my $whatsit = shift;
	my $level = shift;
	my $item;

	printf("\n%*s", -($level*4), "");
	if( $whatsit =~ /ARRAY/){
		printf("array of %d elements", scalar @$whatsit);
		foreach $item (@$whatsit){
			&debugstruct($item, $level + 1);
		}
	} 
	elsif( $whatsit =~ /HASH/){
		printf("hash of %d elements", scalar %$whatsit);
		$level++;
		foreach $item (sort(keys %$whatsit)) {
			printf("\n%*s", -($level*4), "");
			printf("key: <%s> ", $item);
			&debugstruct($whatsit->{$item}, $level + 1);
		}

	} 
	elsif( $whatsit =~ /SCALAR/){
		printf("scalar ref <%s>", $$whatsit);
	} else {
		printf("value <%s>", $whatsit);
	}

}


######################
#	GETINSURANCEINFO
######################
sub getinsuranceinfo()
{
	my $famid = shift;
	my $queryobj;
	my $itemref;
	my $item;
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

	$opt_v && print "getinsuranceinfo(): doing <$query>\n"; #debug only
	$queryobj = $dbh->prepare($query) or die "can't prepare <$query>\n";
	$queryobj->execute() or die "couldn't execute $!\n";

	while ($itemref = $queryobj->fetchrow_hashref){
		$item = &itemhack($itemref); #store a local copy, because mysql will blow it away!
		if($opt_v){
			printf("getinsuranceinfo(): famid: %s %s %s exp %s %s\n", 
				$famid, $item->{'last'}, $item->{'first'}, 
				strftime('%m/%d/%Y', localtime($item->{'exp'})),
				$item->{'policynum'});
		}
		push(@results, $item); #yes, that's right, a referene
	} # end while

	$opt_v && printf("getinsuranceinfo(): returning %d items\n", scalar @results);
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

	$opt_v && printf("emailreminder(): lic %d ins %d\n", $licref->{'exp'}, $insref->{'exp'});
	

	$m .= "Hello! My job this year is to keep track of the automobile insurance and driver's license information for the school.\n\n";

	#insurance
	unless(scalar @$insarref){
			$m .= sprintf("The school doesn't have any insurance card on file for the %s family (or, at least, I couldn't find it).\n\n",
						$famref->{'name'});
			$if++;
			$newbie++;
	}
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
		}
	}
	
	#license
	unless(scalar @$licarref){
				$m .= sprintf(
						"%s couldn't find any driver's license on file for the working parent on the roster, %s %s.\n\n",
						$if ? "I also" : "I",
						$famref->{'first'},
						$famref->{'last'}
				);
			$lf++;
			$newbie++;
	}
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
	my $marf = shift;
	my $checkdate = shift;
	my $onlyexpired = shift;
	my $licref;
	my $licarref;
	my $pararref;
	my $insref;
	my $badness = "";
	my $flag = 0;
	my $mar;
	my $insexp = 0; #flag
	my $licexp = 0; #flag

	#families
	$badness .= sprintf(
				"\n-------------\n%s family Insurance and License\n",
				$famref->{'name'} ? $famref->{'name'} : ""
			);
	$badness .= sprintf(
				" \tFamily Phone: %s\tEmail: %s\n",
				$famref->{'phone'} ? $famref->{'phone'} : "",
				$marf->[0]->{'parref'}->{'email'}
			);

	#insurance
	unless(scalar @$insarref){
			$badness .= "\t- No insurance information for this family\n";
			$insexp++;
		}
	foreach $insref (@$insarref){
		if($insref->{'exp'}){
			if($onlyexpired ? $insref->{'exp'} < $checkdate : 1){
				$badness .= sprintf(
						"\t- Insurance %s %s Company: %s Policy#: %s \n",
						$insref->{'exp'} < $checkdate  ? "EXPIRED" : "",
						strftime('%m/%d/%Y', localtime($insref->{'exp'})) ,
						$insref->{'companyname'},
						$insref->{'policynum'}
					);
				$insexp++;
			}
		} 
	}
		
	#license
	foreach $mar ( @$marf){
		$licarref = $mar->{'licarref'};
		$parref = $mar->{'parref'};
		unless(scalar @$licarref){
				#TODO put in the parent's name here, dude
				$badness .= 
					sprintf("\t- No license information for working parent %s %s\n",
						$parref->{'first'},
						$parref->{'last'}
		);
				$licexp++;
		}
		foreach $licref (@$licarref){
			if($licref->{'exp'}){
				if($onlyexpired ? $licref->{'exp'} < $checkdate : 1){
					$badness .= sprintf(
							"\t- License %s %s Driver's Name: %s %s %s \n",
							$licref->{'exp'} < $checkdate  ? "EXPIRED" : "",
							strftime('%m/%d/%Y', localtime($licref->{'exp'})) ,
							$licref->{'first'},
							$licref->{'middle'},
							$licref->{'last'}
					);
					$licexp++;
				}
			} 
		}
	}

	if($opt_v){
		printf("fieldtripreport(): exp lic %d exp ins %d\n", 
			$licexp, $insexp);
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
	$opt_v && print "updateNags(): doing <$query>\n";
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
