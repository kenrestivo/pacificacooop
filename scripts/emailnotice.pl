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
getopts('vaesrd:n:m:') or &usage();

#arggh
if($opt_d){
	$check_date = &humantounix($opt_d);
} else {
	#ok, now is the time to check.
	print STDERR "You may NOT send out emails without explicitly supplying a check_date\n";
	exit 1;
	#$check_date = time();
}

$opt_m =~ tr/a-z/A-Z/;
printf("Checking expiration against %s for %s session%s\n", 
		strftime('%m/%d/%Y', localtime($check_date)), 
		$opt_m ? $opt_m : "BOTH",
		$opt_m ? "" : "s"
);



#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:coop:bc", "input", "test" )
    or die "can't connect to database $!\n";


#approximate list of families
$rquery = "
	select families.name, families.family_id, families.phone 
		from families 
	order by families.name
";


$opt_v && print "main(): doing <$rquery>\n"; #debug only

$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($famref = $rqueryobj->fetchrow_hashref){
	$id = $famref->{'family_id'};

	$insarref = &getinsuranceinfo($id);
	$pararref = &getworkers($id);
	$massivearref = &getlicenseinfo($pararref);
	$ampm = &getampm($id);

	if(!$ampm){
		# hacky way to skip dropouts
		next;
	}

	# the report of people i need to call or write a note to!
	if($opt_r){
		#TODO maybe take a filename on command line, to output the report
		#XXX this feels really "hacky". but... i'm to tired to do it right.
		# if it has an email, and option e is specified, then skip
		if(!$opt_m || $ampm =~ /$opt_m/i) {
			unless($opt_e && $massivearref->[0]->{'parref'}->{'email_address'}){
				print &fieldTripReport($famref, $insarref, $massivearref, 
						$check_date, $opt_a ? 0 : 1);
			}
		}
	} else {
		if($massivearref->[0]->{'parref'}->{'email_address'} ){
			&emailReminder($famref, $insarref, $massivearref, $check_date );
		}
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
		select parents.email_address, parents.last_name, parents.first_name, 
				parents.parent_id
			from parents
			where parents.worker = 'Yes'
			and family_id = $famid
	";


	$opt_v && print "getworkers(): doing <$query>\n"; #debug only
	$queryobj = $dbh->prepare($query) or die "can't prepare <$query>\n";
	$queryobj->execute() or die "couldn't execute $!\n";


	while ($itemref = $queryobj->fetchrow_hashref){
		$item = &itemhack($itemref); #store a local copy, because mysql will blow it away!
		if($opt_v){
			printf("getworkers(): famid %d parent %s %s pid %d\n",
				$famid,
				$item->{'last_name'}, $item->{'first_name'},
				$item->{'parent_id'});
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

###############################################################
#	PARENTLICENSEHACK
#	just the guyts from getlixenseinfo. i had to do this.
#	otherwise, i was getting duplicates due to the @results being re-used!
###############################################################
sub parentlicensehack()
{
	my $pars = shift;
	my $itemref;
	my @results;
	my $item;
	my $query;
	my $queryobj;
	my $pid;

	$pid = $pars->{'parent_id'};
	$query = "
		select unix_timestamp(max(lic.expiration_date)) as exp, 
			lic.last_name, lic.first_name, lic.middle_name, lic.state, lic.license_number
		from lic 
			where lic.parent_id = $pid
		group by lic.parent_id
		order by exp asc";
	$opt_v && print "parentlicensehack(): doing <$query>\n"; #debug only
	$queryobj = $dbh->prepare($query) or die "can't prepare <$query>\n";
	$queryobj->execute() or die "couldn't execute $!\n";
	$opt_v && printf("parentlicensehack(): checking licenses for  %s %s <%d>\n", 
			$pars->{'first_name'}, $pars->{'last_name'}, $pid
			);

	while ($itemref = $queryobj->fetchrow_hashref){
		$item = &itemhack($itemref);
		if($opt_v){
			printf("parentlicensehack(): returned exp %s for %s %s num %s\n",
				$item->{'exp'},
				$item->{'first_name'}, $item->{'last_name'},
				$item->{'license_number'}
			);
		}
		push(@results, $item); #yes, that's right, a referene
	} # end while
	$opt_v && printf("parentlicensehack(): returning %d licenses for parent %d\n", 
				scalar @results, $pid);
	return \@results;

} #END PARENTLICENSEHACK

#######################
#	DEBUGSTRUCT
#	nifty utility function, 
#	to print the contents of ANY complex perl structure
#	TODO move this to an external utility library
#######################
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

} #END DEBUGSTRUCT


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
		select unix_timestamp(max(ins.expiration_date)) as exp, parents.family_id, 
			ins.policy_number, ins.companyname, ins.last_name, ins.first_name
			from ins 
				left join parents on ins.parent_id = parents.parent_id 
			where parents.family_id = $famid
			group by parents.parent_id
			order by exp desc
		";

	$opt_v && print "getinsuranceinfo(): doing <$query>\n"; #debug only
	$queryobj = $dbh->prepare($query) or die "can't prepare <$query>\n";
	$queryobj->execute() or die "couldn't execute $!\n";

	while ($itemref = $queryobj->fetchrow_hashref){
		$item = &itemhack($itemref); #store a local copy, because mysql will blow it away!
		if($opt_v){
			printf("getinsuranceinfo(): famid: %s %s %s exp %s %s\n", 
				$famid, $item->{'last_name'}, $item->{'first_name'}, 
				strftime('%m/%d/%Y', localtime($item->{'exp'})),
				$item->{'policy_number'});
		}
		push(@results, $item); #yes, that's right, a referene
	} # end while

	$opt_v && printf("getinsuranceinfo(): returning %d items\n", scalar @results);
	return \@results; # yes, a ref to the results

} #END GETINSURANCEINFO



######################
#	MAILIT
#	takes a message and a famref
######################
sub
mailIt()
{
	my $m = shift;
	my $parref = shift;
	printf("\n--------------\n<%s>\nemail the above message to %s %s (%s)?\n",
		$m,
		$parref->{'first_name'},
		$parref->{'last_name'},
		$parref->{'email_address'}
		);
	$res = <STDIN>;
	if($res =~ /^[yY]/){
			%mail = (       To => $parref->{'email_address'},
					From => 'ken@restivo.org',
					Subject=> "Insurance Information for Co-Op",
					Message => $m,
				);
		#SEND!
		if($opt_s){
			&sendmail(%mail) or die $Mail::Sendmail::error;
			&updateNags($parref->{'parent_id'});
			printf("result: <%s>\n", $Mail::Sendmail::log);
		} else {
			print "-----\nNOTE!!! this is a dry run, no email will actually be sent\n";
		}
	}
	
}# END MAILIT


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
	my $check_date = shift;
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
	my $skip = 0;

	#families
	$badness .= sprintf(
				"\n-------------\n%s family Insurance and License\n",
				$famref->{'name'} ? $famref->{'name'} : ""
			);
	$badness .= sprintf(
				" \tFamily Phone: %s\tEmail: %s\n",
				$famref->{'phone'} ? $famref->{'phone'} : "",
				$marf->[0]->{'parref'}->{'email_address'} ?
					$marf->[0]->{'parref'}->{'email_address'} : ""
			);

	#insurance
	unless(scalar @$insarref){
			$badness .= "\t- No insurance information for this family\n";
			$insexp++;
		}

	#XXX i only print the FIRST (latest) insurance ref, not all
	# i could print all of them.
	#if i want this as a -i switch, for example, 
	#it's more work than it's worth
	$insref  = $insarref->[0];
	if($insref->{'exp'}){
		if($onlyexpired ? $insref->{'exp'} < $check_date : 1){
			$badness .= sprintf(
					"\t- Insurance %s %s Company: %.10s #: %s \n",
					$insref->{'exp'} < $check_date  ? "EXPIRED" : "expiration_date",
					strftime('%m/%d/%Y', localtime($insref->{'exp'})) ,
					$insref->{'companyname'},
					$insref->{'policy_number'}
				);
			$insexp++;
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
						$parref->{'first_name'},
						$parref->{'last_name'}
		);
				$licexp++;
		}
		foreach $licref (@$licarref){
			if($licref->{'exp'}){
				if($onlyexpired ? $licref->{'exp'} < $check_date : 1){
					$badness .= sprintf(
							"\t- License %s %s (%s)%s Driver: %s %s %s \n",
							$licref->{'exp'} < $check_date  ? "EXPIRED" : "expiration_date",
							strftime('%m/%d/%Y', localtime($licref->{'exp'})) ,
							$licref->{'state'},
							$licref->{'license_number'},
							$licref->{'first_name'},
							$licref->{'middle_name'},
							$licref->{'last_name'}
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

	if(!$onlyexpired){
		return $badness;
	}

	if(($insexp || $licexp) && !$skip){
		return $badness;
	}

	return "";

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
    #parent_id int(32),
	#done datetime,

	$query = sprintf("insert into nags set 
				parent_id = '%s', which_event = 'Insurance', method_of_contact = 'Email',
				done = now() ", $pid
			);
	$opt_v && print "updateNags(): doing <$query>\n";
	print STDERR $dbh->do($query) . "\n";

} #END UPDATENAGS

sub usage()
{
    print STDERR "usage: $0 -d date [-s -a -v -e -a -r -n date ]\n";
    print STDERR "\t-d date (in USA format mm/dd/yyyy)\n";
    print STDERR "\t-s send the actual email, not just show what would happen! \n";
    print STDERR "\t-v verbose \n";
    print STDERR "\t-n everyone not nagged since date (mm/dd/yyyy) \n";
    print STDERR "\t-e skip email-able parents in report\n";
    print STDERR "\t-e include ALL parents, not just expireds!\n";
    print STDERR "\t-r report mode (email mode is default) \n";
    print STDERR "\t-m session (am or pm, default BOTH) \n";
	exit 1;
}

######################
#	GETAMPM
#	returns the am/pm status for this family
######################
sub getampm()
{
	my $famid = shift;
	my $item;
	my $itemref;
	my $query;
	my $sess;

	#get array of working parent(s)
	$query = "
		select enrol.sess 
			from kids 
				left join attendance on attendance.kid_id = kids.kid_id 
				left join enrol on enrol.enrolid = attendance.enrolid
			where kids.family_id = $famid and attendance.dropout is null
			group by enrol.sess
	";


	$opt_v && print "getampm(): doing <$query>\n"; #debug only
	$queryobj = $dbh->prepare($query) or die "can't prepare <$query>\n";
	$queryobj->execute() or die "couldn't execute $!\n";


	while ($itemref = $queryobj->fetchrow_hashref){
		$sess = $itemref->{'sess'};
	} # end while

	return $sess;

}  #END GETAMPM

######################
#	EMAILREMINDER
#	inputs:
#	outputs: a tabular style report
######################
sub emailReminder()
{
	my $famref = shift;
	my $insarref = shift;
	my $marf = shift;
	my $check_date = shift;
	my $licref;
	my $licarref;
	my $pararref;
	my $insref;
	my $badness = "";
	my $flag = 0;
	my $mar;
	my $insexp = 0; #flag
	my $licexp = 0; #flag
	my $skip = 0;

	$badness .= "Hello! My job this year at the Co-Op is to keep track of the automobile insurance and driver's license information for the school.\n\n";


	#insurance
	unless(scalar @$insarref){
			$badness .= sprintf("The school doesn't have any insurance card on file for the %s family (or, at least, I couldn't find it).\n\n", $famref->{'name'});
			$insexp++;
		}

	#XXX i only print the FIRST (latest) insurance ref, not all
	# i could print all of them.
	#if i want this as a -i switch, for example, 
	#it's more work than it's worth
	$insref  = $insarref->[0];
	if($insref->{'exp'}){
		if($insref->{'exp'} < $check_date){
			$badness .= sprintf(
					"The '%s' insurance card on file for your family expired on %s.\n\n",
					$insref->{'companyname'},
					strftime('%m/%d/%Y', localtime($insref->{'exp'})) 
				);
			$insexp++;
		}
	} 
	
	#license
	foreach $mar ( @$marf){
		$licarref = $mar->{'licarref'};
		$parref = $mar->{'parref'};
		unless(scalar @$licarref){
				#put in the parent's name here, dude
				$badness .= sprintf(
						"I%s couldn't find any driver's license on file for the working parent on the roster, %s %s.\n\n",
						$insexp ? " also" : "",
						$parref->{'first_name'},
						$parref->{'last_name'}
				);
				$licexp++;
		}
		foreach $licref (@$licarref){
			if($licref->{'exp'}){
				if($licref->{'exp'} < $check_date){
					$badness .= sprintf(
							"The copy of the %s driver's license %s on file for %s %s %s expired on %s.\n\n",
							$licref->{'state'},
							$licref->{'license_number'},
							$licref->{'first_name'},
							$licref->{'middle_name'} ? $licref->{'middle_name'}: "",
							$licref->{'last_name'},
							strftime('%m/%d/%Y', localtime($licref->{'exp'}))
					);
					$licexp++;
				}
			} 
		}
	}

	if($opt_v){
		printf("emailreminder(): exp lic %d exp ins %d\n", 
			$licref->{'exp'}, $insexp->{'exp'});
	}

	#some explanation probably required for those who have both missing!
	if($licexp && $insexp){
		$badness .= sprintf("Regulations require the school to have a copy of a valid driver's license and current auto insurance on file. It appears this has to be current in order for you to be allowed to drive your child on any field trips. The next field trip is scheduled for %s, so now is the time to get all this paperwork up-to-date.\n\n", 

			strftime('%A, %B %d', localtime($check_date)) );
	}
	##finishing up
	$badness .= sprintf("If you could, please place a copy of your current %s%s%s into my communications folder (Restivo, PM), at least a day or two before %s.\n\n",
			$insexp ? "insurance card": "",
			$licexp && $insexp ? " and " : "",
			$licexp ? "driver's license": "",
			strftime('%A, %B %d', localtime($check_date))
		);

	$badness .= "My apologies for the impersonal, automatic computer-generated email. Please feel free to call me at 650-355-1317 with any questions.\n\nThanks!\n\n-ken\n";


	if(($insexp || $licexp) && !$skip){

		#&debugstruct($marf, 0);
		&mailIt($badness, $marf->[0]->{'parref'});
		return $badness;
	}

	return "";

} # END EMAILREMINDER

#EOF
