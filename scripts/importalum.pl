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
	"salut" ,
	"first" ,
	"last"  ,
	"title" ,
	"company" ,
	"addr" ,
	"addrcont"  ,
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
open(IMPORT , $filename) or die "coulnt' open $filename $!\n";
while($line = <IMPORT>){
	(@fields) = split(/\t/, $line);
	&addThem(&makeHash(\@fields, \@fn));
}

close(IMPORT) or die "coulnt' close $filename $!\n";
$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

exit 0;
#END MAIN
########################

sub
makeHash()
{
	my $ar = shift;
	my $fn = shift;
	my @a = @$ar;
	my @f = @$fn;
	my %h;
	my $i;

	$i = 0;
	foreach $field (@a){
		$h{$f[$i]} = $field;
		$i++;
	}

	return %h;
} #END MAKEHASH

sub
addThem()
{
	my $fr = shift;
	my %f = %$fr;
	my $arref;
	my $query;
	my $count = 0;
	my $rquery;
	my $ritemref;
	my %ritem;
	my $rqueryobj;

	#NOW, add the privs
	#gotta check first that they don't already exist!
	$count = 0; #again, just to be sure
	$rquery = sprintf("select count(privid) as counter from privs 
				where userid = %d and realm = '%s'", $uid, $$arref[2]
	);
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
		if($opt_v){
			printf("matched %d rows already present for %d %s\n", 
				$count, $uid, $$arref[2]);
		}
		$query = sprintf("insert into privs set 
				userid = %d, grouplevel = %d, 
				userlevel = %d, realm = '%s' ", 
			$uid, $$arref[0], $$arref[1], $$arref[2]);
		if($opt_v){
			print "doing <$query>\n";
		}
		unless($opt_t){
			print STDERR $dbh->do($query) . "\n"; #THIS DOES IT!
		}
	}

} #END ADDTHEM

sub
addUser()
{
	my $name = shift;
	my $familyid = shift;
	my $query;
	my $rquery;
	my $ritemref;
	my %ritem;
	my $rqueryobj;
	my $uid = 0;


	if($opt_v){
		if(!$name || !$familyid){
			print "addUser() missing data: famid <$familyid> name <$name>. must be teachers?\n";
		}
	}

	#ok, add the users
	#gotta check first that they don't already exist!
	$rquery = sprintf("select userid from users 
				where name like '%s' or familyid = %d ",  $name, $familyid);
	if($opt_v){
		print "doing <$rquery>\n"; 
	}
	$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
	$rqueryobj->execute() or die "couldn't execute $!\n";

	while ($ritemref = $rqueryobj->fetchrow_hashref){
		$uid = $$ritemref{'userid'};

	} # end while
	if($uid) {
		#do NOT re-enter duplicates!!
		if($opt_v){
			print "found user $name $familyid in db with id $uid\n";
		}
		return $uid;
	}
	$query = sprintf("insert into users  set
					name = \'%s\' ,
					familyid = %d
					",
					$name, $familyid);

	print "adding <$name> into users\n";
	if($opt_v){
		print "doing <$query>\n";
	}
	unless($opt_t){
		print STDERR $dbh->do($query) . "\n"; #THIS DOES IT!
	}
	
	#find out what we got for a uid
	return $dbh->{'mysql_insertid'};

}

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
    print STDERR "usage: $0 -v -t\n";
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
