#!/usr/bin/perl -w


#$Id$
# one-off to bring ann edminster's stuff in

# Copyright (C) 2003,2004  ken restivo <ken@restivo.org>
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


use DBI;
use Getopt::Std;


getopts('vtfh:p:d:') or &usage();


@fn = (
	"salutation" ,
	"first_name" ,
	"last_name"  ,
	"title" ,
	"company" ,
	"address1" ,
	"address2"  ,
	"city"  ,
	"state" ,
	"zip" ,
	"country"
);

### main code starts here

$host = $opt_h ? $opt_h : "bc";
$port = $opt_p ? ":$opt_p" : "";
$dbname = $opt_d ? $opt_d : "coop_dev";
#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:$dbname:$host$port", "input", "test" )
    or die "can't connect to database $!\n";

#get the stuff
$filename = shift;
$filename  or die "must supply filename to open\n";
open(IMPORT , $filename) or die "coulnt' open $filename $!\n";
while($line = <IMPORT>){
	(@fields) = split(/\t/, $line);
	$i = 0;
	foreach $f (@fn){
		$h{$f} = $fields[$i];
		$i++;
	}
	$ds += &addThem(\%h);
}

print "suppressed $ds duplicates\n";

close(IMPORT) or die "coulnt' close $filename $!\n";
$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

exit 0;
#END MAIN
########################

sub
makeHash()
{
	my $ar = shift;
	&debugStruct($ar, 0); #SUPERDEBUG!
	my $fn = shift;
	my @a = @$ar;
	my @f = @$fn;
	my %h;
	my $i;


	#&debugStruct(\%h, 0);

	return \%h;
} #END MAKEHASH

sub
addThem()
{
	my $fr = shift;
	my %f = %$fr;
	my $arref;
	my $key;
	my $query;
	my $count = 0;
	my $rquery;
	my $ds = 0;
	my $ritemref;
	my %ritem;
	my $rqueryobj;

	#NOW, add the privs
	#gotta check first_name that they don't already exist!
	$count = 0; #again, just to be sure
	$rquery = sprintf("select count(lead_id) as counter from leads 
				where last_name = %s ", $dbh->quote($f{'last_name'})
	);
	unless($opt_f){
		$rquery .= sprintf(" and address1 = %s ",  $dbh->quote($f{'address1'}));
	}
	if($opt_v){
		print "doing <$rquery>\n"; 
	}
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		$count += $$ritemref{'counter'};

	} # end while

	#ok. DO it!
	if($count){
		printf("%d possible duplicate for <%s> <%s>\n", 
			$count, $f{'last_name'},  $f{'address1'});
		$ds += $count;
	} else {
		$query = "insert into leads set ";
		$i = 0;
		foreach $key (keys %f){ 
			if($f{$key} !~ /^\W*$/){
				if($key eq 'last_name' && !$f{'first_name'}){
					$val = $dbh->quote($f{$key} . " Family");
				} else {
					$val = $dbh->quote($f{$key});
				}
				$query .= sprintf(" %s %s = %s ",
							$i++ ? "," : "",
							$key, $val);
			}
		}
		$query .= " , relation = 'Alumni', source = 'Springfest', family_id = 0";
		if($opt_v){
			print "doing <$query>\n";
		}
		unless($opt_t){
			print STDERR $dbh->do($query) . "\n"; #THIS DOES IT!
		}
	}

	return $ds;

} #END ADDTHEM

#######################
#	DEBUGSTRUCT
#	nifty utility function, 
#	to print the contents of ANY complex perl structure
#	TODO move this to an external utility library
#######################
sub debugStruct()
{
	my $whatsit = shift;
	my $level = shift;
	my $item;

	printf("\n%*s", -($level*4), "");
	if( $whatsit =~ /ARRAY/){
		printf("array of %d elements", scalar @$whatsit);
		foreach $item (@$whatsit){
			&debugStruct($item, $level + 1);
		}
	} 
	elsif( $whatsit =~ /HASH/){
		printf("hash of %d elements", scalar %$whatsit);
		$level++;
		foreach $item (sort(keys %$whatsit)) {
			printf("\n%*s", -($level*4), "");
			printf("key: <%s> ", $item);
			&debugStruct($whatsit->{$item}, $level + 1);
		}

	} 
	elsif( $whatsit =~ /SCALAR/){
		printf("scalar ref <%s>", $$whatsit);
	} else {
		printf("value <%s>", $whatsit);
	}

} #END DEBUGSTRUCT

sub usage()
{
    print STDERR "usage: $0 -v -t <filename>\n";
    print STDERR "imports alumni\n";
    print STDERR "\t-v verbose \n";
    print STDERR "\t-t test (don't actually update db) \n";
    print STDERR "\t-f dupecheck families not families AND addresses \n";
    print STDERR "\t-h hostname of db\n";
    print STDERR "\t-p port db is running on\n";
    print STDERR "\t-d database name\n";
	exit 1;
}

#EOF
