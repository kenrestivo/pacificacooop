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


getopts('rvth:p:d:') or &usage();


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

sub usage()
{
    print STDERR "usage: $0 -v -t\n";
    print STDERR "makes sure there are users for each family/teacher\n";
    print STDERR "and that they have privs for all default realms\n";
    print STDERR "sets privs for new realms/users to defaults\n";
    print STDERR "\t-v verbose \n";
    print STDERR "\t-r reset ALL privs to defaults (dangerous!!) \n";
    print STDERR "\t-t test (don't actually update db) \n";
    print STDERR "\t-h hostname of db\n";
    print STDERR "\t-p port db is running on\n";
    print STDERR "\t-d database name\n";
	exit 1;
}

#EOF
