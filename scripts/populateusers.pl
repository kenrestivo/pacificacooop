#!/usr/bin/perl -w


#$Id$
# deduces the user names from the family names
# i shouldn't need again, after i put user-adding into the rastaimport script
# until then, if i manually add families, i DEFINITELY neeed this!

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


use DBI;
use Getopt::Std;


getopts('rvth:p:d:') or &usage();


#the access hash
# in perl, the hash keys should NOT be quoted, or all hell will break loose!!
%access = (
	0 => 'ACCESS_NONE',
	100 => 'ACCESS_SUMMARY' ,
	200 => 'ACCESS_VIEW' ,
	300 => 'ACCESS_VIEWALL' ,
	500 => 'ACCESS_EDIT' ,
	600 => 'ACCESS_ADD' ,
	700 => 'ACCESS_DELETE' ,
	800 => 'ACCESS_ADMIN' 
);

use constant _ACCESS_NONE => 0;
use constant _ACCESS_SUMMARY => 100;
use constant _ACCESS_VIEW => 200;
use constant _ACCESS_VIEWALL => 300;
use constant _ACCESS_EDIT => 500;
use constant _ACCESS_ADD => 600;
use constant _ACCESS_DELETE => 700;
use constant _ACCESS_ADMIN => 800;


#default privs for all families
#  				group, 					user, 				item
@familydefaults =  (
	[ _ACCESS_SUMMARY, _ACCESS_DELETE, "invitations" ],
	[ _ACCESS_SUMMARY, _ACCESS_VIEW, "invitations_cash" ],
	[ _ACCESS_SUMMARY, _ACCESS_DELETE, "auction" ],
	[ _ACCESS_VIEW, _ACCESS_VIEW, "calendar" ],
	[ _ACCESS_NONE, _ACCESS_VIEW, "money" ],
	[ _ACCESS_NONE, _ACCESS_VIEW, "insurance" ],
	[ _ACCESS_VIEW, _ACCESS_EDIT, "roster" ],
	[ _ACCESS_NONE, _ACCESS_VIEW, "user" ]
);

@teacherdefaults =  (
	[ _ACCESS_VIEW, _ACCESS_EDIT, "roster" ],
	[ _ACCESS_DELETE, _ACCESS_VIEW, "calendar" ],
	[ _ACCESS_NONE, _ACCESS_VIEW, "money" ],
	[ _ACCESS_SUMMARY, _ACCESS_DELETE, "auction" ],
	[ _ACCESS_DELETE, _ACCESS_EDIT, "insurance" ],
	[ _ACCESS_NONE, _ACCESS_VIEW, "user" ]
);

@teachers = ("Teacher Sandy", "Teacher Catherine", "Teacher Pat");

### main code starts here

$host = $opt_h ? $opt_h : "bc";
$port = $opt_p ? ":$opt_p" : "";
$dbname = $opt_d ? $opt_d : "coop_dev";
#basic login and housekeeping stuff
$dbh = DBI->connect("DBI:mysql:$dbname:$host$port", "input", "test" )
    or die "can't connect to database $!\n";


#approximate list of families
$rquery = "select families.name, families.familyid
		from families 
			left join leads on families.familyid = leads.familyid
		left join kids on kids.familyid = families.familyid
		left join attendance on attendance.kidsid = kids.kidsid
		left join enrol on enrol.enrolid = attendance.enrolid
		where attendance.dropout is NULL
	group by families.familyid\n";
if($opt_v){
	print "doing <$rquery>\n"; 
}
$rqueryobj = $dbh->prepare($rquery) or die "can't prepare <$rquery>\n";
$rqueryobj->execute() or die "couldn't execute $!\n";

while ($ritemref = $rqueryobj->fetchrow_hashref){
	%ritem = %$ritemref;

	$uid = &addUser($ritem{'name'}. " Family", $ritem{'familyid'});
	&addDefaultPrivs($uid, \@familydefaults);
	## TODO add group privileges as well!

} # end while


#add users for teachers
foreach $teacher (@teachers){
	$uid = &addUser($teacher);
	&addDefaultPrivs($uid, \@teacherdefaults);
}

$dbh->disconnect or die "couldnt' disconnect from dtatbase $!\n";

exit 0;
#END MAIN
########################


sub
addDefaultPrivs()
{
	my $uid = shift;
	my $defref = shift;
	my $reset = shift;
	my $arref;
	my $query;
	my $count = 0;
	my $rquery;
	my $ritemref;
	my %ritem;
	my $rqueryobj;

	#NOW, add the privs
	foreach $arref (@$defref){
		$count = 0; #gotta reset this each time!
		if($opt_v){
			printf("\tsurfing through privs: %d %d %s\n", 
				$$arref[0], $$arref[1], $$arref[2]);
		}
		#gotta check first that they don't already exist!
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
			if(!$reset){
				next; #important! we don't wanto to whack the privs!!
			}
			print "resetting privs for uid <$uid>\n";
			$query = sprintf("
				update privs set grouplevel = %d, userlevel = %d 
				where userid = %d and realm = '%s' ", 
				 $$arref[0], $$arref[1], $uid, $$arref[2]);
		} else {
			printf("adding privs for realm <%s> uid <%d>\n",
					$$arref[2], $uid
				);
			$query = sprintf("insert into privs set 
					userid = %d, grouplevel = %d, 
					userlevel = %d, realm = '%s' ", 
				$uid, $$arref[0], $$arref[1], $$arref[2]);
		}
		if($opt_v){
			print "doing <$query>\n";
		}
		unless($opt_t){
			print STDERR $dbh->do($query) . "\n"; #THIS DOES IT!
		}
	}

}

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
